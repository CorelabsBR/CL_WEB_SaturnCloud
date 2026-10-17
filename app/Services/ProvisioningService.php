<?php

namespace App\Services;

use App\Exceptions\MissingPterodactylEggVariablesException;
use App\Models\CustomerService;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProvisioningService
{
    public function __construct(
        private readonly HestiaService $hestia,
        private readonly PterodactylService $pterodactyl,
    ) {
    }

    public function provisionOrder(Order $order): void
    {
        $order->loadMissing(['items.product.categoria', 'user']);

        foreach ($order->items as $item) {
            for ($copy = 1; $copy <= $item->quantidade; $copy++) {
                $service = $this->firstOrCreateService($order, $item, $copy);

                if ($service->status === 'active') {
                    continue;
                }

                $service->update(['status' => 'provisioning']);

                try {
                    match ($service->type) {
                        'web' => $this->hestia->provision($service, $item),
                        'game' => $this->pterodactyl->provision($service, $item),
                        default => $service->update(['status' => 'pending']),
                    };
                } catch (MissingPterodactylEggVariablesException $e) {
                    $service->provisioningConfiguration()->updateOrCreate(
                        ['service_id' => $service->id],
                        [
                            'last_error' => [
                                'type' => 'missing_required_egg_variables',
                                'missing_variables' => $e->variables(),
                                'message' => $e->getMessage(),
                                'failed_at' => now()->toIso8601String(),
                            ],
                        ]
                    );

                    $service->update(['status' => 'pending']);

                    Log::warning('Provisionamento aguardando configuração do servidor', [
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'service_id' => $service->id,
                        'missing_variables' => $e->variables(),
                    ]);
                } catch (\Throwable $e) {
                    $service->update(['status' => 'failed']);

                    Log::error('Falha ao provisionar serviço', [
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'service_id' => $service->id,
                        'type' => $service->type,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    private function firstOrCreateService(Order $order, OrderItem $item, int $copy): CustomerService
    {
        return DB::transaction(function () use ($order, $item, $copy) {
            $existing = CustomerService::query()
                ->where('order_item_id', $item->id)
                ->orderBy('id')
                ->skip($copy - 1)
                ->first();

            if ($existing) {
                if (! $existing->provision_identifier) {
                    $existing->update([
                        'provision_identifier' => $this->provisionIdentifier($existing),
                    ]);
                }

                return $existing;
            }

            $type = $this->typeFor($item);

            $service = CustomerService::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'type' => $type,
                'status' => 'pending',
                'name' => $this->serviceName($item, $copy),
                'expires_at' => $type === 'web' ? now()->addYear() : now()->addMonth(),
            ]);

            $service->update([
                'provision_identifier' => $this->provisionIdentifier($service),
            ]);

            return $service;
        });
    }

    private function provisionIdentifier(CustomerService $service): string
    {
        do {
            $identifier = 'VTX-' . Str::upper(Str::random(12));
        } while (
            CustomerService::query()
                ->where('provision_identifier', $identifier)
                ->whereKeyNot($service->id)
                ->exists()
        );

        return $identifier;
    }

    private function typeFor(OrderItem $item): string
    {
        if ($item->product?->jogo_id || $item->product?->plan) {
            return 'game';
        }

        $category = Str::lower((string) $item->product?->categoria?->nome);

        if (str_contains($category, 'web') || str_contains($category, 'hosped')) {
            return 'web';
        }

        if (str_contains($category, 'jogo') || str_contains($category, 'game')) {
            return 'game';
        }

        if (str_contains($category, 'vps')) {
            return 'vps';
        }

        return match ((int) $item->product?->categoria_id) {
            1 => 'web',
            2 => 'game',
            3 => 'vps',
            default => 'web',
        };
    }

    private function serviceName(OrderItem $item, int $copy): string
    {
        $suffix = $item->quantidade > 1 ? " {$copy}" : '';

        return ($item->product?->nome ?: 'Serviço VortexHost') . $suffix;
    }
}
