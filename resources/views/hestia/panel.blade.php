@extends('layouts.app')

@section('content')
<section class="dashboard-section">
    <div class="container">
        <div class="hosting-header">
            <h1 class="section-title">Painel do site</h1>
            <p class="section-sub">Use estes dados para acessar sua conta Hestia.</p>
        </div>

        <div class="plans-grid">
            <article class="plan-card">
                <h3 class="plan-name">Credenciais Hestia</h3>

                <ul class="plan-features">
                    <li><strong>Usuário</strong> {{ $user }}</li>
                    <li><strong>Senha</strong> {{ $password }}</li>
                </ul>

                <div class="plan-footer">
                    <a class="btn btn-primary" href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                        Abrir Hestia
                    </a>
                </div>
            </article>
        </div>
    </div>
</section>
@endsection
