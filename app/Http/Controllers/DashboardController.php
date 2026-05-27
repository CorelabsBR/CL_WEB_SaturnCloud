<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\PterodactylService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(PterodactylService $pterodactyl)
    {
        $userId = Auth::id();
        $user = Auth::user();

        try {
            $pterodactyl->pruneDeletedServersForUser($userId);
        } catch (\Throwable $e) {
            Log::warning('Falha ao sincronizar servidores removidos do Pterodactyl no dashboard', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        $services = DB::table('services as s')
            ->leftJoin('products as p', 's.product_id', '=', 'p.id')
            ->leftJoin('orders as o', 's.order_id', '=', 'o.id')
            ->leftJoin('pterodactyl_servers as ps', 's.id', '=', 'ps.service_id')
            ->leftJoin('hestia_accounts as ha', 's.id', '=', 'ha.service_id')
            ->leftJoin('service_provisioning_configurations as spc', 's.id', '=', 'spc.service_id')
            ->select(
                's.id',
                's.user_id',
                's.order_id',
                's.order_item_id',
                's.product_id',
                's.name',
                's.status',
                's.type',
                's.domain',
                's.provision_identifier',
                's.expires_at',
                's.created_at',
                's.updated_at',
                'p.nome as product_name',
                'p.plan as product_plan',
                'p.preco as product_price',
                'p.ram',
                'p.armazenamento',
                'p.processamento',
                'o.status as original_order_status',
                'spc.last_error as provisioning_last_error',
                'ps.identifier as pterodactyl_identifier',
                'ha.hestia_user',
                'ha.domain as hestia_domain'
            )
            ->selectRaw(
                'exists(select 1 from payments pay where pay.order_id = s.order_id and pay.status = ?) as has_approved_payment',
                ['approved']
            )
            ->where('s.user_id', $userId)
            ->orderByDesc('s.created_at')
            ->get();

        $this->expirePendingInvoices($userId);
        $this->createRenewalInvoices($services);

        $invoices = $this->dashboardInvoices($userId);

        $services = $services->map(function ($service) {
                $status = match ($service->status) {
                    'active' => 'ativo',
                    'pending' => 'pendente',
                    'provisioning' => 'pendente',
                    'suspended' => 'suspenso',
                    'cancelled' => 'cancelado',
                    'failed' => 'offline',
                    default => 'ativo',
                };

                $name = $service->name ?: $service->product_name ?: 'Serviço Vortex';
                $plan = $service->product_name
                    ? $service->product_name
                    : ($service->product_plan ? ucfirst($service->product_plan) : ucfirst($service->type ?: 'Plano'));
                $lastError = $service->provisioning_last_error
                    ? json_decode($service->provisioning_last_error, true)
                    : null;

                return [
                    'id' => $service->id,
                    'display_id' => $this->displayId('SRV', $service->id),
                    'provision_identifier' => $service->provision_identifier,
                    'name' => $name,
                    'status' => $status,
                    'type' => $service->type,
                    'ip' => $service->domain ?: 'Aguardando provisionamento',
                    'plan' => $plan,
                    'price' => $service->product_price
                        ? 'R$ ' . number_format((float) $service->product_price, 2, ',', '.')
                        : 'Sob consulta',
                    'location' => $service->domain ?: 'São Paulo, BR',
                    'usage' => 'Sem leitura recente',
                    'created_at' => $service->created_at
                        ? date('d/m/Y', strtotime($service->created_at))
                        : '--/--/----',
                    'activity_at' => $service->updated_at ?: $service->created_at,
                    'expires_at' => $service->expires_at
                        ? date('d/m/Y', strtotime($service->expires_at))
                        : 'Sem renovação definida',
                    'resources' => $this->buildServiceResources($service->ram, $service->armazenamento, $service->processamento),
                    'panel' => $this->servicePanelLink($service),
                    'configuration_url' => $service->type === 'game' && data_get($lastError, 'type') === 'missing_required_egg_variables'
                        ? route('pterodactyl.configure.edit', $service->id)
                        : null,
                    'invoice' => $this->serviceInvoiceStatus(
                        $service->original_order_status,
                        (bool) $service->has_approved_payment,
                        $service->status
                    ),
                ];
            });

        $stats = $this->buildStats($user, $services, $invoices);
        $profile = $this->buildProfileSummary($user);
        $activities = $this->buildActivities($services, $invoices, $user);

        return view('dashboard.index', compact(
            'services',
            'invoices',
            'activities',
            'stats',
            'profile'
        ));
    }

    private function buildStats($user, $services, $invoices): array
    {
        $pendingInvoices = $invoices->where('status', 'pendente');
        $paidInvoices = $invoices->where('status', 'pago');
        $lastPayment = $this->lastPaymentSummary($user?->id);
        $nextInvoice = $pendingInvoices
            ->sortBy(fn ($invoice) => data_get($invoice, 'due_at_raw')?->timestamp ?? PHP_INT_MAX)
            ->first();

        return [
            'active_services' => $services->where('status', 'ativo')->count(),
            'pending_services' => $services->where('status', 'pendente')->count(),
            'suspended_services' => $services->where('status', 'suspenso')->count(),
            'total_services' => $services->count(),
            'pending_invoices' => $pendingInvoices->count(),
            'paid_invoices' => $paidInvoices->count(),
            'pending_amount' => $pendingInvoices->sum('amount'),
            'paid_amount' => $paidInvoices->sum('amount'),
            'last_payment' => $lastPayment,
            'next_invoice' => $nextInvoice,
            'account_created_at' => $this->modelTimestamp($user, 'created')?->format('d/m/Y') ?? '--/--/----',
            'account_age_days' => $this->modelTimestamp($user, 'created')
                ? (int) floor($this->modelTimestamp($user, 'created')->diffInDays(now()))
                : 0,
        ];
    }

    private function expirePendingInvoices(int $userId): void
    {
        Order::where('user_id', $userId)
            ->where('status', 'pendente')
            ->where('billing_type', 'renewal')
            ->where('due_at', '<=', now())
            ->update(['status' => 'cancelado']);
    }

    private function createRenewalInvoices($services): void
    {
        foreach ($services as $service) {
            if ($service->status !== 'active' || ! $service->expires_at || Carbon::parse($service->expires_at)->isFuture()) {
                continue;
            }

            if (! $this->serviceAllowsRenewalInvoice($service)) {
                continue;
            }

            $exists = Order::where('user_id', $service->user_id)
                ->where('billing_type', 'renewal')
                ->where('service_id', $service->id)
                ->whereIn('status', ['pendente', 'pago'])
                ->exists();

            if ($exists) {
                continue;
            }

            $order = Order::create([
                'user_id' => $service->user_id,
                'total' => $service->product_price ?: 0,
                'status' => 'pendente',
                'billing_type' => 'renewal',
                'service_id' => $service->id,
                'due_at' => now()->addDays(5),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $service->product_id,
                'quantidade' => 1,
                'preco' => $service->product_price ?: 0,
            ]);
        }
    }

    private function dashboardInvoices(int $userId)
    {
        return Order::with(['items.product', 'renewalService'])
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query
                    ->whereHas('items.product.categoria', fn ($category) => $category->where('nome', 'like', '%VPS%'))
                    ->orWhereHas('renewalService.product.categoria', fn ($category) => $category->where('nome', 'like', '%VPS%'));
            })
            ->where(function ($query) {
                $query
                    ->where('status', '!=', 'cancelado')
                    ->orWhere('billing_type', '!=', 'renewal')
                    ->orWhere('due_at', '>', now()->subMinutes(5));
            })
            ->orderByDesc('id')
            ->get()
            ->map(function (Order $order) {
                $description = $order->items->map(function ($item) {
                    return $item->product?->nome ?? "Item #{$item->id}";
                })->filter()->unique()->values()->join(', ');

                $createdAt = $this->modelTimestamp($order, 'created');

                return [
                    'id' => $order->id,
                    'display_id' => $this->displayId('VTX', $order->id),
                    'status' => $order->status,
                    'billing_type' => $order->billing_type,
                    'description' => $description ?: ($order->billing_type === 'renewal' ? 'Renovação de mensalidade' : 'Compra inicial'),
                    'amount' => $order->total,
                    'created_at' => $createdAt?->format('d/m/Y H:i') ?? 'Sem data',
                    'created_at_raw' => $createdAt,
                    'due_at' => $order->billing_type === 'renewal'
                        ? ($order->due_at?->format('d/m/Y H:i') ?? 'Sem vencimento')
                        : ($createdAt?->format('d/m/Y H:i') ?? 'Sem data'),
                    'due_at_raw' => $order->due_at,
                    'due_at_iso' => $order->billing_type === 'renewal' ? $order->due_at?->toIso8601String() : null,
                ];
            });
    }

    private function serviceAllowsRenewalInvoice($service): bool
    {
        if (! $service->order_id) {
            return false;
        }

        $payment = DB::table('payments')
            ->where('order_id', $service->order_id)
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->first();

        $method = strtolower((string) ($payment->payment_method_id ?? ''));
        $type = strtolower((string) ($payment->payment_type_id ?? ''));

        return in_array($method, ['pix', 'bolbradesco', 'boleto'], true)
            || in_array($type, ['ticket', 'bank_transfer'], true);
    }

    private function servicePanelLink($service): ?array
    {
        if ($service->type === 'game') {
            $baseUrl = rtrim((string) config('services.pterodactyl.url'), '/');

            if (! $baseUrl) {
                return null;
            }

            return [
                'label' => 'Painel do jogo',
                'url' => $service->pterodactyl_identifier
                    ? "{$baseUrl}/server/{$service->pterodactyl_identifier}"
                    : $baseUrl,
            ];
        }

        if ($service->type === 'web') {
            if (! config('services.hestia.url')) {
                return null;
            }

            return [
                'label' => 'Painel do site',
                'url' => route('hestia.panel', $service->id),
            ];
        }

        return null;
    }

    private function lastPaymentSummary(?int $userId): ?array
    {
        if (! $userId) {
            return null;
        }

        $lastPaidOrder = Order::query()
            ->where('user_id', $userId)
            ->where('status', 'pago')
            ->latest('atualizado_em')
            ->first();

        if ($lastPaidOrder) {
            return [
                'amount' => $lastPaidOrder->total,
                'paid_at' => $this->modelTimestamp($lastPaidOrder, 'updated')?->format('d/m/Y H:i'),
                'display_id' => $this->displayId('VTX', $lastPaidOrder->id),
            ];
        }

        $lastApprovedPayment = Payment::query()
            ->where('status', 'approved')
            ->whereHas('order', fn ($query) => $query->where('user_id', $userId))
            ->with('order')
            ->latest('updated_at')
            ->first();

        if (! $lastApprovedPayment) {
            return null;
        }

        return [
            'amount' => $lastApprovedPayment->amount ?: $lastApprovedPayment->order?->total,
            'paid_at' => $this->modelTimestamp($lastApprovedPayment, 'updated')?->format('d/m/Y H:i'),
            'display_id' => $this->displayId('VTX', $lastApprovedPayment->order_id),
        ];
    }

    private function serviceInvoiceStatus(?string $status, bool $hasApprovedPayment = false, ?string $serviceStatus = null): array
    {
        if ($hasApprovedPayment || $serviceStatus === 'active') {
            return [
                'label' => 'Fatura paga',
                'class' => 'status-pago',
            ];
        }

        return match ($status) {
            'pago' => [
                'label' => 'Fatura paga',
                'class' => 'status-pago',
            ],
            'cancelado' => [
                'label' => 'Fatura cancelada',
                'class' => 'status-suspenso',
            ],
            default => [
                'label' => 'Fatura pendente',
                'class' => 'status-pendente',
            ],
        };
    }

    private function modelTimestamp($model, string $type)
    {
        if (! $model) {
            return null;
        }

        $column = $type === 'updated'
            ? $model->getUpdatedAtColumn()
            : $model->getCreatedAtColumn();

        $value = data_get($model, $column)
            ?: data_get($model, $type === 'updated' ? 'updated_at' : 'created_at');

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        return $value ? Carbon::parse($value) : null;
    }

    private function displayId(string $prefix, int|string|null $id): string
    {
        return $prefix . '-' . str_pad((string) ($id ?: 0), 6, '0', STR_PAD_LEFT);
    }

    private function buildProfileSummary($user): array
    {
        $fields = [
            'nome' => 'Nome',
            'email' => 'E-mail',
            'cpf_cnpj' => 'CPF/CNPJ',
            'celular' => 'Celular',
            'cep' => 'CEP',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
        ];

        $filled = collect($fields)
            ->filter(fn ($label, $field) => filled(data_get($user, $field)));

        return [
            'completion' => count($fields) > 0
                ? (int) round(($filled->count() / count($fields)) * 100)
                : 0,
            'missing' => collect($fields)
                ->reject(fn ($label, $field) => filled(data_get($user, $field)))
                ->values(),
            'phone' => $user?->celular ?: 'Não informado',
            'document' => $user?->cpf_cnpj ?: 'Não informado',
            'address' => collect([
                $user?->rua,
                $user?->numero,
                $user?->bairro,
                $user?->cidade,
                $user?->estado,
            ])->filter()->join(', ') ?: 'Endereço não informado',
            'email_verified' => (bool) $user?->email_verified_at,
            'profile_completed' => method_exists($user, 'hasCompletedProfile')
                ? $user->hasCompletedProfile()
                : $filled->count() === count($fields),
        ];
    }

    private function buildServiceResources(?string $ram, ?string $storage, ?string $cpu): array
    {
        $resources = [];

        if ($ram) {
            $resources[] = [
                'label' => 'RAM',
                'value' => $this->metricValueFromSpec($ram, 64),
            ];
        }

        if ($storage) {
            $resources[] = [
                'label' => 'Disco',
                'value' => $this->metricValueFromSpec($storage, 500),
            ];
        }

        if ($cpu) {
            $resources[] = [
                'label' => 'CPU',
                'value' => $this->metricValueFromSpec($cpu, 16),
            ];
        }

        return $resources;
    }

    private function metricValueFromSpec(string $value, int $max): int
    {
        if (preg_match('/(\d+(?:[.,]\d+)?)/', $value, $matches)) {
            $quantity = (float) str_replace(',', '.', $matches[1]);
            return min(100, max(0, (int) round(($quantity / $max) * 100)));
        }

        return 0;
    }

    private function buildActivities($services, $invoices, $user)
    {
        $activities = collect();

        foreach ($services as $service) {
            $activityAt = $this->activityTimestamp(data_get($service, 'activity_at'));

            $activities->push([
                'title' => data_get($service, 'status') === 'ativo'
                    ? "Serviço ativo: {$service['name']}"
                    : "{$service['name']} " . ucfirst((string) data_get($service, 'status', 'pendente')),
                'description' => "Serviço em " . data_get($service, 'location', 'local não informado') . ".",
                'time' => $this->activityTimeLabel($activityAt),
                'sort_at' => $activityAt,
            ]);
        }

        foreach ($invoices as $invoice) {
            $activityAt = $this->activityTimestamp(data_get($invoice, 'created_at_raw'));

            $activities->push([
                'title' => 'Fatura ' . data_get($invoice, 'display_id', $this->displayId('VTX', data_get($invoice, 'id'))) . ' ' . ucfirst($invoice['status']),
                'description' => 'Valor R$ ' . number_format((float) $invoice['amount'], 2, ',', '.'),
                'time' => $this->activityTimeLabel($activityAt),
                'sort_at' => $activityAt,
            ]);
        }

        if ($activities->isEmpty()) {
            $activityAt = $this->modelTimestamp($user, 'created');

            $activities->push([
                'title' => 'Conta criada',
                'description' => 'Sua conta foi criada com sucesso.',
                'time' => $this->activityTimeLabel($activityAt),
                'sort_at' => $activityAt,
            ]);
        }

        return $activities
            ->sortByDesc(fn ($activity) => data_get($activity, 'sort_at')?->timestamp ?? 0)
            ->take(6)
            ->values();
    }

    private function activityTimestamp($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        return Carbon::parse($value);
    }

    private function activityTimeLabel(?Carbon $timestamp): string
    {
        if (! $timestamp) {
            return 'Sem data';
        }

        $diffInMinutes = (int) $timestamp->diffInMinutes(now());

        if ($diffInMinutes < 1) {
            return 'Agora';
        }

        if ($diffInMinutes < 60) {
            return 'há ' . $diffInMinutes . ' min';
        }

        if ($diffInMinutes < 1440) {
            $hours = (int) floor($diffInMinutes / 60);

            return 'há ' . $hours . ' h';
        }

        if ($diffInMinutes < 10080) {
            $days = (int) floor($diffInMinutes / 1440);

            return 'há ' . $days . ' d';
        }

        return $timestamp->format('d/m/Y');
    }
}
