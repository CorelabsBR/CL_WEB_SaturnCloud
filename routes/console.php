<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Order;
use App\Models\User;
use App\Services\HestiaService;
use App\Services\ProvisioningService;
use App\Services\PterodactylService;
use App\Services\RenewalBillingService;
use App\Models\MarketingCampaign;
use App\Models\MarketingContent;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('auth:purge-unverified', function () {
    $deleted = User::query()
        ->whereNull('email_verified_at')
        ->where('criado_em', '<=', now()->subDay())
        ->delete();

    $this->info("Contas não verificadas removidas: {$deleted}");

    return self::SUCCESS;
})->purpose('Remove contas que não verificaram o e-mail em até 1 dia.');

Artisan::command('pterodactyl:check {server_id? : ID numérico de um servidor Pterodactyl para testar permissões de databases}', function (PterodactylService $pterodactyl) {
    try {
        $serverId = $this->argument('server_id') ? (int) $this->argument('server_id') : null;
        $result = $pterodactyl->healthCheck($serverId);
    } catch (\Throwable $e) {
        $this->error('Pterodactyl falhou antes de responder: ' . $e->getMessage());
        return self::FAILURE;
    }

    if ($result['ok']) {
        $this->info('Pterodactyl OK: chave Application API aceita nas permissões testadas.');
        return self::SUCCESS;
    }

    foreach ($result['checks'] as $name => $check) {
        if ($check['ok']) {
            $this->info("{$name}: OK");
            continue;
        }

        $this->error("{$name}: falhou com HTTP {$check['status']}");
        $this->line(json_encode($check['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    return self::FAILURE;
})->purpose('Testa se a chave Application API do Pterodactyl tem as permissões necessárias.');

Artisan::command('pterodactyl:sync-eggs', function (PterodactylService $pterodactyl) {
    $synced = $pterodactyl->syncEggMetadata();

    if (empty($synced)) {
        $this->warn('Nenhuma egg local foi sincronizada.');
        return self::SUCCESS;
    }

    $this->table(['egg_id', 'name', 'variables', 'required_variables'], $synced);

    return self::SUCCESS;
})->purpose('Sincroniza startup, docker image e variáveis das eggs salvas no banco.');

Artisan::command('provisioning:retry-paid {order_id?} {--failed : Reprocessa também serviços que já estão failed}', function (ProvisioningService $provisioning) {
    $orderId = $this->argument('order_id');
    $statuses = $this->option('failed')
        ? ['pending', 'provisioning', 'failed']
        : ['pending', 'provisioning'];

    $orders = Order::query()
        ->where('status', 'pago')
        ->where('billing_type', '!=', 'renewal')
        ->when($orderId, fn ($query) => $query->whereKey($orderId))
        ->where(function ($query) use ($statuses) {
            $query
                ->whereDoesntHave('services')
                ->orWhereHas('services', fn ($services) => $services->whereIn('status', $statuses));
        })
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

Artisan::command('hestia:process-queue {order_id?} {--limit=50}', function (HestiaService $hestia) {
    $orderId = $this->argument('order_id');
    $result = $hestia->processQueue(
        (int) $this->option('limit'),
        $orderId ? (int) $orderId : null
    );

    foreach ($result['processed'] as $serviceId) {
        $this->info("Serviço #{$serviceId} criado no Hestia.");
    }

    foreach ($result['skipped'] as $skip) {
        $this->warn("Serviço #{$skip['service_id']} ignorado: {$skip['reason']}");
    }

    foreach ($result['failed'] as $failure) {
        $this->error("Serviço #{$failure['service_id']} falhou: {$failure['error']}");
    }

    if (empty($result['processed']) && empty($result['skipped']) && empty($result['failed'])) {
        $this->info($orderId
            ? "Nenhum pedido Hestia pendente para o pedido #{$orderId}."
            : 'Nenhum pedido Hestia pendente.'
        );
    }

    return empty($result['failed']) ? self::SUCCESS : self::FAILURE;
})->purpose('Processa a fila local de criação de usuários Hestia.');

Artisan::command('billing:process-renewals {--user_id= : Processa apenas um cliente}', function (RenewalBillingService $billing) {
    $userId = $this->option('user_id') ? (int) $this->option('user_id') : null;
    $result = $billing->process($userId);

    $this->info("Compras iniciais pendentes removidas: {$result['expired_initial_orders']}");
    $this->info("Faturas mensais criadas: {$result['generated']}");
    $this->info("Serviços vencidos apagados: {$result['terminated']}");

    return self::SUCCESS;
})->purpose('Remove compras iniciais vencidas, cria faturas mensais e apaga serviços vencidos.');

Schedule::command('hestia:process-queue')
    ->everyMinute();

Schedule::command('billing:process-renewals')
    ->everyMinute();

Artisan::command('marketing:process-schedule', function () {
    $campaigns = MarketingCampaign::where('status', 'scheduled')->whereNotNull('approved_at')->where('starts_at', '<=', now())->update(['status' => 'active']);
    $contents = MarketingContent::where('status', 'scheduled')->whereNotNull('approved_by')->where('scheduled_at', '<=', now())->update(['status' => 'published', 'published_at' => now()]);
    $this->info("Campanhas ativadas: {$campaigns}; conteúdos publicados: {$contents}.");
    return self::SUCCESS;
})->purpose('Processa apenas campanhas e conteúdos previamente aprovados e agendados.');

Schedule::command('marketing:process-schedule')->everyMinute()->withoutOverlapping();
