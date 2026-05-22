<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Order;
use App\Services\ProvisioningService;
use App\Services\PterodactylService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pterodactyl:check', function (PterodactylService $pterodactyl) {
    try {
        $result = $pterodactyl->healthCheck();
    } catch (\Throwable $e) {
        $this->error('Pterodactyl falhou antes de responder: ' . $e->getMessage());
        return self::FAILURE;
    }

    if ($result['ok']) {
        $this->info('Pterodactyl OK: chave Application API aceita.');
        return self::SUCCESS;
    }

    $this->error('Pterodactyl falhou com HTTP ' . $result['status']);
    $this->line(json_encode($result['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return self::FAILURE;
})->purpose('Testa se a chave Application API do Pterodactyl tem permissão de leitura.');

Artisan::command('provisioning:retry-paid {order_id?}', function (ProvisioningService $provisioning) {
    $orderId = $this->argument('order_id');

    $orders = Order::query()
        ->where('status', 'pago')
        ->where('billing_type', '!=', 'renewal')
        ->when($orderId, fn ($query) => $query->whereKey($orderId))
        ->whereHas('services', fn ($query) => $query->whereIn('status', ['pending', 'provisioning', 'failed']))
        ->with(['services', 'items.product.categoria', 'user'])
        ->get();

    if ($orders->isEmpty()) {
        $this->info('Nenhum pedido pago pendente/falho para reprocessar.');
        return self::SUCCESS;
    }

    foreach ($orders as $order) {
        $this->line("Reprocessando pedido #{$order->id}...");
        $provisioning->provisionOrder($order);
    }

    $this->info('Reprocessamento concluído.');

    return self::SUCCESS;
})->purpose('Reprocessa provisionamento de pedidos pagos que ainda não ficaram ativos.');
