<?php

namespace App\Services;

use App\Models\CustomerService;
use App\Models\HestiaAccount;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HestiaService
{
    private const RETURN_CODES = [
        0 => 'OK',
        1 => 'Argumentos insuficientes',
        2 => 'Objeto ou argumento invalido',
        3 => 'Objeto nao existe',
        4 => 'Objeto ja existe',
        5 => 'Objeto suspenso',
        6 => 'Objeto ja esta ativo',
        7 => 'Objeto em uso',
        8 => 'Limite do pacote atingido',
        9 => 'Senha invalida',
        10 => 'Acesso negado para este usuario/chave',
        11 => 'Subsistema desativado',
        12 => 'Configuracao quebrada',
        13 => 'Sem espaco em disco',
        14 => 'Servidor ocupado',
        15 => 'Falha de conexao',
        16 => 'FTP sem resposta',
        17 => 'Banco de dados sem resposta',
        18 => 'Falha no RRDtool',
        19 => 'Falha ao atualizar',
        20 => 'Falha ao reiniciar servico',
    ];

    public function deleteUser(CustomerService $service): bool
    {
        $service->loadMissing('hestiaAccount');
        $account = $service->hestiaAccount;

        if (! $account?->hestia_user) {
            return false;
        }

        $this->command('v-delete-user', [
            $account->hestia_user,
        ], [0, 3]);

        return true;
    }

    public function provision(CustomerService $service, OrderItem $item): HestiaAccount
    {
        $service->loadMissing(['product', 'order.user', 'orderItem']);

        $user = $service->order->user;
        $hestiaUser = $this->username($user, $service);
        $password = $service->hestiaAccount?->password ?: 'vtx' . Str::password(18);
        $package = $this->packageFor($service);
        $domain = $this->domainFor($service);

        if ($this->shouldQueueLocalProvisioning()) {
            return $this->queueProvisioning($service, $hestiaUser, $password, $package, $domain, $user);
        }

        $createUser = $this->command('v-add-user', [
            $hestiaUser,
            $password,
            $user->email,
            $package,
            $this->firstName($user),
            $this->lastName($user),
        ], [0, 4]);

        $domainResponse = null;

        if ($domain && config('services.hestia.create_domain', false)) {
            $domainResponse = $this->command('v-add-web-domain', [
                $hestiaUser,
                $domain,
            ], [0, 4]);
        }

        $account = HestiaAccount::updateOrCreate(
            ['service_id' => $service->id],
            $this->accountAttributes(
                $hestiaUser,
                $password,
                $domain,
                [
                    'user' => $createUser,
                    'domain' => $domainResponse,
                ]
            )
        );

        $service->update([
            'status' => 'active',
            'domain' => $domain,
        ]);

        return $account;
    }

    public function processQueue(int $limit = 50, ?int $orderId = null): array
    {
        $processed = [];
        $failed = [];
        $skipped = [];

        foreach (array_slice($this->queuedFiles($orderId), 0, $limit) as $file) {
            $payload = json_decode((string) file_get_contents($file), true);

            if (! is_array($payload)) {
                File::delete($file);
                continue;
            }

            try {
                $service = CustomerService::with('product')->find($payload['service_id']);

                if (! $service) {
                    File::delete($file);

                    $skipped[] = [
                        'service_id' => $payload['service_id'] ?? null,
                        'reason' => 'Serviço não existe mais no banco.',
                    ];

                    Log::warning('Fila Hestia órfã descartada', [
                        'service_id' => $payload['service_id'] ?? null,
                        'order_id' => $payload['order_id'] ?? null,
                        'order_item_id' => $payload['order_item_id'] ?? null,
                        'hestia_user' => $payload['hestia_user'] ?? null,
                    ]);

                    continue;
                }

                $package = $this->packageFor($service);

                $result = $this->command('v-add-user', [
                    $payload['hestia_user'],
                    $payload['password'],
                    $payload['email'],
                    $package,
                    $payload['first_name'],
                    $payload['last_name'],
                ], [0, 4]);

                if (! in_array($result['code'], [0, 4], true)) {
                    throw new \RuntimeException($this->errorMessage(
                        'v-add-user',
                        $result,
                        (int) ($result['http_status'] ?? 0)
                    ));
                }

                if ($service) {
                    $service->update([
                        'status' => 'active',
                        'domain' => $payload['domain'] ?? null,
                    ]);

                    HestiaAccount::updateOrCreate(
                        ['service_id' => $service->id],
                        $this->accountAttributes(
                            $payload['hestia_user'],
                            $payload['password'],
                            $payload['domain'] ?? null,
                            [
                                'user' => $result,
                                'queued' => true,
                                'processed_at' => now()->toIso8601String(),
                            ]
                        )
                    );
                }

                File::delete($file);
                $processed[] = $payload['service_id'];
            } catch (\Throwable $e) {
                $payload['attempts'] = (int) ($payload['attempts'] ?? 0) + 1;
                $payload['last_error'] = $e->getMessage();
                $payload['last_attempt_at'] = now()->toIso8601String();
                file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $failed[] = [
                    'service_id' => $payload['service_id'] ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'skipped' => $skipped,
        ];
    }

    private function queueProvisioning(
        CustomerService $service,
        string $hestiaUser,
        string $password,
        string $package,
        ?string $domain,
        User $user
    ): HestiaAccount {
        $payload = [
            'service_id' => $service->id,
            'order_id' => $service->order_id,
            'order_item_id' => $service->order_item_id,
            'hestia_user' => $hestiaUser,
            'password' => $password,
            'email' => $user->email,
            'package' => $package,
            'first_name' => $this->firstName($user),
            'last_name' => $this->lastName($user),
            'domain' => $domain,
            'attempts' => 0,
            'queued_at' => now()->toIso8601String(),
        ];

        $path = $this->queuePath();
        File::ensureDirectoryExists($path);
        file_put_contents($this->queueFile($service->id), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return HestiaAccount::updateOrCreate(
            ['service_id' => $service->id],
            $this->accountAttributes(
                $hestiaUser,
                $password,
                $domain,
                [
                    'queued' => true,
                    'queued_at' => $payload['queued_at'],
                    'message' => 'Criação Hestia aguardando processamento via CLI.',
                ]
            )
        );
    }

    private function accountAttributes(string $hestiaUser, string $password, ?string $domain, array $rawResponse): array
    {
        $attributes = [
            'hestia_user' => $hestiaUser,
            'domain' => $domain,
            'raw_response' => $rawResponse,
        ];

        if (Schema::hasColumn('hestia_accounts', 'password')) {
            $attributes['password'] = $password;
        } else {
            $attributes['raw_response']['password'] = $password;
        }

        return $attributes;
    }

    private function shouldQueueLocalProvisioning(): bool
    {
        return config('services.hestia.driver') === 'local'
            && config('services.hestia.queue_when_exec_disabled', true)
            && ! \function_exists('exec');
    }

    private function queuedFiles(?int $orderId = null): array
    {
        $path = $this->queuePath();

        if (! is_dir($path)) {
            return [];
        }

        $files = glob($path . '/*.json') ?: [];
        sort($files);

        if ($orderId) {
            $files = array_values(array_filter($files, function (string $file) use ($orderId) {
                $payload = json_decode((string) file_get_contents($file), true);

                return is_array($payload) && (int) ($payload['order_id'] ?? 0) === $orderId;
            }));
        }

        return $files;
    }

    private function queueFile(int $serviceId): string
    {
        return $this->queuePath() . '/service-' . $serviceId . '.json';
    }

    private function queuePath(): string
    {
        return (string) config('services.hestia.queue_path', storage_path('app/hestia-queue'));
    }

    private function command(string $command, array $arguments, array $allowedCodes = [0]): array
    {
        if (config('services.hestia.driver') === 'local') {
            $result = $this->localCommand($command, $arguments);

            if (! in_array($result['code'], $allowedCodes, true)) {
                throw new \RuntimeException($this->localErrorMessage($command, $result));
            }

            return $result;
        }

        $payload = $this->payload($command, $arguments);
        $response = $this->send($payload);
        $result = $this->parseResponse($command, $response->status(), $response->body());

        if (
            (! $response->successful() || $result['code'] === 10)
            && isset($payload['hash'])
            && config('services.hestia.user')
            && config('services.hestia.password')
        ) {
            $payload = $this->payload($command, $arguments, true);
            $response = $this->send($payload);
            $result = $this->parseResponse($command, $response->status(), $response->body());
        }

        if (! $response->successful() && ! in_array($result['code'], $allowedCodes, true)) {
            throw new \RuntimeException($this->errorMessage($command, $result, $response->status()));
        }

        if (! in_array($result['code'], $allowedCodes, true)) {
            throw new \RuntimeException($this->errorMessage($command, $result, $response->status()));
        }

        return $result;
    }

    private function localCommand(string $command, array $arguments): array
    {
        $bin = rtrim((string) config('services.hestia.bin_path', '/usr/local/hestia/bin'), '/');
        $executable = "{$bin}/{$command}";

        if (! preg_match('/^v-[a-z0-9-]+$/', $command)) {
            throw new \RuntimeException("Comando Hestia local invalido: {$command}");
        }

        $commandLine = config('services.hestia.local_use_sudo')
            ? ['sudo', '-n', $executable]
            : [$executable];

        $shellCommand = collect(array_merge($commandLine, array_map('strval', $arguments)))
            ->map(fn (string $part) => \escapeshellarg($part))
            ->implode(' ');

        $output = [];
        $code = 1;
        if (! \function_exists('exec')) {
            throw new \RuntimeException('A funcao exec esta desabilitada no PHP-FPM. Remova exec de disable_functions em /etc/php/8.3/fpm/php.ini e reinicie php8.3-fpm para criar usuarios Hestia localmente.');
        }

        \exec($shellCommand . ' 2>&1', $output, $code);
        $body = trim(implode("\n", $output));
        $error = '';

        if ($code === 0 && str_contains($body, 'Permission denied')) {
            $code = 10;
        }

        return [
            'code' => $code,
            'body' => $body,
            'error' => $error,
            'message' => self::RETURN_CODES[$code] ?? 'Erro desconhecido',
            'command' => $command,
            'driver' => 'local',
        ];
    }

    private function localErrorMessage(string $command, array $result): string
    {
        $details = $result['error'] ?: $result['body'];
        $message = "Hestia local {$command} retornou codigo {$result['code']} ({$result['message']}).";

        if ($details !== '') {
            $message .= " Detalhes: {$details}.";
        }

        if (str_contains($details, 'Permission denied') || str_contains($details, 'sudo')) {
            $message .= " Como o Hestia esta na mesma VPS, habilite sudo sem senha para o usuario do PHP executar /usr/local/hestia/bin/v-* ou rode o PHP com um usuario que tenha acesso ao Hestia.";
        }

        return $message;
    }

    private function payload(string $command, array $arguments, bool $forcePasswordAuth = false): array{
        $accessKey = config('services.hestia.access_key');
        $secretKey = config('services.hestia.secret_key');
        $hash = $accessKey && $secretKey ? "{$accessKey}:{$secretKey}" : null;

        $user = config('services.hestia.user');
        $password = config('services.hestia.password');

        if (! config('services.hestia.url')) {
            throw new \RuntimeException('URL do Hestia nao configurada.');
        }

        if (! $forcePasswordAuth && $hash) {
            $payload = [
                'hash' => $hash,
            ];
        } elseif ($user && $password) {
            $payload = [
                'user' => $user,
                'password' => $password,
            ];
        } else {
            throw new \RuntimeException('Credenciais Hestia nao configuradas.');
        }

        $payload += [
            'cmd' => $command,
            'returncode' => 'yes',
        ];

        foreach (array_values($arguments) as $index => $argument) {
            $payload['arg' . ($index + 1)] = $argument;
        }

        return $payload;
    }

    private function send(array $payload)
    {
        return Http::asForm()
            ->withOptions(['verify' => (bool) config('services.hestia.verify_ssl', true)])
            ->timeout(30)
            ->post(rtrim(config('services.hestia.url'), '/') . '/api/', $payload);
    }

    private function parseResponse(string $command, int $status, string $body): array
    {
        $body = trim($body);
        $code = is_numeric($body) ? (int) $body : 0;

        return [
            'code' => $code,
            'body' => $body,
            'message' => self::RETURN_CODES[$code] ?? 'Erro desconhecido',
            'command' => $command,
            'http_status' => $status,
        ];
    }

    private function errorMessage(string $command, array $result, int $status): string
    {
        $message = "Hestia {$command} retornou codigo {$result['code']} ({$result['message']}) via HTTP {$status}.";

        if ((int) $result['code'] === 10) {
            $message .= " A chave precisa ser do usuario admin com permissao para este comando. No servidor Hestia, gere uma chave com: v-add-access-key admin '*' vortexhost json. Para permissao minima, crie um perfil de API com ROLE='admin' e COMMANDS='v-add-user'.";
        }

        return $message;
    }

    private function username(User $user, CustomerService $service): string
    {
        $requestedUsername = trim((string) $service->orderItem?->requested_username);

        if ($requestedUsername !== '') {
            return Str::lower($requestedUsername);
        }

        $base = Str::lower(Str::before($user->email, '@'));
        $base = preg_replace('/[^a-z0-9]/', '', $base) ?: 'cliente';

        return substr($base, 0, 8) . $service->id;
    }

    private function domainFor(CustomerService $service): ?string
    {
        $baseDomain = config('services.hestia.default_domain');

        if (! $baseDomain) {
            return null;
        }

        return 'srv' . $service->id . '.' . ltrim($baseDomain, '.');
    }

    private function packageFor(CustomerService $service): string
    {
        $product = $service->product;
        $map = $this->packageMap();
        $productName = (string) $product?->nome;
        $productSlug = Str::slug($productName);
        $shortSlug = Str::slug(Str::replaceStart('tier-', '', $productSlug));

        foreach ([
            (string) $product?->id,
            (string) $product?->plan,
            $productSlug,
            $shortSlug,
            $productName,
        ] as $key) {
            if ($key !== '' && isset($map[$key])) {
                return (string) $map[$key];
            }
        }

        if (config('services.hestia.package_from_product', true) && $shortSlug !== '') {
            return $shortSlug;
        }

        return (string) config('services.hestia.package', 'default');
    }

    private function packageMap(): array
    {
        $defaultMap = [
            'tier-amador' => 'amateur',
            'amador' => 'amateur',
            'amateur' => 'amateur',
            'Tier Amador' => 'amateur',
            'tier-profissional' => 'profissional',
            'profissional' => 'profissional',
            'Tier Profissional' => 'profissional',
            'tier-elite' => 'elite',
            'elite' => 'elite',
            'Tier Elite' => 'elite',
        ];

        $json = config('services.hestia.package_map');
        $map = $json ? json_decode($json, true) : [];

        return array_merge($defaultMap, is_array($map) ? $map : []);
    }

    private function firstName(User $user): string
    {
        return Str::of($user->nome)->trim()->explode(' ')->first() ?: 'Cliente';
    }

    private function lastName(User $user): string
    {
        $parts = Str::of($user->nome)->trim()->explode(' ')->filter()->values();

        return $parts->count() > 1 ? $parts->slice(1)->join(' ') : 'VortexHost';
    }
}
