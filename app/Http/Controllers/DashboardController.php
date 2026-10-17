<?php

namespace App\Http\Controllers;

use App\Models\CustomerService;
use App\Models\Order;
use App\Models\Payment;
use App\Services\RenewalBillingService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(RenewalBillingService $billing)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $billing->purgeExpiredInitialPendingOrders($userId);

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
                's.cancellation_requested_at',
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
                    'cancellation_requested_at' => $service->cancellation_requested_at
                        ? date('d/m/Y H:i', strtotime($service->cancellation_requested_at))
                        : null,
                    'cancellation_identifier' => $this->serviceCancellationIdentifier($service),
                    'is_cancellation_scheduled' => (bool) $service->cancellation_requested_at,
                    'can_schedule_cancellation' => $service->status === 'active'
                        && ! $service->cancellation_requested_at
                        && (bool) $service->expires_at,
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

    public function cancelService(Request $request, CustomerService $service)
    {
        abort_if((int) $service->user_id !== (int) Auth::id(), 403);

        $servicesUrl = route('dashboard.index') . '#servicos';
        $expectedIdentifier = $this->serviceCancellationIdentifier($service);
        $typedIdentifier = trim((string) $request->input('service_identifier'));

        if ($typedIdentifier === '' || $typedIdentifier !== $expectedIdentifier) {
            return redirect()
                ->to($servicesUrl)
                ->with('error', "Digite exatamente {$expectedIdentifier} para confirmar o cancelamento.");
        }

        if ($service->status !== 'active') {
            return redirect()
                ->to($servicesUrl)
                ->with('error', 'Somente serviços ativos podem ter cancelamento agendado.');
        }

        if (! $service->expires_at) {
            return redirect()
                ->to($servicesUrl)
                ->with('error', 'Este serviço não possui vencimento definido para agendar cancelamento.');
        }

        if ($service->cancellation_requested_at) {
            return redirect()
                ->to($servicesUrl)
                ->with('success', 'O cancelamento deste plano já estava agendado.');
        }

        DB::transaction(function () use ($service) {
            $service->forceFill([
                'cancellation_requested_at' => now(),
            ])->save();

            Order::query()
                ->where('billing_type', 'renewal')
                ->where('service_id', $service->id)
                ->where('status', 'pendente')
                ->update(['status' => 'cancelado']);
        });

        return redirect()
            ->to($servicesUrl)
            ->with('success', 'Cancelamento agendado. O plano fica ativo até o vencimento e não será cobrado no próximo mês.');
    }

    private function serviceCancellationIdentifier($service): string
    {
        return (string) (
            $service->provision_identifier
            ?: $service->domain
            ?: $this->displayId('SRV', $service->id)
        );
    }

    public function processRenewals(RenewalBillingService $billing)
    {
        $userId = Auth::id();

        try {
            $result = $billing->processUser($userId);
        } catch (\Throwable $e) {
            Log::warning('Falha ao processar renovações em background', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'changed' => false,
            ], 202);
        }

        return response()->json([
            'ok' => true,
            'changed' => ($result['generated'] ?? 0) > 0
                || ($result['terminated'] ?? 0) > 0
                || ($result['expired_initial_orders'] ?? 0) > 0,
            'generated' => $result['generated'] ?? 0,
            'terminated' => $result['terminated'] ?? 0,
            'expired_initial_orders' => $result['expired_initial_orders'] ?? 0,
        ]);
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

    private function dashboardInvoices(int $userId)
    {
        return Order::with(['items.product', 'renewalService'])
            ->where('user_id', $userId)
            ->where('status', 'pendente')
            ->where('billing_type', 'renewal')
            ->whereNotNull('service_id')
            ->whereHas('renewalService')
            ->where(function ($query) {
                $query
                    ->whereNull('due_at')
                    ->orWhere('due_at', '>', now()->subDays(5));
            })
            ->orderByDesc('id')
            ->get()
            ->map(function (Order $order) {
                $description = $order->items->map(function ($item) {
                    return $item->product?->nome ?? "Item #{$item->id}";
                })->filter()->unique()->values()->join(', ');

                $createdAt = $this->modelTimestamp($order, 'created');
                $isRenewal = $order->billing_type === 'renewal';
                $expiresAt = $isRenewal
                    ? $order->due_at
                    : $createdAt?->copy()->addDay();

                return [
                    'id' => $order->id,
                    'display_id' => $this->displayId('VTX', $order->id),
                    'status' => $order->status,
                    'billing_type' => $order->billing_type,
                    'renewal_label' => $order->renewalService?->type === 'web'
                        ? 'Renovação anual'
                        : 'Renovação mensal',
                    'description' => $description ?: ($isRenewal ? 'Renovação de mensalidade' : 'Compra inicial'),
                    'amount' => $order->total,
                    'created_at' => $createdAt?->format('d/m/Y H:i') ?? 'Sem data',
                    'created_at_raw' => $createdAt,
                    'due_at' => $expiresAt?->format('d/m/Y H:i') ?? 'Sem vencimento',
                    'due_at_raw' => $expiresAt,
                    'due_at_iso' => $expiresAt?->toIso8601String(),
                ];
            });
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
                'url' => route('ptero.panel', $service->id),
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
