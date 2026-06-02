<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CustomerService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RenewalBillingService
{
    public function __construct(
        private readonly HestiaService $hestia,
        private readonly PterodactylService $pterodactyl,
    ) {
    }

    public function process(?int $userId = null): array
    {
        $expiredInitialOrders = $this->purgeExpiredInitialPendingOrders($userId);
        $scheduledTerminated = $this->terminateScheduledCancellations($userId);
        $overdueTerminated = $this->terminateOverdueServices($userId);

        return [
            'expired_initial_orders' => $expiredInitialOrders,
            'terminated' => $scheduledTerminated + $overdueTerminated,
            'generated' => $this->createMonthlyInvoices($userId),
        ];
    }

    public function processUser(int $userId): array
    {
        return $this->process($userId);
    }

    public function createMonthlyInvoices(?int $userId = null): int
    {
        $generated = 0;

        CustomerService::query()
            ->with('product')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereNull('cancellation_requested_at')
            ->where('expires_at', '<=', now())
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->orderBy('id')
            ->chunkById(100, function ($services) use (&$generated) {
                foreach ($services as $service) {
                    if ($this->hasOpenMonthlyInvoice($service)) {
                        continue;
                    }

                    $order = DB::transaction(function () use ($service) {
                        $lockedService = CustomerService::query()
                            ->lockForUpdate()
                            ->with('product')
                            ->find($service->id);

                        if (
                            ! $lockedService
                            || $lockedService->status !== 'active'
                            || ! $lockedService->expires_at
                            || $lockedService->expires_at->isFuture()
                            || $lockedService->cancellation_requested_at
                            || $this->hasOpenMonthlyInvoice($lockedService)
                        ) {
                            return null;
                        }

                        $order = Order::create([
                            'user_id' => $lockedService->user_id,
                            'total' => $lockedService->product?->preco ?: 0,
                            'status' => 'pendente',
                            'billing_type' => 'renewal',
                            'service_id' => $lockedService->id,
                            'due_at' => now()->addDays(5),
                        ]);

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $lockedService->product_id,
                            'quantidade' => 1,
                            'preco' => $lockedService->product?->preco ?: 0,
                        ]);

                        return $order->load(['items.product', 'renewalService.product']);
                    });

                    if (! $order) {
                        continue;
                    }

                    $generated++;
                    $this->sendMonthlyInvoiceEmail($order);
                }
            });

        return $generated;
    }

    public function purgeExpiredInitialPendingOrders(?int $userId = null): int
    {
        $purged = 0;

        Order::query()
            ->where('status', 'pendente')
            ->where('billing_type', '!=', 'renewal')
            ->where('criado_em', '<=', now()->subDay())
            ->whereDoesntHave('services')
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$purged) {
                foreach ($orders as $order) {
                    if ($this->purgeExpiredInitialPendingOrder($order)) {
                        $purged++;
                    }
                }
            });

        return $purged;
    }

    public function purgeExpiredInitialPendingOrder(Order $order): bool
    {
        return (bool) DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()
                ->withCount('services')
                ->lockForUpdate()
                ->find($order->id);

            if (
                ! $lockedOrder
                || $lockedOrder->status !== 'pendente'
                || $lockedOrder->billing_type === 'renewal'
                || $lockedOrder->services_count > 0
                || ! $lockedOrder->criado_em
                || $lockedOrder->criado_em->gt(now()->subDay())
            ) {
                return false;
            }

            if ($lockedOrder->coupon_code && (float) $lockedOrder->discount_amount > 0) {
                Coupon::query()
                    ->where('code', $lockedOrder->coupon_code)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');

                DB::table('coupon_redemptions')
                    ->where('order_id', $lockedOrder->id)
                    ->delete();
            }

            $lockedOrder->payments()->delete();
            $lockedOrder->items()->delete();
            $lockedOrder->delete();

            return true;
        });
    }

    public function terminateScheduledCancellations(?int $userId = null): int
    {
        $terminated = 0;

        CustomerService::query()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereNotNull('cancellation_requested_at')
            ->where('expires_at', '<=', now())
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->orderBy('id')
            ->chunkById(100, function ($services) use (&$terminated) {
                foreach ($services as $service) {
                    if ($this->terminateScheduledCancellation($service)) {
                        $terminated++;
                    }
                }
            });

        return $terminated;
    }

    private function terminateScheduledCancellation(CustomerService $service): bool
    {
        $service = CustomerService::query()->find($service->id);

        if (
            ! $service
            || $service->status !== 'active'
            || ! $service->expires_at
            || ! $service->cancellation_requested_at
            || $service->expires_at->isFuture()
        ) {
            return false;
        }

        try {
            $this->deleteRemoteService($service);
        } catch (\Throwable $e) {
            Log::error('Falha ao apagar serviço com cancelamento agendado', [
                'service_id' => $service->id,
                'type' => $service->type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        DB::transaction(function () use ($service) {
            Order::query()
                ->where('billing_type', 'renewal')
                ->where('service_id', $service->id)
                ->where('status', 'pendente')
                ->update(['status' => 'cancelado']);

            $this->purgeLocalServiceRecords($service);
        });

        return true;
    }

    public function terminateOverdueServices(?int $userId = null): int
    {
        $terminated = 0;

        Order::query()
            ->with('renewalService')
            ->where('billing_type', 'renewal')
            ->whereIn('status', ['pendente', 'cancelado'])
            ->whereNotNull('service_id')
            ->where('due_at', '<=', now())
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$terminated) {
                foreach ($orders as $order) {
                    if ($this->terminateOverdueOrder($order)) {
                        $terminated++;
                    }
                }
            });

        return $terminated;
    }

    public function terminateOverdueOrder(Order $order): bool
    {
        $order->loadMissing('renewalService');

        if (
            $order->billing_type !== 'renewal'
            || ! in_array($order->status, ['pendente', 'cancelado'], true)
            || ! $order->due_at
            || $order->due_at->isFuture()
            || ! $order->renewalService
            || $order->renewalService->status === 'cancelled'
        ) {
            return false;
        }

        $service = $order->renewalService;

        try {
            $this->deleteRemoteService($service);
        } catch (\Throwable $e) {
            Log::error('Falha ao apagar serviço vencido', [
                'order_id' => $order->id,
                'service_id' => $service->id,
                'type' => $service->type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        DB::transaction(fn () => $this->purgeLocalServiceRecords($service));

        return true;
    }

    private function hasOpenMonthlyInvoice(CustomerService $service): bool
    {
        return Order::query()
            ->where('billing_type', 'renewal')
            ->where('service_id', $service->id)
            ->where('status', 'pendente')
            ->exists();
    }
private function sendMonthlyInvoiceEmail(Order $order): void
{
    $user = User::find($order->user_id);

    if (! $user?->email) {
        return;
    }

    try {
        Mail::html($this->monthlyInvoiceEmailBody($order, $user), function ($message) use ($order, $user) {
            $message->to($user->email, $user->nome)
                ->subject('Fatura mensal pendente - VortexHost #' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));
        });
    } catch (\Throwable $e) {
        Log::warning('Falha ao enviar email de fatura mensal pendente', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'error' => $e->getMessage(),
        ]);
    }
}

private function monthlyInvoiceEmailBody(Order $order, User $user): string
{
    $service = $order->renewalService;

    $serviceName = $service?->name
        ?: $order->items->first()?->product?->nome
        ?: 'Serviço VortexHost';

    $amount = 'R$ ' . number_format((float) $order->total, 2, ',', '.');

    $dueAt = $order->due_at?->format('d/m/Y H:i')
        ?: 'em até 5 dias';

    $panelUrl = route('dashboard.index') . '#faturas';

    $firstName = trim(explode(' ', (string) $user->nome)[0] ?? '')
        ?: 'Cliente';

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fatura pendente - VortexHost</title>
</head>
<body style="margin:0;padding:0;background-color:#0f172a;font-family:Arial,sans-serif;color:#e2e8f0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#111827;border-radius:12px;overflow:hidden;border:1px solid #1f2937;">

                    <tr>
                        <td style="background:#2563eb;padding:28px 32px;text-align:center;">
                            <h1 style="margin:0;font-size:28px;color:#ffffff;">
                                VortexHost
                            </h1>

                            <p style="margin:8px 0 0 0;color:#dbeafe;font-size:14px;">
                                Notificação de cobrança
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">

                            <p style="margin:0 0 20px 0;font-size:16px;line-height:1.6;">
                                Olá, <strong>{$firstName}</strong>.
                            </p>

                            <p style="margin:0 0 24px 0;font-size:15px;line-height:1.7;color:#cbd5e1;">
                                Identificamos uma fatura pendente vinculada ao serviço abaixo.
                                Para evitar suspensão ou remoção automática, recomendamos que o pagamento seja realizado dentro do prazo informado.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#0b1220;border:1px solid #1e293b;border-radius:10px;padding:20px;">
                                <tr>
                                    <td style="padding:10px 0;color:#94a3b8;font-size:14px;">
                                        Serviço
                                    </td>

                                    <td align="right" style="padding:10px 0;color:#ffffff;font-size:14px;font-weight:bold;">
                                        {$serviceName}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px 0;color:#94a3b8;font-size:14px;">
                                        Fatura
                                    </td>

                                    <td align="right" style="padding:10px 0;color:#ffffff;font-size:14px;font-weight:bold;">
                                        #{$order->id}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px 0;color:#94a3b8;font-size:14px;">
                                        Valor
                                    </td>

                                    <td align="right" style="padding:10px 0;color:#22c55e;font-size:16px;font-weight:bold;">
                                        {$amount}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px 0;color:#94a3b8;font-size:14px;">
                                        Prazo limite
                                    </td>

                                    <td align="right" style="padding:10px 0;color:#f59e0b;font-size:14px;font-weight:bold;">
                                        {$dueAt}
                                    </td>
                                </tr>
                            </table>

                            <div style="margin:32px 0;text-align:center;">
                                <a href="{$panelUrl}"
                                   style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-size:15px;font-weight:bold;">
                                    Acessar área do cliente
                                </a>
                            </div>

                            <p style="margin:0 0 18px 0;font-size:14px;line-height:1.7;color:#cbd5e1;">
                                O não pagamento desta fatura dentro do período de tolerância poderá resultar na
                                suspensão e posterior remoção automática do serviço vinculado.
                            </p>

                            <p style="margin:0;font-size:14px;line-height:1.7;color:#64748b;">
                                Caso o pagamento já tenha sido realizado, desconsidere esta mensagem.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px;background:#0b1120;border-top:1px solid #1e293b;">
                            <p style="margin:0;font-size:13px;color:#64748b;text-align:center;">
                                © VortexHost — Infraestrutura de alta performance.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

    private function deleteRemoteService(CustomerService $service): void
    {
        match ($service->type) {
            'game' => $this->pterodactyl->deleteServer($service),
            'web' => $this->hestia->deleteUser($service),
            default => null,
        };
    }

    private function purgeLocalServiceRecords(CustomerService $service): void
    {
        $lockedService = CustomerService::query()
            ->lockForUpdate()
            ->find($service->id);

        if (! $lockedService) {
            return;
        }

        $initialOrderId = $lockedService->order_id;
        $initialOrderItemId = $lockedService->order_item_id;

        $renewalOrders = Order::query()
            ->where('billing_type', 'renewal')
            ->where('service_id', $lockedService->id)
            ->lockForUpdate()
            ->get();

        foreach ($renewalOrders as $renewalOrder) {
            $renewalOrder->payments()->delete();
            $renewalOrder->items()->delete();
            $renewalOrder->delete();
        }

        $lockedService->pterodactylServer()->delete();
        $lockedService->hestiaAccount()->delete();
        $lockedService->provisioningConfiguration()->delete();
        $lockedService->delete();

        $this->deleteInitialOrderItemIfUnused($initialOrderItemId);
        $this->deleteInitialOrderIfEmpty($initialOrderId);
    }

    private function deleteInitialOrderItemIfUnused(?int $orderItemId): void
    {
        if (! $orderItemId) {
            return;
        }

        $stillUsed = CustomerService::query()
            ->where('order_item_id', $orderItemId)
            ->exists();

        if (! $stillUsed) {
            OrderItem::query()
                ->whereKey($orderItemId)
                ->delete();
        }
    }

    private function deleteInitialOrderIfEmpty(?int $orderId): void
    {
        if (! $orderId) {
            return;
        }

        $order = Order::query()
            ->withCount(['items', 'services'])
            ->lockForUpdate()
            ->find($orderId);

        if (! $order || $order->items_count > 0 || $order->services_count > 0) {
            return;
        }

        $order->payments()->delete();
        $order->delete();
    }
}
