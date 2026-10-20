@extends('layouts.termos')

@section('title', 'Política de Reembolso')

@section('content')
<main class="refund-page">
    <section class="refund-hero">
        <div class="container">
            <h1>Política de Reembolso</h1>

            <p>
                Esta Política explica quando a Saturn Cloud pode aprovar, recusar ou analisar
                solicitações de reembolso relacionadas aos serviços contratados.
            </p>

            <p class="refund-updated">
                Última atualização: {{ date('d/m/Y') }}
            </p>
        </div>
    </section>

    <section class="refund-content">
        <div class="container refund-grid">
            <aside class="refund-sidebar">
                <a href="#introducao">Introdução</a>
                <a href="#prazo">Prazo</a>
                <a href="#elegibilidade">Elegibilidade</a>
                <a href="#nao-reembolsavel">Não reembolsável</a>
                <a href="#cancelamento">Cancelamento</a>
                <a href="#chargeback">Chargeback</a>
                <a href="#analise">Análise</a>
                <a href="#forma">Forma de reembolso</a>
                <a href="#abuso">Abuso</a>
                <a href="#alteracoes">Alterações</a>
                <a href="#contato">Contato</a>
            </aside>

            <article class="refund-card">
                <section id="introducao">
                    <h2>Introdução</h2>
                    <p>
                        A Saturn Cloud busca manter uma política clara e justa para reembolsos.
                        Ao contratar qualquer serviço, o cliente declara estar ciente das condições
                        descritas nesta página.
                    </p>
                    <p>
                        O reembolso não é automático e depende de análise conforme o tipo de serviço,
                        tempo de uso, forma de pagamento, motivo da solicitação e cumprimento dos
                        Termos de Serviço.
                    </p>
                </section>

                <section id="prazo">
                    <h2>1. Prazo para solicitação</h2>
                    <p>
                        O cliente poderá solicitar reembolso em até 7 dias corridos após a contratação,
                        desde que o serviço se enquadre nas condições desta Política.
                    </p>
                    <p>
                        Após esse prazo, pedidos de reembolso poderão ser recusados, salvo em casos
                        excepcionais analisados pela Saturn Cloud.
                    </p>
                </section>

                <section id="elegibilidade">
                    <h2>2. Serviços elegíveis para reembolso</h2>
                    <p>Podem ser elegíveis para análise de reembolso:</p>

                    <ul>
                        <li>Serviços contratados por engano e ainda não utilizados de forma relevante;</li>
                        <li>Serviços que não foram ativados por falha interna da Saturn Cloud;</li>
                        <li>Cobranças duplicadas confirmadas;</li>
                        <li>Problemas técnicos comprovados que impeçam totalmente o uso do serviço;</li>
                        <li>Pedidos feitos dentro do prazo de 7 dias corridos após a contratação.</li>
                    </ul>
                </section>

                <section id="nao-reembolsavel">
                    <h2>3. Serviços e situações não reembolsáveis</h2>
                    <p>Não haverá reembolso em casos como:</p>

                    <ul>
                        <li>Serviços já utilizados normalmente pelo cliente;</li>
                        <li>Renovações automáticas ou manuais após o período contratado;</li>
                        <li>Serviços personalizados, configurações, instalações ou migrações já executadas;</li>
                        <li>Registro, renovação ou transferência de domínios;</li>
                        <li>Licenças, IPs dedicados, addons e recursos adicionais já entregues;</li>
                        <li>Suspensão ou cancelamento por violação dos Termos de Serviço;</li>
                        <li>Uso indevido, abuso de recursos, fraude, spam, ataques ou atividade ilegal;</li>
                        <li>Desistência após uso significativo do serviço;</li>
                        <li>Problemas causados por erro de configuração do cliente.</li>
                    </ul>
                </section>

                <section id="cancelamento">
                    <h2>4. Cancelamento do serviço</h2>
                    <p>
                        O cancelamento impede novas cobranças futuras, mas não gera reembolso automático
                        de valores já pagos.
                    </p>
                    <p>
                        Após o cancelamento, dados, arquivos, bancos de dados e configurações poderão ser
                        removidos definitivamente conforme os prazos internos da Saturn Cloud.
                    </p>
                </section>

                <section id="chargeback">
                    <h2>5. Chargeback e contestação de pagamento</h2>
                    <p>
                        Contestações de pagamento abertas diretamente com bancos, operadoras de cartão ou
                        intermediadores poderão resultar na suspensão imediata da conta e dos serviços
                        relacionados.
                    </p>
                    <p>
                        Em caso de chargeback indevido, a Saturn Cloud poderá bloquear novas contratações,
                        encerrar serviços ativos e aplicar medidas administrativas para proteção da plataforma.
                    </p>
                </section>

                <section id="analise">
                    <h2>6. Processo de análise</h2>
                    <p>
                        Toda solicitação será analisada pela equipe da Saturn Cloud. Poderão ser verificados
                        dados da contratação, logs de uso, status do serviço, pagamentos, suporte prestado
                        e eventuais violações de política.
                    </p>
                    <p>
                        A Saturn Cloud poderá solicitar informações adicionais antes de aprovar ou recusar
                        o pedido.
                    </p>
                </section>

                <section id="forma">
                    <h2>7. Forma de reembolso</h2>
                    <p>
                        Quando aprovado, o reembolso será realizado preferencialmente pelo mesmo método de
                        pagamento utilizado na contratação.
                    </p>
                    <p>
                        Dependendo do método de pagamento, banco ou intermediador, o prazo para o valor
                        retornar ao cliente pode variar e não depende exclusivamente da Saturn Cloud.
                    </p>
                </section>

                <section id="abuso">
                    <h2>8. Abuso da política de reembolso</h2>
                    <p>
                        Solicitações repetidas, má-fé, uso temporário recorrente, fraude, manipulação de
                        pagamentos ou tentativa de obter serviço gratuito poderão resultar na recusa do
                        reembolso e bloqueio da conta.
                    </p>
                </section>

                <section id="alteracoes">
                    <h2>9. Alterações nesta Política</h2>
                    <p>
                        Esta Política de Reembolso pode ser atualizada a qualquer momento para refletir
                        mudanças operacionais, legais ou comerciais.
                    </p>
                    <p>
                        A versão publicada nesta página será considerada a versão vigente.
                    </p>
                </section>

                <section id="contato">
                    <h2>10. Contato</h2>
                    <p>
                        Para solicitar reembolso ou tirar dúvidas sobre esta Política, entre em contato
                        pelos canais oficiais de atendimento da Saturn Cloud.
                    </p>

                    <a href="#suporte" class="btn btn-primary" data-support-open>Solicitar atendimento</a>
                </section>
            </article>
        </div>
    </section>
</main>
@endsection
