@extends('layouts.app')

@section('content')
<section class="dashboard-section">
    <div class="container">
        <div class="hosting-header">
            <h1 class="section-title">Painel do jogo</h1>
            <p class="section-sub">Use estes dados para acessar sua conta Pterodactyl.</p>
        </div>

        <div class="plans-grid">
            <article class="plan-card">
                <h3 class="plan-name">Credenciais Pterodactyl</h3>

                <ul class="plan-features">
                    <li><strong>Username</strong> {{ $username }}</li>
                    <li><strong>Senha</strong> {{ $password ?? 'Manutencao isso ira funcionar daqui uns dias' }}</li>
                </ul>

                <div class="plan-footer">
                    <a class="btn btn-primary" href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                        Abrir Pterodactyl
                    </a>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection