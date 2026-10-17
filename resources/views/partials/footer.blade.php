<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">

            <div class="footer-brand">
                <div class="brand">
                    <img src="{{ asset('img/vortexhost-logo-full.png') }}" alt="VortexHost" id="logo_img">
                </div>

                <p>Hospedagem e infraestrutura digital com informações comerciais claras e gestão pela área do cliente.</p>
                {{-- [SUBSTITUIR POR DADO REAL] Adicionar razão social e CNPJ após validação jurídica. --}}

                <a href="mailto:contato@corelabs.dev.br">
                    contato@corelabs.dev.br
                </a>
            </div>

            <div class="footer-col">
                <h4>Serviços</h4>
                <ul class="footer-links">
                    <li><a href="{{ url('/vps') }}">Servidores VPS</a></li>
                    <li><a href="{{ route('vps.linux') }}">VPS Linux</a></li>
                    <li><a href="{{ route('vps.windows') }}">VPS Windows</a></li>
                    <li><a href="{{ url('/web') }}">Hospedagem Web</a></li>
                    <li><a href="{{ route('content.index') }}">Conteúdos técnicos</a></li>
                    <li><a href="{{ route('pages.show', 'revenda') }}">Revenda</a></li>
                    <li><a href="{{ route('pages.show', 'dominios') }}">Domínios</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Suporte</h4>
                <ul class="footer-links">
                    <li><a href="{{ auth()->check() ? route('dashboard.index') : route('login') }}">Painel</a></li>
                    <li><a href="{{ url('/login') }}">Login</a></li>
                    <li><a href="{{ url('/registro') }}">Criar Conta</a></li>
                    <li><a href="{{ route('pages.show', 'faq') }}">FAQ</a></li>
                    <li><a href="{{ route('help.index') }}">Central de Ajuda</a></li>
                    <li><a href="{{ route('pages.show', 'contato') }}">Contato</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="{{ url('/termos') }}">Termos de Serviço</a></li>
                    <li><a href="{{ url('/politica-privacidade') }}">Política de Privacidade</a></li>
                    <li><a href="{{ url('/reembolso') }}">Política de Reembolso</a></li>
                    <li><a href="{{ route('pages.show', 'sla') }}">SLA</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Empresa</h4>

                <div class="social-icons">
                    <a href="https://www.instagram.com/vortexhostbr/" target="_blank" rel="noopener noreferrer" class="social-icon" title="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="5"></rect>
                            <circle cx="12" cy="12" r="4"></circle>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>

                </div>
                <ul class="footer-links"><li><a href="{{ route('pages.show', 'sobre') }}">Sobre a Vortex</a></li><li><a href="{{ route('pages.show', 'infraestrutura') }}">Infraestrutura</a></li></ul>
            </div>

        </div>

        <div class="footer-bottom">
            <span class="footer-copy">
                © {{ date('Y') }} VortexHost. Todos os direitos reservados.
            </span>

            <div class="footer-payments">
                <span class="payment-badge">PIX</span>
                <span class="payment-badge">Cartão</span>
            </div>
        </div>
    </div>
</footer>
