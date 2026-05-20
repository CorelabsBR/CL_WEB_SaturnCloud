@extends('layouts.termos')

@section('title', 'Termos de Serviço')

@section('content')
<main class="terms-page">
    <section class="terms-hero">
        <div class="container">
            <h1>Termos de Serviço</h1>

            <p>
                Ao contratar ou utilizar os serviços da VortexHost, você concorda com as regras,
                limites e responsabilidades descritos nesta página.
            </p>

            <p class="terms-updated">
                Última atualização: {{ date('d/m/Y') }}
            </p>
        </div>
    </section>

    <section class="terms-content">
        <div class="container terms-grid">
            <aside class="terms-sidebar">
                <a href="#introducao">Introdução</a>
                <a href="#definicoes">Definições</a>
                <a href="#cadastro">Cadastro</a>
                <a href="#elegibilidade">Elegibilidade</a>
                <a href="#cancelamento">Cancelamento</a>
                <a href="#uso-aceitavel">Uso aceitável</a>
                <a href="#sigilo">Sigilo</a>
                <a href="#responsabilidade-cliente">Resp. do cliente</a>
                <a href="#responsabilidade-vortex">Resp. da VortexHost</a>
                <a href="#garantias">Garantias</a>
                <a href="#antifraude">Antifraude</a>
                <a href="#privacidade">Privacidade</a>
                <a href="#suspensao">Suspensão</a>
                <a href="#suporte">Suporte</a>
                <a href="#vps">VPS</a>
                <a href="#pagamentos">Pagamentos</a>
            </aside>


            <article class="terms-card">
                <article class="terms-card">
    <section id="introducao">
        <h2>Introdução</h2>
        <p>
            A VortexHost estabelece estes Termos de Serviço para regular a contratação,
            utilização e manutenção dos serviços de hospedagem, servidores de jogos,
            VPS, hospedagem web, painéis e demais produtos digitais oferecidos.
        </p>
        <p>
            Ao criar uma conta, contratar um serviço ou utilizar qualquer recurso da
            VortexHost, o cliente declara que leu, entendeu e concorda integralmente
            com estes termos.
        </p>
    </section>

    <section id="definicoes">
        <h2>Definições</h2>
        <ul>
            <li><strong>Cliente:</strong> pessoa física ou jurídica que contrata ou utiliza os serviços.</li>
            <li><strong>Serviço:</strong> qualquer produto digital fornecido pela VortexHost.</li>
            <li><strong>Conta:</strong> cadastro usado para acessar painéis, pagamentos e serviços.</li>
            <li><strong>Backup:</strong> cópia de segurança dos arquivos, sob responsabilidade principal do cliente.</li>
            <li><strong>Chargeback:</strong> contestação de pagamento feita pelo cliente junto ao meio de pagamento.</li>
        </ul>
    </section>

    <section id="cadastro">
        <h2>1. Cadastro</h2>
        <p>
            O cliente deve informar dados verdadeiros, completos e atualizados. Contas com
            dados falsos, incompletos, suspeitos ou de terceiros poderão ser suspensas até
            regularização.
        </p>
    </section>

    <section id="elegibilidade">
        <h2>2. Elegibilidade</h2>
        <p>
            Ao contratar os serviços, o cliente declara possuir capacidade legal para isso.
            Caso o contratante seja menor de idade, entende-se que há autorização e ciência
            dos responsáveis legais.
        </p>
    </section>

    <section id="cancelamento">
        <h2>3. Cancelamento e Reembolso</h2>
        <p>
            O cliente poderá solicitar cancelamento pela área do cliente ou canais oficiais
            de atendimento. O pedido de cancelamento impede novas cobranças, mas não significa
            reembolso automático.
        </p>
        <p>
            Reembolsos podem ser analisados em até 7 dias após a contratação, conforme a lei
            aplicável. Serviços personalizados, adicionais, licenças, IPs, instalações,
            configurações técnicas e produtos já entregues podem não ser reembolsáveis.
        </p>
        <p>
            Serviços suspensos por violação destes termos, abuso, fraude, chargeback ou uso
            inadequado não terão direito a reembolso.
        </p>
    </section>

    <section id="uso-aceitavel">
        <h2>4. Política de Uso Aceitável</h2>
        <p>É proibido utilizar os serviços da VortexHost para:</p>
        <ul>
            <li>Atividades ilegais, fraudes, golpes ou violação da legislação brasileira;</li>
            <li>Hospedagem ou distribuição de malware, phishing, botnets ou scripts nocivos;</li>
            <li>Envio de spam, abuso de e-mail ou campanhas não solicitadas;</li>
            <li>Mineração de criptomoedas sem autorização expressa;</li>
            <li>Ataques DDoS, força bruta, scan de portas ou exploração de vulnerabilidades;</li>
            <li>Hospedagem de conteúdo pirata, protegido por direitos autorais ou sem autorização;</li>
            <li>Uso excessivo de CPU, RAM, disco, rede ou qualquer recurso que prejudique a infraestrutura;</li>
            <li>Conteúdo abusivo, difamatório, discriminatório, ilegal ou que viole direitos de terceiros.</li>
        </ul>
    </section>

    <section id="sigilo">
        <h2>5. Quebra de Sigilo e Autoridades</h2>
        <p>
            A VortexHost poderá fornecer dados cadastrais, registros de acesso, informações
            técnicas e demais dados necessários quando houver solicitação legal, ordem judicial
            ou requisição válida de autoridade competente.
        </p>
    </section>

    <section id="responsabilidade-cliente">
        <h2>6. Responsabilidades do Cliente</h2>
        <ul>
            <li>Manter seus dados de acesso seguros;</li>
            <li>Realizar backups próprios e frequentes;</li>
            <li>Garantir que seus arquivos e aplicações não violem leis ou direitos de terceiros;</li>
            <li>Responder por todas as ações realizadas em sua conta ou serviço;</li>
            <li>Manter sistemas, plugins, mods, aplicações e senhas atualizados.</li>
        </ul>
    </section>

    <section id="responsabilidade-vortex">
        <h2>7. Responsabilidades da VortexHost</h2>
        <p>
            A VortexHost se compromete a prestar os serviços contratados conforme o plano
            escolhido, buscando estabilidade, segurança e disponibilidade da infraestrutura.
        </p>
        <p>
            Não nos responsabilizamos por perdas causadas por erro do cliente, má configuração,
            uso incorreto, falhas de terceiros, ataques externos, indisponibilidade de provedores,
            força maior ou eventos fora do nosso controle razoável.
        </p>
    </section>

    <section id="garantias">
        <h2>8. Fornecimento e Garantias</h2>
        <p>
            Os serviços são fornecidos conforme disponibilidade técnica e características do
            plano contratado. Manutenções programadas, atualizações, incidentes de rede,
            ataques e falhas de fornecedores podem afetar temporariamente a disponibilidade.
        </p>
    </section>

    <section id="antifraude">
        <h2>9. Antifraude</h2>
        <p>
            Pedidos com suspeita de fraude, dados inconsistentes, uso de terceiros, pagamento
            irregular ou chargeback poderão ser suspensos, cancelados ou submetidos à verificação
            adicional.
        </p>
    </section>

    <section id="privacidade">
        <h2>10. Privacidade e Dados</h2>
        <p>
            Os dados do cliente são utilizados para cadastro, pagamento, suporte, segurança,
            entrega dos serviços e cumprimento de obrigações legais. A VortexHost não vende
            dados pessoais de clientes.
        </p>
    </section>

    <section id="propriedade">
        <h2>11. Propriedade Intelectual</h2>
        <p>
            O cliente mantém a responsabilidade sobre os arquivos, sistemas, marcas, conteúdos
            e aplicações hospedadas. É proibido armazenar ou distribuir conteúdo sem autorização
            do titular dos direitos.
        </p>
    </section>

    <section id="limitacao">
        <h2>12. Limitação de Responsabilidade</h2>
        <p>
            A VortexHost não será responsável por perdas indiretas, lucros cessantes, perda de
            receita, danos comerciais, perda de dados, interrupções externas ou prejuízos causados
            por uso inadequado do serviço.
        </p>
    </section>

    <section id="suspensao">
        <h2>13. Suspensão e Exclusão</h2>
        <p>
            Serviços poderão ser suspensos ou encerrados em caso de inadimplência, violação dos
            termos, abuso de recursos, risco à infraestrutura, fraude, ordem legal ou uso indevido.
        </p>
    </section>

    <section id="suporte">
        <h2>14. Atendimento e Suporte</h2>
        <p>
            O suporte será prestado pelos canais oficiais da VortexHost. O suporte cobre problemas
            relacionados à infraestrutura contratada, não incluindo desenvolvimento, correção de
            código, configuração avançada de aplicações ou administração completa de serviços
            self-managed, salvo contratação específica.
        </p>
    </section>

    <section id="vps">
        <h2>15. VPS e Serviços Self-Managed</h2>
        <p>
            Em serviços VPS ou self-managed, o cliente é responsável pela administração do sistema,
            instalação de softwares, segurança, firewall, atualizações, backups e configurações
            internas do ambiente.
        </p>
    </section>

    <section id="pagamentos">
        <h2>16. Pagamentos e Prazos</h2>
        <p>
            A ativação dos serviços ocorre após confirmação de pagamento e análise antifraude,
            quando aplicável. A falta de pagamento poderá gerar suspensão e posterior remoção
            definitiva dos dados.
        </p>
    </section>

    <section id="alteracoes">
        <h2>17. Alterações dos Termos</h2>
        <p>
            Estes termos podem ser alterados a qualquer momento. A versão publicada nesta página
            será considerada vigente para novas contratações e continuidade do uso dos serviços.
        </p>
    </section>

    <section id="foro">
        <h2>18. Foro</h2>
        <p>
            Fica eleito o foro da comarca correspondente à sede da VortexHost, salvo disposição
            legal obrigatória em contrário.
        </p>
    </section>
</article>
            </article>
        </div>
    </section>
</main>
@endsection
