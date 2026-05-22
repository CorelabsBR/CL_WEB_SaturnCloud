<?php

namespace App\Services;

use App\Models\CustomerService;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\PterodactylServer;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PterodactylService
{
    public function healthCheck(): array
    {
        $response = $this->client()->get('/api/application/users', [
            'per_page' => 1,
        ]);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?: $response->body(),
        ];
    }

    public function provision(CustomerService $service, OrderItem $item): PterodactylServer
    {
        $service->loadMissing(['product', 'order.user']);

        $plan = Plan::query()->where('plan', $service->product->plan)->firstOrFail();
        $egg = DB::table('pterodactyl_eggs')->where('id', $plan->egg)->first();

        if (! $egg) {
            throw new \RuntimeException("Egg local do plano {$plan->plan} não encontrado.");
        }

        $pterodactylUser = $this->findOrCreateUser($service->order->user);
        $payload = $this->serverPayload($service, $plan, (int) $egg->egg_id, $pterodactylUser['id']);
        $existingServer = $this->findServerByExternalId($payload['external_id']);

        if ($existingServer) {
            return $this->recordServer($service, $plan, $pterodactylUser['id'], (int) $egg->egg_id, $payload, $existingServer);
        }

        $response = $this->client()->post('/api/application/servers', $payload);

        if (! $response->successful()) {
            Log::error('Pterodactyl recusou criação de servidor', [
                'service_id' => $service->id,
                'plan_id' => $plan->plan,
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
            ]);

            throw new \RuntimeException("Pterodactyl HTTP {$response->status()}: {$response->body()}");
        }

        return $this->recordServer($service, $plan, $pterodactylUser['id'], (int) $egg->egg_id, $payload, $response->json());
    }

    private function findOrCreateUser(User $user): array{
        $externalId = 'vortex-user-' . $user->id;

        // procura por external id
        $response = $this->client()->get("/api/application/users/external/{$externalId}");

        if ($response->successful()) {
            return data_get($response->json(), 'attributes', []);
        }

        if ($response->status() !== 404) {
            throw new \RuntimeException(
                "Pterodactyl user lookup HTTP {$response->status()}: {$response->body()}"
            );
        }

        // procura por email
        $search = $this->client()->get('/api/application/users', [
            'filter[email]' => $user->email,
        ]);

        if ($search->successful()) {

            $data = data_get($search->json(), 'data', []);

            if (! empty($data)) {

                $existing = $data[0]['attributes'];

                Log::info('Usuário já existe no pterodactyl pelo email', [
                    'email' => $user->email,
                    'ptero_user_id' => $existing['id'],
                ]);

                return $existing;
            }
        }

        // cria usuário
        $names = Str::of($user->nome)->trim()->explode(' ')->filter()->values();

        $create = $this->client()->post('/api/application/users', [
            'external_id' => $externalId,
            'email' => $user->email,
            'username' => $this->username($user),
            'first_name' => $names->first() ?: 'Cliente',
            'last_name' => $names->count() > 1
                ? $names->slice(1)->join(' ')
                : 'VortexHost',
            'language' => config('services.pterodactyl.user_language', 'pt-br'),
            'root_admin' => false,
        ]);

        if (! $create->successful()) {

            Log::error('Pterodactyl recusou criação de usuário', [
                'user_id' => $user->id,
                'status' => $create->status(),
                'body' => $create->json() ?: $create->body(),
            ]);

            throw new \RuntimeException(
                "Pterodactyl create user HTTP {$create->status()}: {$create->body()}"
            );
        }

        return data_get($create->json(), 'attributes', []);
    }

    private function serverPayload(CustomerService $service, Plan $plan, int $eggId, int $pterodactylUserId): array
    {
        $nodeId = config('services.pterodactyl.node_id') ?: $this->nodeId($plan);
        $locationId = config('services.pterodactyl.location_id') ?: $plan->location_id;
        $allocationId = config('services.pterodactyl.allocation_id');

        $payload = [
            'name' => $service->name ?: "{$service->product->nome} #{$service->id}",
            'external_id' => 'vortex-service-' . $service->id,
            'user' => $pterodactylUserId,
            'egg' => $eggId,
            'docker_image' => config('services.pterodactyl.docker_image'),
            'startup' => config('services.pterodactyl.startup'),
            'environment' => $this->environment(),
            'limits' => [
                'memory' => $plan->ram > 0 ? $plan->ram : (int) config('services.pterodactyl.unlimited_memory', 0),
                'swap' => (int) config('services.pterodactyl.swap', 0),
                'disk' => $plan->disk > 0 ? $plan->disk : (int) config('services.pterodactyl.unlimited_disk', 0),
                'io' => (int) config('services.pterodactyl.io', 500),
                'cpu' => $plan->cpu ?: (int) config('services.pterodactyl.cpu', 0),
                'oom_disabled' => (bool) config('services.pterodactyl.oom_disabled', false),
            ],
            'feature_limits' => [
                'databases' => (int) config('services.pterodactyl.databases', 1),
                'allocations' => (int) config('services.pterodactyl.allocations', 1),
                'backups' => (int) config('services.pterodactyl.backups', 1),
            ],
        ];

        if ($nodeId) {
            $payload['node'] = (int) $nodeId;
        }

        if ($allocationId) {
            $payload['allocation'] = ['default' => (int) $allocationId];
        } else {
            $payload['deploy'] = [
                'locations' => [(int) $locationId],
                'dedicated_ip' => false,
                'port_range' => [],
            ];
        }

        return array_filter($payload, fn ($value) => $value !== null && $value !== '');
    }

    private function client(): PendingRequest
    {
        if (! config('services.pterodactyl.url') || ! config('services.pterodactyl.api_key')) {
            throw new \RuntimeException('Credenciais Pterodactyl não configuradas.');
        }

        return Http::baseUrl(rtrim(config('services.pterodactyl.url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withToken(config('services.pterodactyl.api_key'))
            ->timeout(30)
            ->retry(2, 250, null, false);
    }

    private function findServerByExternalId(string $externalId): ?array
    {
        $response = $this->client()->get("/api/application/servers/external/{$externalId}");

        if ($response->successful()) {
            return $response->json();
        }

        if ($response->status() === 404) {
            return null;
        }

        if ($response->status() === 403) {
            Log::error('Pterodactyl negou consulta de servidor por external_id', [
                'external_id' => $externalId,
                'body' => $response->json() ?: $response->body(),
            ]);
        }

        throw new \RuntimeException("Pterodactyl server lookup HTTP {$response->status()}: {$response->body()}");
    }

    private function recordServer(
        CustomerService $service,
        Plan $plan,
        int $pterodactylUserId,
        int $eggId,
        array $payload,
        array $rawResponse
    ): PterodactylServer {
        $server = data_get($rawResponse, 'attributes', $rawResponse);

        $record = PterodactylServer::updateOrCreate(
            ['service_id' => $service->id],
            [
                'plan_id' => $plan->plan,
                'pterodactyl_user_id' => $pterodactylUserId,
                'pterodactyl_server_id' => $server['id'] ?? null,
                'uuid' => $server['uuid'] ?? null,
                'identifier' => $server['identifier'] ?? null,
                'allocation_id' => data_get($payload, 'allocation.default'),
                'node_id' => $payload['node'] ?? null,
                'egg_id' => $eggId,
                'raw_response' => $rawResponse,
            ]
        );

        $service->update([
            'status' => 'active',
            'domain' => $server['identifier'] ?? null,
        ]);

        return $record;
    }

private function environment(): array
{
    return [
        'VANILLA_VERSION' => '1.20.1',
        'SERVER_JARFILE' => 'server.jar',
    ];
}

    private function username(User $user): string
    {
        $base = Str::lower(Str::before($user->email, '@'));
        $base = preg_replace('/[^a-z0-9]/', '', $base) ?: 'cliente';

        return substr($base, 0, 20) . $user->id;
    }

    private function nodeId(Plan $plan): ?int
    {
        $node = DB::table('pterodactyl_nodes')
            ->where('locationid', $plan->location_id)
            ->orderBy('id')
            ->first();

        return $node?->id;
    }
}
