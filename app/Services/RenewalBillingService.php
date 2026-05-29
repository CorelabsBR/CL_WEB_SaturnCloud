<?php

namespace App\Services;

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
        return [
            'terminated' => $this->terminateOverdueServices($userId),
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
            Mail::raw($this->monthlyInvoiceEmailBody($order, $user), function ($message) use ($order, $user) {
                $message->to($user->email)
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
        $serviceName = $service?->name ?: $order->items->first()?->product?->nome ?: 'Serviço VortexHost';
        $amount = 'R$ ' . number_format((float) $order->total, 2, ',', '.');
        $dueAt = $order->due_at?->format('d/m/Y H:i') ?: 'em ate 5 dias';
        $panelUrl = route('dashboard.index') . '#faturas';
        $firstName = trim(explode(' ', (string) $user->nome)[0] ?? '') ?: 'Cliente';

        return <<<TEXT
Olá, {$firstName}.

Identificamos que a mensalidade do serviço abaixo ainda não foi paga:

Serviço: {$serviceName}
Fatura: #{$order->id}
Valor: {$amount}
Prazo para pagamento: {$dueAt}

Você tem até 5 dias para pagar esta fatura. Se o pagamento não for identificado dentro desse prazo, o servidor vinculado a esta fatura será removido automaticamente.

Para pagar, acesse sua área do cliente:
{$panelUrl}

VortexHost
TEXT;
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
