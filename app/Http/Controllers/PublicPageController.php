<?php

namespace App\Http\Controllers;

class PublicPageController extends Controller
{
    public function show(string $page)
    {
        $pages = [
            'vps-linux' => ['VPS Linux', 'VPS Linux para aplicações, APIs e automações.', 'A compatibilidade de distribuições, licenças e imagens deve ser confirmada no plano ou na proposta comercial antes da contratação.', ['Ambiente virtual isolado', 'Acesso conforme o plano', 'Recursos publicados por configuração']],
            'vps-windows' => ['VPS Windows', 'Infraestrutura Windows sob análise comercial.', 'Ainda não há oferta pública com licença Windows cadastrada. Solicite uma proposta para validar licenciamento, versão e recursos sem custos ocultos.', ['Licenciamento sob consulta', 'Versão definida na proposta', 'Sem preço ou disponibilidade inventados']],
            'revenda' => ['Revenda de hospedagem', 'Estruture sua operação de hospedagem com uma base profissional.', 'A oferta pública de revenda está em preparação. Fale com a equipe para registrar seu interesse e definir requisitos reais.', ['Painel de gestão sob definição', 'Recursos e limites em validação', 'Proposta comercial sob consulta']],
            'dominios' => ['Domínios', 'O endereço certo para construir sua presença digital.', 'A Vortex ainda não comercializa domínios diretamente. Use a consulta oficial do Registro.br e contrate a hospedagem conosco.', ['Consulta externa no Registro.br', 'Configuração de domínio no painel', 'SSL conforme o plano']],
            'infraestrutura' => ['Infraestrutura', 'Uma camada técnica clara para serviços digitais.', 'Hospedagem e VPS integrados ao painel do cliente, pagamento nacional e processos de provisionamento conectados.', ['Ambientes VPS isolados', 'Armazenamento conforme o plano', 'Suporte com histórico']],
            'sobre' => ['Sobre a Vortex', 'Infraestrutura digital no ecossistema CoreLabs.', 'A Vortex atende projetos web com uma experiência comercial direta: recursos publicados, checkout integrado e gestão centralizada.', ['Marca brasileira', 'Operação orientada a produto', 'Transparência comercial']],
            'faq' => ['Suporte e FAQ', 'Respostas diretas antes e depois da contratação.', 'Consulte as dúvidas frequentes na página inicial ou acesse sua conta para abrir um chamado com histórico.', ['Atendimento autenticado', 'Histórico de mensagens', 'Orientação sobre pedidos e serviços']],
            'contato' => ['Contato', 'Fale com a equipe Vortex.', 'Para assuntos comerciais, escreva para contato@corelabs.dev.br. Clientes podem abrir um chamado pela área autenticada.', ['E-mail comercial', 'Chamados para clientes', 'Atendimento vinculado à conta']],
            'sla' => ['SLA', 'Compromissos de serviço publicados com responsabilidade.', 'O SLA comercial ainda não possui métricas públicas aprovadas. As condições aplicáveis devem constar no plano ou contrato contratado.', ['Sem métricas inventadas', 'Condições por serviço', 'Atualização após aprovação jurídica']],
        ];

        abort_unless(isset($pages[$page]), 404);

        return view('pages.show', ['page' => $page, 'content' => $pages[$page]]);
    }
}
