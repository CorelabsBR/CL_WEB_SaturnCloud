<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MercadoPagoService
{
    public function __construct(private readonly ProvisioningService $provisioning)
    {
    }

    public function createPreference(Order $order): array
    {
        $order->loadMissing(['items.product', 'user']);

        $payload = [
            'items' => $this->itemsPayload($order),
            'payer' => $this->payerPayload($order),
            'external_reference' => (string) $order->id,
            'notification_url' => route('webhooks.mercadopago'),
            'back_urls' => [
                'success' => route('checkout.success'),
                'pending' => route('checkout.pending'),
                'failure' => route('checkout.failure'),
            ],
            'auto_return' => 'approved',
            'statement_descriptor' => config('services.mercadopago.statement_descriptor'),
        ];

        if ($order->billing_type === 'renewal') {
            $payload['payment_methods'] = [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'ticket'],
                ],
            ];
        }

        $response = $this->client()->post('/checkout/preferences', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException($response->body());
        }

        $preference = $response->json();

        Payment::updateOrCreate(
            [
                'order_id' => $order->id,
                'provider' => 'mercadopago',
                'preference_id' => $preference['id'] ?? null,
            ],
            [
                'amount' => $order->total,
                'status' => 'pending',
                'raw_payload' => $preference,
            ]
        );

        return $preference;
    }

    public function getPayment(string $paymentId): array
    {
        $response = $this->client()->get("/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            throw new \RuntimeException($response->body());
        }

        return $response->json();
    }

    public function ensurePixPayment(Order $order): Payment
    {
        $order->loadMissing(['items.product', 'user', 'renewalService']);

        if ($order->status !== 'pendente' || $order->billing_type !== 'renewal') {
            throw new \RuntimeException('Esta fatura não está disponível para pagamento via Pix.');
        }

        if ($order->due_at && $order->due_at->isPast()) {
            throw new \RuntimeException('Esta fatura está vencida.');
        }

        $existingPayment = $order->payments()
            ->where('provider', 'mercadopago')
            ->where('payment_method_id', 'pix')
            ->where('status', 'pending')
            ->latest('id')
            ->get()
            ->first(fn (Payment $payment) => filled(data_get($payment->raw_payload, 'point_of_interaction.transaction_data.qr_code')));

        if ($existingPayment) {
            return $existingPayment;
        }

        $user = $order->user;
        $names = preg_split('/\s+/', trim((string) $user?->nome), 2);
        $document = preg_replace('/\D+/', '', (string) $user?->cpf_cnpj);
        $payerEmail = trim((string) $user?->email);

        if (! filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
            $configuredEmail = trim((string) config('mail.from.address'));
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'vortexhost.com.br';

            $payerEmail = filter_var($configuredEmail, FILTER_VALIDATE_EMAIL)
                ? $configuredEmail
                : "cliente-{$user?->id}@{$appHost}";
        }

        $expiration = $order->due_at && $order->due_at->gt(now()->addMinutes(30))
            ? $order->due_at->copy()
            : now()->addMinutes(30);

        $payload = [
            'transaction_amount' => (float) $order->total,
            'description' => Str::limit(
                $order->items->pluck('product.nome')->filter()->join(', ') ?: 'Renovação Saturn Cloud',
                120,
                ''
            ),
            'payment_method_id' => 'pix',
            'external_reference' => (string) $order->id,
            'notification_url' => route('webhooks.mercadopago'),
            'date_of_expiration' => $expiration->format('Y-m-d\TH:i:s.vP'),
            'payer' => array_filter([
                'email' => $payerEmail,
                'first_name' => $names[0] ?? null,
                'last_name' => $names[1] ?? null,
                'identification' => $document ? [
                    'type' => strlen($document) > 11 ? 'CNPJ' : 'CPF',
                    'number' => $document,
                ] : null,
            ]),
        ];

        $response = $this->client()
            ->withHeader('X-Idempotency-Key', "vortex-renewal-pix-{$order->id}")
            ->post('/v1/payments', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException($response->body());
        }

        $payment = $response->json();
        $providerPaymentId = (string) ($payment['id'] ?? '');

        if ($providerPaymentId === '' || ! data_get($payment, 'point_of_interaction.transaction_data.qr_code')) {
            throw new \RuntimeException('O Mercado Pago não retornou os dados do QR Code Pix.');
        }

        return Payment::updateOrCreate(
            [
                'provider' => 'mercadopago',
                'provider_payment_id' => $providerPaymentId,
            ],
            [
                'order_id' => $order->id,
                'payment_method_id' => 'pix',
                'payment_type_id' => $payment['payment_type_id'] ?? 'bank_transfer',
                'amount' => $payment['transaction_amount'] ?? $order->total,
                'status' => $this->localPaymentStatus((string) ($payment['status'] ?? 'pending')),
                'raw_payload' => $payment,
            ]
        );
    }

    public function syncPayment(array $payment): ?Order
    {
        $orderId = $payment['external_reference'] ?? null;

        if (! $orderId) {
            return null;
        }

        $order = Order::find($orderId);

        if (! $order) {
            return null;
        }

        $providerPaymentId = (string) ($payment['id'] ?? '');
        $providerStatus = (string) ($payment['status'] ?? 'pending');
        $localStatus = $this->localPaymentStatus($providerStatus);

        [$order, $orderStatus, $previousStatus] = DB::transaction(function () use (
            $order,
            $payment,
            $providerPaymentId,
            $providerStatus,
            $localStatus
        ) {
            $lockedOrder = Order::query()->lockForUpdate()->find($order->id);

            Payment::updateOrCreate(
                [
                    'provider' => 'mercadopago',
                    'provider_payment_id' => $providerPaymentId,
                ],
                [
                    'order_id' => $lockedOrder->id,
                    'preference_id' => $payment['preference_id'] ?? null,
                    'payment_method_id' => $payment['payment_method_id'] ?? null,
                    'payment_type_id' => $payment['payment_type_id'] ?? null,
                    'amount' => $payment['transaction_amount'] ?? $lockedOrder->total,
                    'status' => $localStatus,
                    'raw_payload' => $payment,
                ]
            );

            $previousStatus = $lockedOrder->status;
            $orderStatus = $this->localOrderStatus($providerStatus, $previousStatus);

            $lockedOrder->update([
                'status' => $orderStatus,
                'mp_pagamento_id' => $providerPaymentId ?: $lockedOrder->mp_pagamento_id,
            ]);

            return [$lockedOrder->fresh(), $orderStatus, $previousStatus];
        });

        if ($orderStatus === 'pago' && $order->billing_type === 'renewal' && $order->due_at?->isPast()) {
            $order->update(['status' => 'cancelado']);

            Log::warning('Pagamento aprovado apos prazo de renovacao; servico nao foi reativado automaticamente', [
                'order_id' => $order->id,
                'service_id' => $order->service_id,
                'due_at' => $order->due_at?->toIso8601String(),
            ]);

            return $order->fresh();
        }

        if ($orderStatus === 'pago' && $order->billing_type === 'renewal') {
            $this->renewService($order);
        }

        if ($orderStatus === 'pago' && $previousStatus !== 'pago' && $order->billing_type !== 'renewal') {
            $this->provisioning->provisionOrder($order);
            $this->processHestiaQueue($order);
        }

        return $order;
    }

    private function processHestiaQueue(Order $order): void
    {
        try {
            $exitCode = Artisan::call('hestia:process-queue', [
                'order_id' => (string) $order->id,
            ]);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                Log::warning('Fila Hestia retornou erro apos pagamento aprovado', [
                    'order_id' => $order->id,
                    'exit_code' => $exitCode,
                    'output' => $output,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Falha ao processar fila Hestia apos pagamento aprovado', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function renewService(Order $order): void
    {
        $service = $order->renewalService;

        if (! $service) {
            return;
        }

        $baseDate = $service->expires_at && $service->expires_at->isFuture()
            ? $service->expires_at
            : now();

        $service->update([
            'status' => 'active',
            'expires_at' => $service->type === 'web'
                ? $baseDate->copy()->addYear()
                : $baseDate->copy()->addMonth(),
        ]);
    }

    public function validateWebhookSignature(Request $request): bool
    {
        $secret = config('services.mercadopago.webhook_secret');

        if (! $secret) {
            return true;
        }

        $signature = (string) $request->header('x-signature');
        $requestId = (string) $request->header('x-request-id');
        $dataId = (string) ($request->query('data.id') ?: data_get($request->input('data'), 'id', ''));

        if (! $signature || ! $requestId || ! $dataId) {
            return false;
        }

        $parts = collect(explode(',', $signature))
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

                return [$key => $value];
            });

        $timestamp = $parts->get('ts');
        $hash = $parts->get('v1');

        if (! $timestamp || ! $hash) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    private function client(): PendingRequest
    {
        $accessToken = config('services.mercadopago.access_token');

        if (! $accessToken) {
            throw new \RuntimeException('MERCADOPAGO_ACCESS_TOKEN não configurado.');
        }

        return Http::baseUrl(rtrim(config('services.mercadopago.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withToken($accessToken)
            ->timeout(20)
            ->retry(2, 250);
    }

    private function itemsPayload(Order $order): array
    {
        $itemsTotal = round((float) $order->items->sum(fn ($item) => $item->preco * $item->quantidade), 2);

        if (round((float) $order->total, 2) !== $itemsTotal) {
            return [[
                'id' => (string) $order->id,
                'title' => 'Pedido Saturn Cloud',
                'description' => $order->coupon_code
                    ? "Pedido com cupom {$order->coupon_code}"
                    : 'Pedido Saturn Cloud',
                'quantity' => 1,
                'currency_id' => 'BRL',
                'unit_price' => (float) $order->total,
            ]];
        }

        return $order->items->map(fn ($item) => [
            'id' => (string) $item->product_id,
            'title' => $item->product->nome,
            'description' => $item->product->descricao,
            'quantity' => (int) $item->quantidade,
            'currency_id' => 'BRL',
            'unit_price' => (float) $item->preco,
        ])->values()->all();
    }

    private function payerPayload(Order $order): array
    {
        $user = $order->user;
        $names = preg_split('/\s+/', trim((string) $user?->nome), 2);
        $document = preg_replace('/\D+/', '', (string) $user?->cpf_cnpj);
        $phone = preg_replace('/\D+/', '', (string) $user?->celular);

        return array_filter([
            'name' => $names[0] ?? null,
            'surname' => $names[1] ?? null,
            'email' => $user?->email,
            'phone' => $phone ? [
                'area_code' => substr($phone, 0, 2),
                'number' => substr($phone, 2),
            ] : null,
            'identification' => $document ? [
                'type' => strlen($document) > 11 ? 'CNPJ' : 'CPF',
                'number' => $document,
            ] : null,
            'address' => array_filter([
                'zip_code' => preg_replace('/\D+/', '', (string) $user?->cep),
                'street_name' => $user?->rua,
                'street_number' => $user?->numero,
            ]),
        ]);
    }

    private function localPaymentStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            'approved', 'authorized' => 'approved',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending',
        };
    }

    private function localOrderStatus(string $providerStatus, string $currentStatus): string
    {
        if ($currentStatus === 'pago') {
            return 'pago';
        }

        return match ($providerStatus) {
            'approved', 'authorized' => 'pago',
            'rejected', 'cancelled', 'refunded', 'charged_back' => 'cancelado',
            default => 'pendente',
        };
    }
}
