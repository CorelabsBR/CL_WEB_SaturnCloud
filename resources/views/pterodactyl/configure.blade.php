@extends('layouts.app')

@section('title', 'Configurar servidor')

@section('content')
<main class="client-page">
    <section class="client-content">
        <div class="container">
            @if (session('error'))
                <div class="flash flash-error" role="alert">{{ session('error') }}</div>
            @endif

            <div class="dashboard-panel">
                <div class="panel-head">
                    <div>
                        <h2>Configurar {{ $service->name }}</h2>
                        <p>{{ $egg?->name ?? 'Egg Pterodactyl' }}</p>
                    </div>
                    <a href="{{ route('dashboard.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                </div>

                @if (empty($variables))
                    <div class="empty-state">
                        <h3>Nenhuma variável obrigatória pendente</h3>
                        <p>Este serviço já tem os valores necessários para provisionamento.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('pterodactyl.configure.update', $service) }}" class="service-list">
                        @csrf
                        @method('PATCH')

                        @foreach ($variables as $variable)
                            <label class="profile-field" for="env_{{ $variable->env_variable }}">
                                <span>{{ $variable->name ?: $variable->env_variable }}</span>
                                <input
                                    id="env_{{ $variable->env_variable }}"
                                    name="environment[{{ $variable->env_variable }}]"
                                    type="text"
                                    value="{{ old('environment.' . $variable->env_variable, data_get($environment, $variable->env_variable)) }}"
                                    autocomplete="off"
                                >
                                @error('environment.' . $variable->env_variable)
                                    <small>{{ $message }}</small>
                                @enderror
                            </label>
                        @endforeach

                        <button type="submit" class="btn btn-primary">Salvar e provisionar</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection
