@extends('layouts.termos')

@section('title', 'Política de Privacidade')

@section('content')
<main class="privacy-page">
    <section class="privacy-hero">
        <div class="container">
            <h1>Política de Privacidade</h1>

            <p>
                Esta Política explica como a Saturn Cloud coleta, utiliza, armazena e protege
                os dados dos clientes ao utilizar nossos serviços de hospedagem.
            </p>

            <p class="privacy-updated">
                Última atualização: {{ date('d/m/Y') }}
            </p>
        </div>
    </section>

    <section class="privacy-content">
        <div class="container privacy-grid">
            <aside class="privacy-sidebar">
                <a href="#introducao">Introdução</a>
                <a href="#dados-coletados">Dados coletados</a>
                <a href="#uso-dados">Uso dos dados</a>
                <a href="#pagamentos">Pagamentos</a>
                <a href="#logs">Logs e segurança</a>
                <a href="#cookies">Cookies</a>
                <a href="#compartilhamento">Compartilhamento</a>
                <a href="#armazenamento">Armazenamento</a>
                <a href="#direitos">Direitos do cliente</a>
                <a href="#menores">Menores de idade</a>
                <a href="#alteracoes">Alterações</a>
                <a href="#contato">Contato</a>
            </aside>

            <article class="privacy-card">
                <section id="introducao">
                    <h2>Introdução</h2>
                    <p>
                        A Saturn Cloud respeita a privacidade dos clientes e busca tratar os dados
                        pessoais de forma segura, transparente e limitada ao necessário para
                        prestação dos serviços.
                    </p>
                    <p>
                        Ao criar uma conta, contratar um plano, acessar nossos painéis ou utilizar
                        qualquer serviço da Saturn Cloud, o cliente concorda com esta Política de Privacidade.
                    </p>
                </section>

                <section id="dados-coletados">
                    <h2>1. Dados que coletamos</h2>
                    <p>Podemos coletar os seguintes dados:</p>

                    <ul>
                        <li>Nome completo ou nome informado no cadastro;</li>
                        <li>E-mail, telefone e dados de contato;</li>
                        <li>CPF, CNPJ ou outros dados necessários para validação, cobrança ou emissão fiscal;</li>
                        <li>Endereço IP, navegador, dispositivo, sistema operacional e registros de acesso;</li>
                        <li>Dados de pagamento, status de transações e informações antifraude;</li>
                        <li>Mensagens enviadas ao suporte e histórico de atendimento;</li>
                        <li>Informações técnicas dos serviços contratados, como servidores, domínios, planos e uso de recursos.</li>
                    </ul>
                </section>

                <section id="uso-dados">
                    <h2>2. Como usamos os dados</h2>
                    <p>Os dados são utilizados para:</p>

                    <ul>
                        <li>Criar e gerenciar contas de cliente;</li>
                        <li>Ativar, manter, suspender ou cancelar serviços contratados;</li>
                        <li>Processar pagamentos e confirmar transações;</li>
                        <li>Prestar suporte técnico e atendimento;</li>
                        <li>Detectar fraudes, abusos, invasões ou uso indevido da infraestrutura;</li>
                        <li>Enviar avisos importantes sobre serviços, vencimentos, incidentes e manutenção;</li>
                        <li>Cumprir obrigações legais, fiscais, regulatórias ou ordens de autoridade competente.</li>
                    </ul>
                </section>

                <section id="pagamentos">
                    <h2>3. Pagamentos</h2>
                    <p>
                        Pagamentos podem ser processados por plataformas terceiras. A Saturn Cloud
                        pode receber informações como status da transação, identificador do pagamento,
                        valor, data, método utilizado e dados necessários para confirmação antifraude.
                    </p>
                    <p>
                        Dados sensíveis de cartão, quando aplicável, são tratados diretamente pelos
                        intermediadores de pagamento. A Saturn Cloud não armazena número completo de cartão.
                    </p>
                </section>

                <section id="logs">
                    <h2>4. Logs, segurança e prevenção de abuso</h2>
                    <p>
                        Podemos registrar logs de acesso, IPs, horários, ações administrativas,
                        tentativas de login, alterações em serviços e eventos técnicos para proteger
                        contas, investigar incidentes e manter a estabilidade da infraestrutura.
                    </p>
                    <p>
                        Esses registros podem ser usados para bloquear ataques, identificar uso indevido,
                        aplicar os Termos de Serviço e cumprir solicitações legais válidas.
                    </p>
                </section>

                <section id="cookies">
                    <h2>5. Cookies e tecnologias semelhantes</h2>
                    <p>
                        Utilizamos cookies e tecnologias semelhantes para manter sessões de login,
                        lembrar preferências, melhorar a experiência do usuário e proteger contra
                        acessos não autorizados.
                    </p>
                    <p>
                        O cliente pode bloquear cookies no navegador, mas isso pode prejudicar o
                        funcionamento de painéis, autenticação e área do cliente.
                    </p>
                </section>

                <section id="compartilhamento">
                    <h2>6. Compartilhamento de dados</h2>
                    <p>
                        A Saturn Cloud não vende dados pessoais. Dados podem ser compartilhados apenas
                        quando necessário com:
                    </p>

                    <ul>
                        <li>Processadores de pagamento;</li>
                        <li>Serviços de infraestrutura, hospedagem, segurança, e-mail e monitoramento;</li>
                        <li>Ferramentas de suporte e atendimento;</li>
                        <li>Autoridades públicas, mediante obrigação legal, ordem judicial ou requisição válida;</li>
                        <li>Parceiros técnicos necessários para execução dos serviços contratados.</li>
                    </ul>
                </section>

                <section id="armazenamento">
                    <h2>7. Armazenamento e retenção</h2>
                    <p>
                        Os dados são mantidos pelo tempo necessário para prestação dos serviços,
                        cumprimento de obrigações legais, prevenção de fraude, resolução de disputas
                        e segurança da infraestrutura.
                    </p>
                    <p>
                        Após o encerramento da conta ou serviço, determinados dados podem continuar
                        armazenados pelo período exigido por lei ou necessário para defesa de direitos.
                    </p>
                </section>

                <section id="seguranca">
                    <h2>8. Segurança dos dados</h2>
                    <p>
                        Aplicamos medidas técnicas e administrativas para proteger dados contra acesso
                        não autorizado, perda, alteração, divulgação indevida ou destruição acidental.
                    </p>
                    <p>
                        Mesmo assim, nenhum sistema é absolutamente imune a falhas, ataques ou incidentes.
                        O cliente também deve manter senhas fortes, autenticação segura e backups próprios.
                    </p>
                </section>

                <section id="direitos">
                    <h2>9. Direitos do cliente</h2>
                    <p>
                        O cliente pode solicitar acesso, correção, atualização, exclusão ou limitação
                        do tratamento de seus dados pessoais, conforme a legislação aplicável.
                    </p>
                    <p>
                        Algumas solicitações podem não ser atendidas imediatamente quando houver obrigação
                        legal, necessidade de segurança, prevenção de fraude ou preservação de direitos.
                    </p>
                </section>

                <section id="menores">
                    <h2>10. Menores de idade</h2>
                    <p>
                        Serviços contratados por menores de idade devem possuir autorização dos responsáveis
                        legais. Caso sejam identificados dados fornecidos sem autorização adequada, a Saturn Cloud
                        poderá suspender a conta até regularização.
                    </p>
                </section>

                <section id="alteracoes">
                    <h2>11. Alterações nesta Política</h2>
                    <p>
                        Esta Política de Privacidade pode ser atualizada a qualquer momento para refletir
                        mudanças legais, técnicas, comerciais ou operacionais.
                    </p>
                    <p>
                        A versão publicada nesta página será considerada a versão vigente.
                    </p>
                </section>

                <section id="contato">
                    <h2>12. Contato</h2>
                    <p>
                        Para dúvidas, solicitações ou assuntos relacionados à privacidade e proteção de dados,
                        entre em contato pelos canais oficiais da Saturn Cloud.
                    </p>

                    <a href="#suporte" class="btn btn-primary" data-support-open>Falar com suporte</a>
                </section>
            </article>
        </div>
    </section>
</main>
@endsection
