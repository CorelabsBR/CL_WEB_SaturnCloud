<?php

namespace App\Http\Controllers;

use App\Exceptions\MissingPterodactylEggVariablesException;
use App\Models\CustomerService;
use App\Services\PterodactylService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PterodactylServerConfigurationController extends Controller
{
    public function edit(CustomerService $service, PterodactylService $pterodactyl)
    {
        $this->authorizeService($service);

        $service->loadMissing([
            'product.planModel.pterodactylEgg.variables',
            'provisioningConfiguration',
        ]);

        $variables = $pterodactyl->requiredVariablesWithoutDefault($service);

        return view('pterodactyl.configure', [
            'service' => $service,
            'egg' => $service->product?->planModel?->pterodactylEgg,
            'variables' => $variables,
            'environment' => $service->provisioningConfiguration?->environment ?? [],
        ]);
    }

    public function update(Request $request, CustomerService $service, PterodactylService $pterodactyl)
    {
        $this->authorizeService($service);

        $service->loadMissing([
            'order',
            'orderItem',
            'product.planModel.pterodactylEgg.variables',
            'provisioningConfiguration',
        ]);

        $egg = $service->product?->planModel?->pterodactylEgg;

        if (! $egg) {
            throw ValidationException::withMessages([
                'environment' => 'Plano/egg do serviço não encontrado.',
            ]);
        }

        $egg = $pterodactyl->ensureEggMetadata($egg);

        $allowedVariables = $egg->variables->pluck('env_variable')->all();
        $environment = collect($request->input('environment', []))
            ->only($allowedVariables)
            ->all();

        $pterodactyl->saveEnvironment($service, $environment);
        $service->refresh();

        $missing = $pterodactyl->requiredVariablesWithoutDefault($service);

        if ($missing) {
            throw ValidationException::withMessages(
                collect($missing)
                    ->mapWithKeys(fn ($variable) => ["environment.{$variable->env_variable}" => 'Informe este valor para criar o servidor.'])
                    ->all()
            );
        }

        if ($service->order?->status !== 'pago') {
            return redirect()
                ->route('dashboard.index')
                ->with('success', 'Configuração salva. O servidor será criado quando o pagamento for aprovado.');
        }

        try {
            $service->update(['status' => 'provisioning']);
            $pterodactyl->provision($service, $service->orderItem);
        } catch (MissingPterodactylEggVariablesException $e) {
            return redirect()
                ->route('pterodactyl.configure.edit', $service)
                ->with('error', 'Ainda faltam variáveis obrigatórias: ' . implode(', ', $e->variables()));
        } catch (\Throwable $e) {
            $service->update(['status' => 'failed']);

            Log::error('Falha ao provisionar após configuração do servidor', [
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Configuração salva, mas o Pterodactyl recusou a criação. Verifique os logs.');
        }

        return redirect()
            ->route('dashboard.index')
            ->with('success', 'Servidor criado no Pterodactyl com sucesso.');
    }

    private function authorizeService(CustomerService $service): void
    {
        abort_if((int) $service->user_id !== (int) Auth::id(), 403);
        abort_if($service->type !== 'game', 404);
    }
}
