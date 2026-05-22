<?php

namespace App\Services;

use App\Models\CustomerService;
use App\Models\HestiaAccount;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class HestiaService
{
    public function provision(CustomerService $service, OrderItem $item): HestiaAccount
    {
        $service->loadMissing(['product', 'order.user']);

        $user = $service->order->user;
        $hestiaUser = $this->username($user, $service);
        $password = Str::password(18);
        $package = $this->packageFor($service);
        $domain = $this->domainFor($service);

        $createUser = $this->command('v-add-user', [
            $hestiaUser,
            $password,
            $user->email,
            $package,
            $this->firstName($user),
            $this->lastName($user),
        ], [0, 4]);

        $domainResponse = null;

        if ($domain) {
            $domainResponse = $this->command('v-add-web-domain', [
                $hestiaUser,
                $domain,
            ], [0, 4]);
        }

        $account = HestiaAccount::updateOrCreate(
            ['service_id' => $service->id],
            [
                'hestia_user' => $hestiaUser,
                'domain' => $domain,
                'raw_response' => [
                    'user' => $createUser,
                    'domain' => $domainResponse,
                ],
            ]
        );

        $service->update([
            'status' => 'active',
            'domain' => $domain,
        ]);

        return $account;
    }

    private function command(string $command, array $arguments, array $allowedCodes = [0]): array
    {
        $accessKey = config('services.hestia.access_key');
        $secretKey = config('services.hestia.secret_key');
        $hash = $accessKey && $secretKey ? "{$accessKey}:{$secretKey}" : null;

        if (! config('services.hestia.url') || ! $hash) {
            throw new \RuntimeException('Credenciais Hestia não configuradas.');
        }

        $payload = [
            'hash' => $hash,
            'cmd' => $command,
            'returncode' => 'yes',
        ];

        foreach (array_values($arguments) as $index => $argument) {
            $payload['arg' . ($index + 1)] = $argument;
        }

        $response = Http::asForm()
            ->withOptions(['verify' => (bool) config('services.hestia.verify_ssl', true)])
            ->timeout(30)
            ->post(rtrim(config('services.hestia.url'), '/') . '/api/', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException("Hestia HTTP {$response->status()}: {$response->body()}");
        }

        $body = trim($response->body());
        $code = is_numeric($body) ? (int) $body : 0;

        if (! in_array($code, $allowedCodes, true)) {
            throw new \RuntimeException("Hestia {$command} retornou código {$code}.");
        }

        return ['code' => $code, 'body' => $body];
    }

    private function username(User $user, CustomerService $service): string
    {
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

        foreach ([
            (string) $product?->id,
            (string) $product?->plan,
            Str::slug((string) $product?->nome),
            (string) $product?->nome,
        ] as $key) {
            if ($key !== '' && isset($map[$key])) {
                return (string) $map[$key];
            }
        }

        return (string) config('services.hestia.package', 'default');
    }

    private function packageMap(): array
    {
        $json = config('services.hestia.package_map');
        $map = $json ? json_decode($json, true) : [];

        return is_array($map) ? $map : [];
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
