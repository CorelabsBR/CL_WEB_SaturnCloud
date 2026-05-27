<?php

namespace App\Services;

use App\Exceptions\MissingPterodactylEggVariablesException;
use App\Models\CustomerService;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\PterodactylEgg;
use App\Models\PterodactylEggVariable;
use App\Models\PterodactylServer;
use App\Models\ServiceProvisioningConfiguration;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PterodactylService
{
    public function pruneDeletedServersForUser(int $userId): int
    {
        if (! config('services.pterodactyl.url') || ! config('services.pterodactyl.api_key')) {
            return 0;
        }

        $services = CustomerService::query()
            ->where('user_id', $userId)
            ->where('type', 'game')
            ->whereHas('pterodactylServer')
            ->with('pterodactylServer')
            ->get();

        $deleted = 0;

        foreach ($services as $service) {
            $server = $service->pterodactylServer;

            if (! $server || $this->remoteServerExists($server)) {
                continue;
            }

            DB::transaction(function () use ($service) {
                $service->pterodactylServer()->delete();
                $service->provisioningConfiguration()->delete();
                $service->delete();
            });

            $deleted++;

            Log::info('Serviço local removido porque o servidor não existe mais no Pterodactyl', [
                'service_id' => $service->id,
                'user_id' => $service->user_id,
                'pterodactyl_server_id' => $server->pterodactyl_server_id,
                'external_id' => $server->external_id,
            ]);
        }

        return $deleted;
    }

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

    public function provision(CustomerService $service, OrderItem $item, array $environmentOverride = []): PterodactylServer
    {
        $this->ensureProvisionIdentifier($service);

        $service->loadMissing([
            'product.planModel.pterodactylEgg.variables',
            'order.user',
            'provisioningConfiguration',
        ]);

        $plan = $service->product?->planModel
            ?: Plan::query()->with('pterodactylEgg.variables')->where('plan', $service->product?->plan)->firstOrFail();

        $egg = $plan->pterodactylEgg;

        if (! $egg) {
            throw new \RuntimeException("Egg local do plano {$plan->plan} não encontrado.");
        }

        if (! $egg->startup || ! $this->dockerImage($egg)) {
            throw new \RuntimeException("Egg {$egg->egg_id} sem startup/docker_image no banco. Rode php artisan pterodactyl:sync-eggs.");
        }

        $pterodactylUser = $this->findOrCreateUser($service->order->user);
        $payload = $this->serverPayload($service, $plan, $egg, $pterodactylUser['id'], $environmentOverride);
        $capacityIssue = $this->nodeCapacityIssue($payload, $plan);

        if ($capacityIssue) {
            $this->configuration($service)->update([
                'last_payload' => $payload,
                'last_error' => [
                    'type' => 'node_capacity',
                    'message' => $capacityIssue,
                    'failed_at' => now()->toIso8601String(),
                ],
            ]);

            throw new \RuntimeException($capacityIssue);
        }

        $this->configuration($service)->update(['last_payload' => $payload]);

        $existingServer = $this->findServerByExternalId($payload['external_id']);

        if ($existingServer) {
            return $this->recordServer($service, $plan, $pterodactylUser['id'], $egg->egg_id, $payload, $existingServer);
        }

        $response = $this->client()->post('/api/application/servers', $payload);

        if (! $response->successful()) {
            $body = $response->json() ?: $response->body();
            $missingVariables = $this->missingVariablesFromResponse($body);

            $this->configuration($service)->update([
                'last_error' => [
                    'status' => $response->status(),
                    'body' => $body,
                    'missing_variables' => $missingVariables,
                    'payload_environment_keys' => array_keys($payload['environment'] ?? []),
                    'failed_at' => now()->toIso8601String(),
                ],
            ]);

            Log::error('Pterodactyl recusou criação de servidor', [
                'service_id' => $service->id,
                'order_item_id' => $item->id,
                'plan_id' => $plan->plan,
                'egg_id' => $egg->egg_id,
                'status' => $response->status(),
                'missing_variables' => $missingVariables,
                'body' => $body,
            ]);

            throw new \RuntimeException("Pterodactyl HTTP {$response->status()}: {$response->body()}");
        }

        return $this->recordServer($service, $plan, $pterodactylUser['id'], $egg->egg_id, $payload, $response->json());
    }

    public function payloadPreview(CustomerService $service): array
    {
        $service->loadMissing([
            'product.planModel.pterodactylEgg.variables',
            'order.user',
            'provisioningConfiguration',
        ]);

        $plan = $service->product?->planModel;
        $egg = $plan?->pterodactylEgg;

        if (! $plan || ! $egg) {
            throw new \RuntimeException('Plano/egg do serviço não encontrado.');
        }

        return $this->serverPayload($service, $plan, $egg, 0);
    }

    public function requiredVariablesWithoutDefault(CustomerService $service): array
    {
        $service->loadMissing('product.planModel.pterodactylEgg.variables', 'provisioningConfiguration');

        $egg = $service->product?->planModel?->pterodactylEgg;

        if (! $egg) {
            return [];
        }

        $environment = $this->configuredEnvironment($service);

        return $egg->variables
            ->filter(fn (PterodactylEggVariable $variable) => $variable->required)
            ->filter(fn (PterodactylEggVariable $variable) => blank($variable->default_value))
            ->filter(fn (PterodactylEggVariable $variable) => blank($environment[$variable->env_variable] ?? null))
            ->values()
            ->all();
    }

    public function saveEnvironment(CustomerService $service, array $environment): ServiceProvisioningConfiguration
    {
        $clean = collect($environment)
            ->mapWithKeys(fn ($value, $key) => [trim((string) $key) => is_scalar($value) ? trim((string) $value) : $value])
            ->filter(fn ($value, $key) => $key !== '' && $value !== null && $value !== '')
            ->all();

        $configuration = $this->configuration($service);
        $configuration->update([
            'environment' => array_replace($configuration->environment ?? [], $clean),
            'last_error' => null,
        ]);

        return $configuration->fresh();
    }

    public function syncEggMetadata(): array
    {
        $synced = [];

        foreach (PterodactylEgg::query()->get() as $localEgg) {
            $nestId = $localEgg->nest_id ?: $this->nestIdForEgg($localEgg->egg_id);

            if (! $nestId) {
                $matchedEgg = $this->findEggByName($localEgg->name);

                if (! $matchedEgg) {
                    Log::warning('Egg não encontrada nos nests do Pterodactyl', [
                        'local_egg_id' => $localEgg->id,
                        'egg_id' => $localEgg->egg_id,
                        'name' => $localEgg->name,
                    ]);

                    continue;
                }

                $localEgg->update([
                    'egg_id' => $matchedEgg['egg_id'],
                    'nest_id' => $matchedEgg['nest_id'],
                    'name' => $matchedEgg['name'],
                ]);

                $localEgg = $localEgg->fresh();
                $nestId = $matchedEgg['nest_id'];
            }

            $response = $this->client()->get("/api/application/nests/{$nestId}/eggs/{$localEgg->egg_id}", [
                'include' => 'variables',
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException("Pterodactyl egg {$localEgg->egg_id} HTTP {$response->status()}: {$response->body()}");
            }

            $attributes = data_get($response->json(), 'attributes', []);
            $dockerImages = data_get($attributes, 'docker_images', []);

            $localEgg->update([
                'nest_id' => $nestId,
                'name' => $attributes['name'] ?? $localEgg->name,
                'startup' => $attributes['startup'] ?? $localEgg->startup,
                'docker_image' => $this->firstDockerImage($dockerImages) ?: $localEgg->docker_image,
                'docker_images' => $dockerImages ?: null,
            ]);

            foreach (data_get($attributes, 'relationships.variables.data', []) as $variable) {
                $variableAttributes = data_get($variable, 'attributes', []);
                $envVariable = $variableAttributes['env_variable'] ?? null;

                if (! $envVariable) {
                    continue;
                }

                PterodactylEggVariable::updateOrCreate(
                    [
                        'pterodactyl_egg_id' => $localEgg->id,
                        'env_variable' => $envVariable,
                    ],
                    [
                        'pterodactyl_variable_id' => $variableAttributes['id'] ?? null,
                        'name' => $variableAttributes['name'] ?? null,
                        'default_value' => $variableAttributes['default_value'] ?? null,
                        'rules' => $variableAttributes['rules'] ?? null,
                        'required' => $this->rulesRequireValue((string) ($variableAttributes['rules'] ?? '')),
                        'user_viewable' => (bool) ($variableAttributes['user_viewable'] ?? false),
                        'user_editable' => (bool) ($variableAttributes['user_editable'] ?? false),
                    ]
                );
            }

            $localEgg->load('variables');

            $synced[] = [
                'egg_id' => $localEgg->egg_id,
                'name' => $localEgg->name,
                'variables' => $localEgg->variables->count(),
                'required_variables' => $localEgg->variables->where('required', true)->count(),
            ];
        }

        return $synced;
    }

    private function findOrCreateUser(User $user): array
    {
        $externalId = 'vortex-user-' . $user->id;

        $response = $this->client()->get("/api/application/users/external/{$externalId}");

        if ($response->successful()) {
            return data_get($response->json(), 'attributes', []);
        }

        if ($response->status() !== 404) {
            throw new \RuntimeException(
                "Pterodactyl user lookup HTTP {$response->status()}: {$response->body()}"
            );
        }

        $search = $this->client()->get('/api/application/users', [
            'filter[email]' => $user->email,
        ]);

        if ($search->successful()) {
            $data = data_get($search->json(), 'data', []);

            if (! empty($data)) {
                $existing = $data[0]['attributes'];

                Log::info('Usuário já existe no Pterodactyl pelo email', [
                    'email' => $user->email,
                    'ptero_user_id' => $existing['id'],
                ]);

                return $existing;
            }
        }

        $names = Str::of($user->nome)->trim()->explode(' ')->filter()->values();

        $create = $this->client()->post('/api/application/users', [
            'external_id' => $externalId,
            'email' => $user->email,
            'username' => $this->username($user),
            'first_name' => $names->first() ?: 'Cliente',
            'last_name' => $names->count() > 1 ? $names->slice(1)->join(' ') : 'VortexHost',
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

    private function serverPayload(
        CustomerService $service,
        Plan $plan,
        PterodactylEgg $egg,
        int $pterodactylUserId,
        array $environmentOverride = []
    ): array {
        $provisionIdentifier = $this->ensureProvisionIdentifier($service);
        $externalId = $this->externalId($service);
        $environment = $this->environmentFromEgg($egg);
        $environment = array_replace($environment, $this->configuredEnvironment($service), $environmentOverride);
        $environment = $this->removeEmptyEnvironmentValues($environment);

        $missing = $this->missingRequiredVariables($egg, $environment);

        if ($missing) {
            Log::warning('Provisionamento aguardando variáveis obrigatórias da egg', [
                'service_id' => $service->id,
                'plan_id' => $plan->plan,
                'egg_id' => $egg->egg_id,
                'missing_variables' => $missing,
            ]);

            throw new MissingPterodactylEggVariablesException($missing);
        }

        $nodeId = config('services.pterodactyl.node_id') ?: $this->nodeId($plan);
        $locationId = config('services.pterodactyl.location_id') ?: $plan->location_id;
        $allocationId = config('services.pterodactyl.allocation_id');

        $payload = [
            'name' => $this->serverName($service, $provisionIdentifier),
            'external_id' => $externalId,
            'user' => $pterodactylUserId,
            'egg' => $egg->egg_id,
            'docker_image' => $this->dockerImage($egg),
            'startup' => $egg->startup,
            'environment' => $environment,
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
            'start_on_completion' => true,
        ];

        if ($nodeId) {
            $payload['node'] = (int) $nodeId;
        }

        if ($allocationId) {
            $payload['allocation'] = ['default' => (int) $allocationId];
        } elseif ($nodeId) {
            $payload['allocation'] = ['default' => $this->firstFreeAllocationId((int) $nodeId)];
        } else {
            $payload['deploy'] = [
                'locations' => [(int) $locationId],
                'dedicated_ip' => false,
                'port_range' => [],
            ];
        }

        return $payload;
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

        throw new \RuntimeException("Pterodactyl server lookup HTTP {$response->status()}: {$response->body()}");
    }

    private function remoteServerExists(PterodactylServer $server): bool
    {
        try {
            if ($server->pterodactyl_server_id) {
                $response = $this->client()->get("/api/application/servers/{$server->pterodactyl_server_id}");

                if ($response->successful()) {
                    return true;
                }

                if ($response->status() === 404) {
                    return false;
                }

                Log::warning('Falha ao consultar servidor Pterodactyl por ID; mantendo registro local', [
                    'pterodactyl_server_id' => $server->pterodactyl_server_id,
                    'status' => $response->status(),
                    'body' => $response->json() ?: $response->body(),
                ]);

                return true;
            }

            if ($server->external_id) {
                return (bool) $this->findServerByExternalId($server->external_id);
            }
        } catch (\Throwable $e) {
            Log::warning('Erro ao consultar servidor Pterodactyl; mantendo registro local', [
                'pterodactyl_server_id' => $server->pterodactyl_server_id,
                'external_id' => $server->external_id,
                'error' => $e->getMessage(),
            ]);
        }

        return true;
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
                'external_id' => $payload['external_id'] ?? null,
                'provision_identifier' => $service->provision_identifier,
                'uuid' => $server['uuid'] ?? null,
                'identifier' => $server['identifier'] ?? null,
                'allocation_id' => data_get($payload, 'allocation.default'),
                'node_id' => $payload['node'] ?? null,
                'egg_id' => $eggId,
                'raw_response' => $rawResponse,
            ]
        );

        $this->configuration($service)->update(['last_error' => null]);

        $service->update([
            'status' => 'active',
            'domain' => $server['identifier'] ?? null,
        ]);

        return $record;
    }

    private function ensureProvisionIdentifier(CustomerService $service): string
    {
        if ($service->provision_identifier) {
            return $service->provision_identifier;
        }

        $identifier = sprintf(
            'VTX-SRV-%06d-U%06d-O%06d-I%06d',
            $service->id,
            $service->user_id,
            $service->order_id ?: 0,
            $service->order_item_id ?: 0
        );

        $service->forceFill(['provision_identifier' => $identifier])->save();
        $service->provision_identifier = $identifier;

        return $identifier;
    }

    private function externalId(CustomerService $service): string
    {
        return Str::of($this->ensureProvisionIdentifier($service))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();
    }

    private function serverName(CustomerService $service, string $provisionIdentifier): string
    {
        $name = $service->name ?: "{$service->product->nome} #{$service->id}";

        return Str::limit("{$name} ({$provisionIdentifier})", 191, '');
    }

    private function configuration(CustomerService $service): ServiceProvisioningConfiguration
    {
        return ServiceProvisioningConfiguration::firstOrCreate([
            'service_id' => $service->id,
        ]);
    }

    private function configuredEnvironment(CustomerService $service): array
    {
        return $service->provisioningConfiguration?->environment ?? [];
    }

    private function environmentFromEgg(PterodactylEgg $egg): array
    {
        return $egg->variables
            ->mapWithKeys(fn (PterodactylEggVariable $variable) => [
                $variable->env_variable => $variable->default_value,
            ])
            ->all();
    }

    private function removeEmptyEnvironmentValues(array $environment): array
    {
        return collect($environment)
            ->reject(fn ($value) => $value === null || $value === '')
            ->map(fn ($value) => is_bool($value) ? ($value ? '1' : '0') : (string) $value)
            ->all();
    }

    private function missingRequiredVariables(PterodactylEgg $egg, array $environment): array
    {
        return $egg->variables
            ->filter(fn (PterodactylEggVariable $variable) => $variable->required)
            ->filter(fn (PterodactylEggVariable $variable) => blank($environment[$variable->env_variable] ?? null))
            ->pluck('env_variable')
            ->values()
            ->all();
    }

    private function dockerImage(PterodactylEgg $egg): ?string
    {
        return $egg->docker_image ?: $this->firstDockerImage($egg->docker_images ?? []);
    }

    private function firstDockerImage(mixed $dockerImages): ?string
    {
        if (is_string($dockerImages)) {
            $decoded = json_decode($dockerImages, true);
            $dockerImages = is_array($decoded) ? $decoded : [$dockerImages];
        }

        if (! is_array($dockerImages) || empty($dockerImages)) {
            return null;
        }

        $first = reset($dockerImages);

        return is_array($first) ? (string) reset($first) : (string) $first;
    }

    private function firstFreeAllocationId(int $nodeId): int
    {
        $page = 1;

        do {
            $response = $this->client()->get("/api/application/nodes/{$nodeId}/allocations", [
                'page' => $page,
                'per_page' => 100,
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException("Pterodactyl allocations HTTP {$response->status()}: {$response->body()}");
            }

            foreach ($response->json('data', []) as $allocation) {
                if (! (bool) data_get($allocation, 'attributes.assigned')) {
                    return (int) data_get($allocation, 'attributes.id');
                }
            }

            $currentPage = (int) data_get($response->json(), 'meta.pagination.current_page', $page);
            $totalPages = (int) data_get($response->json(), 'meta.pagination.total_pages', $page);
            $page++;
        } while ($currentPage < $totalPages);

        throw new \RuntimeException("Node {$nodeId} não possui allocation livre para criar servidor.");
    }

    private function nodeCapacityIssue(array $payload, Plan $plan): ?string
    {
        $nodeId = $payload['node'] ?? null;

        if (! $nodeId) {
            return null;
        }

        $response = $this->client()->get("/api/application/nodes/{$nodeId}");

        if (! $response->successful()) {
            return "Não foi possível consultar capacidade do node {$nodeId}: HTTP {$response->status()}";
        }

        $node = $response->json('attributes', []);
        $memoryLimit = $this->resourceLimit(
            (int) data_get($node, 'memory', 0),
            (int) data_get($node, 'memory_overallocate', 0)
        );
        $diskLimit = $this->resourceLimit(
            (int) data_get($node, 'disk', 0),
            (int) data_get($node, 'disk_overallocate', 0)
        );
        $allocatedMemory = (int) data_get($node, 'allocated_resources.memory', 0);
        $allocatedDisk = (int) data_get($node, 'allocated_resources.disk', 0);
        $requestedMemory = (int) data_get($payload, 'limits.memory', 0);
        $requestedDisk = (int) data_get($payload, 'limits.disk', 0);

        if ($memoryLimit !== null && $allocatedMemory + $requestedMemory > $memoryLimit) {
            return "Node {$nodeId} sem memória disponível: alocado {$allocatedMemory} MB, solicitado {$requestedMemory} MB, limite {$memoryLimit} MB.";
        }

        if ($diskLimit !== null && $allocatedDisk + $requestedDisk > $diskLimit) {
            return "Node {$nodeId} sem disco disponível: alocado {$allocatedDisk} MB, solicitado {$requestedDisk} MB, limite {$diskLimit} MB.";
        }

        return null;
    }

    private function resourceLimit(int $base, int $overallocate): ?int
    {
        if ($overallocate < 0) {
            return null;
        }

        return (int) floor($base * (1 + ($overallocate / 100)));
    }

    private function nestIdForEgg(int $eggId): ?int
    {
        $response = $this->client()->get('/api/application/nests', [
            'include' => 'eggs',
            'per_page' => 100,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Pterodactyl nests HTTP {$response->status()}: {$response->body()}");
        }

        foreach ($response->json('data', []) as $nest) {
            $nestId = (int) data_get($nest, 'attributes.id');

            foreach (data_get($nest, 'attributes.relationships.eggs.data', []) as $egg) {
                if ((int) data_get($egg, 'attributes.id') === $eggId) {
                    return $nestId;
                }
            }
        }

        return null;
    }

    private function findEggByName(string $name): ?array
    {
        $needle = $this->normalizedName($name);
        $response = $this->client()->get('/api/application/nests', [
            'include' => 'eggs',
            'per_page' => 100,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Pterodactyl nests HTTP {$response->status()}: {$response->body()}");
        }

        $fallback = null;

        foreach ($response->json('data', []) as $nest) {
            $nestId = (int) data_get($nest, 'attributes.id');

            foreach (data_get($nest, 'attributes.relationships.eggs.data', []) as $egg) {
                $eggName = (string) data_get($egg, 'attributes.name');
                $normalizedEggName = $this->normalizedName($eggName);
                $match = [
                    'egg_id' => (int) data_get($egg, 'attributes.id'),
                    'nest_id' => $nestId,
                    'name' => $eggName,
                ];

                if ($normalizedEggName === $needle) {
                    return $match;
                }

                if (! $fallback && (str_contains($normalizedEggName, $needle) || str_contains($needle, $normalizedEggName))) {
                    $fallback = $match;
                }
            }
        }

        return $fallback;
    }

    private function normalizedName(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function rulesRequireValue(string $rules): bool
    {
        return collect(explode('|', $rules))
            ->map(fn (string $rule) => Str::before(trim($rule), ':'))
            ->contains('required');
    }

    private function missingVariablesFromResponse(mixed $body): array
    {
        $text = is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_UNICODE);

        if (! $text) {
            return [];
        }

        preg_match_all('/(?:environment\.|variable\s+)([A-Z0-9_]+)/i', $text, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $variable) => strtoupper($variable))
            ->unique()
            ->values()
            ->all();
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
