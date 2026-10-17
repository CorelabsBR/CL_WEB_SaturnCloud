<?php

return [
    'categories' => [
        'primeiros-passos' => ['name' => 'Primeiros passos', 'description' => 'Entenda os serviços e comece sem complicação.'],
        'dominios-dns' => ['name' => 'Domínios e DNS', 'description' => 'Conecte seu domínio ao servidor corretamente.'],
        'hospedagem' => ['name' => 'Hospedagem de sites', 'description' => 'Publique, proteja e administre seu site.'],
        'vps' => ['name' => 'VPS Linux e Windows', 'description' => 'Acesse e cuide do seu servidor virtual.'],
        'financeiro' => ['name' => 'Financeiro e contratos', 'description' => 'Pedidos, Pix, cartão, renovação e cancelamento.'],
        'seguranca' => ['name' => 'Segurança', 'description' => 'Boas práticas para proteger conta e serviços.'],
        'problemas' => ['name' => 'Resolvendo problemas', 'description' => 'Diagnósticos simples antes de abrir um chamado.'],
        'glossario' => ['name' => 'Glossário', 'description' => 'Termos técnicos explicados em linguagem simples.'],
    ],
    'articles' => [
        'o-que-e-hospedagem-de-sites' => ['category'=>'primeiros-passos','title'=>'O que é hospedagem de sites?','excerpt'=>'Entenda onde seu site fica e por que ele precisa de hospedagem.','sections'=>[
            ['heading'=>null,'paragraphs'=>['Pense no seu site como uma casa. A hospedagem é o terreno onde os arquivos, imagens, banco de dados e e-mails ficam armazenados. Esse espaço está em um servidor conectado à internet.','Sem hospedagem, o conteúdo do site não fica disponível para visitantes. Na hospedagem gerenciada, a Vortex mantém a infraestrutura e você administra seu projeto pelo painel.'],'items'=>['Hospedagem é indicada para sites institucionais, blogs, portfólios e lojas pequenas.','Os planos web atuais da Vortex têm cobrança anual.','Os recursos disponíveis variam conforme o plano contratado.']],
        ]],
        'o-que-e-vps' => ['category'=>'primeiros-passos','title'=>'O que é VPS e quando eu preciso de um?','excerpt'=>'Saiba quando um servidor virtual é melhor que hospedagem compartilhada.','sections'=>[
            ['heading'=>null,'paragraphs'=>['VPS significa Servidor Virtual Privado. É um ambiente virtual isolado no qual você pode executar aplicações, APIs, bancos de dados e outros serviços.','Uma VPS oferece mais autonomia, mas também exige conhecimento para atualizar, configurar e proteger o sistema.'],'items'=>['Use VPS para sistemas próprios, APIs, automações ou múltiplos sites.','Use hospedagem web quando quiser publicar um site sem administrar o sistema operacional.','Confirme sistema operacional, acesso e recursos na oferta de cada plano.']],
        ]],
        'hospedagem-ou-vps' => ['category'=>'primeiros-passos','title'=>'Hospedagem ou VPS: qual escolher?','excerpt'=>'Compare as duas opções de forma rápida.','sections'=>[
            ['heading'=>'Escolha hospedagem web','paragraphs'=>['É a opção mais simples para site empresarial, portfólio, blog, WordPress ou uma loja pequena.'],'items'=>['Painel mais simples','Infraestrutura administrada','Menor necessidade de conhecimento técnico']],
            ['heading'=>'Escolha VPS','paragraphs'=>['É indicada para sistemas próprios e projetos que precisam de controle sobre o ambiente.'],'items'=>['Aplicações e APIs','Instalação de software próprio','Maior autonomia de configuração']],
        ]],
        'como-criar-conta' => ['category'=>'primeiros-passos','title'=>'Como criar sua conta na Vortex','excerpt'=>'Cadastre-se e complete seus dados com segurança.','sections'=>[
            ['heading'=>'Passo a passo','paragraphs'=>[],'items'=>['Acesse a página Criar conta.','Informe um e-mail que você realmente acessa e crie uma senha forte.','Complete nome, CPF ou CNPJ, telefone e endereço.','Use o código de verificação enviado ao e-mail quando o envio estiver habilitado.','Depois da confirmação, entre na área do cliente.']],
        ]],
        'como-contratar-um-plano' => ['category'=>'primeiros-passos','title'=>'Como contratar um plano','excerpt'=>'Da escolha do produto ao acompanhamento do pedido.','sections'=>[
            ['heading'=>'Contratação','paragraphs'=>['Os preços e especificações válidos são os exibidos na página do produto no momento do pedido.'],'items'=>['Abra Hospedagem Web ou VPS.','Compare os recursos e clique em Contratar.','Complete o cadastro, caso seja solicitado.','Revise o carrinho e prossiga ao checkout.','Pague pelo ambiente seguro do Mercado Pago.','Acompanhe o pedido na área do cliente.']],
        ]],
        'o-que-e-dominio' => ['category'=>'dominios-dns','title'=>'O que é um domínio?','excerpt'=>'Entenda a diferença entre domínio, hospedagem e servidor.','sections'=>[
            ['heading'=>null,'paragraphs'=>['Domínio é o endereço digitado no navegador, como exemplo.com.br. Hospedagem é o espaço onde o site fica. DNS é o sistema que conecta o domínio ao endereço do servidor.','A Vortex não comercializa domínios diretamente neste momento. Para domínios .br, consulte o Registro.br.'],'items'=>[]],
        ]],
        'o-que-e-dns' => ['category'=>'dominios-dns','title'=>'O que é DNS?','excerpt'=>'Uma explicação simples sobre como o domínio encontra seu servidor.','sections'=>[
            ['heading'=>null,'paragraphs'=>['DNS funciona como uma lista de endereços da internet. Ele informa para qual servidor o visitante deve ser enviado quando digita seu domínio.'],'items'=>['A aponta um nome para um endereço IPv4.','AAAA aponta para um endereço IPv6.','CNAME aponta um nome para outro nome.','MX define onde os e-mails são recebidos.','TXT é usado em verificações, SPF e DKIM.']],
            ['heading'=>'Propagação','paragraphs'=>['Alterações de DNS não aparecem ao mesmo tempo em todos os provedores. Evite fazer novas mudanças enquanto a configuração se propaga.'],'items'=>[]],
        ]],
        'apontar-dominio' => ['category'=>'dominios-dns','title'=>'Como apontar seu domínio para a Vortex','excerpt'=>'Use os dados exibidos no seu serviço para configurar o DNS.','sections'=>[
            ['heading'=>'Antes de começar','paragraphs'=>['Abra seu serviço na área do cliente e use somente os nameservers ou endereços informados ali. Se eles não estiverem visíveis, abra um chamado.'],'items'=>['Entre no registrador onde comprou o domínio.','Localize a área de DNS ou nameservers.','Substitua os dados pelos exibidos no serviço Vortex.','Salve e aguarde a propagação.']],
        ]],
        'acessar-painel-hospedagem' => ['category'=>'hospedagem','title'=>'Como acessar o painel da hospedagem','excerpt'=>'Entre no painel Hestia a partir da área do cliente.','sections'=>[
            ['heading'=>'Acesso','paragraphs'=>['Entre na área do cliente, abra o serviço de hospedagem e use a ação de acesso ao painel Hestia disponível no serviço. Não compartilhe usuário ou senha.'],'items'=>['Gerencie domínios e arquivos.','Crie bancos de dados e contas de e-mail quando incluídos.','Consulte os recursos liberados no seu plano.']],
        ]],
        'colocar-site-no-ar' => ['category'=>'hospedagem','title'=>'Como colocar seu site no ar','excerpt'=>'Envie os arquivos e confira o domínio.','sections'=>[
            ['heading'=>'Publicação de arquivos','paragraphs'=>['No painel de hospedagem, abra o gerenciador de arquivos e localize o diretório público do domínio. No Hestia, o caminho pode variar conforme o domínio criado.'],'items'=>['Envie os arquivos do projeto.','Extraia o ZIP quando necessário.','Confirme que existe um arquivo inicial, como index.html ou index.php.','Aponte o DNS e teste o domínio em uma aba anônima.']],
        ]],
        'ativar-ssl' => ['category'=>'hospedagem','title'=>'Como ativar o SSL','excerpt'=>'Ative HTTPS e o cadeado no navegador.','sections'=>[
            ['heading'=>'Certificado','paragraphs'=>['O domínio precisa apontar corretamente para o servidor antes da emissão. No painel, abra as configurações do domínio e habilite SSL/Let’s Encrypt quando o recurso estiver disponível.'],'items'=>['Confirme o DNS.','Solicite o certificado no painel.','Ative o redirecionamento para HTTPS.','Se falhar, aguarde a propagação ou abra um chamado.']],
        ]],
        'backup-do-site' => ['category'=>'hospedagem','title'=>'Como fazer backup do site','excerpt'=>'Crie uma cópia independente dos arquivos e do banco.','sections'=>[
            ['heading'=>'Backup seguro','paragraphs'=>['A frequência de backup automático depende do serviço e não deve ser presumida. Mantenha sempre uma cópia fora do servidor.'],'items'=>['Baixe os arquivos do site.','Exporte o banco de dados.','Guarde a cópia em local separado.','Registre a data e teste a restauração quando possível.']],
        ]],
        'primeiro-acesso-vps-linux' => ['category'=>'vps','title'=>'Primeiro acesso à VPS Linux','excerpt'=>'Conecte-se com SSH sem expor suas credenciais.','sections'=>[
            ['heading'=>'Conexão SSH','paragraphs'=>['Use o endereço IP, usuário e método de autenticação fornecidos no serviço. Nunca publique esses dados em mensagens abertas.'],'items'=>['No terminal, execute ssh usuario@IP_DO_SERVIDOR substituindo pelos dados reais.','Confirme a identidade do servidor somente se o IP estiver correto.','Troque a senha inicial quando aplicável.','Atualize o sistema e configure firewall antes de publicar aplicações.']],
        ]],
        'primeiro-acesso-vps-windows' => ['category'=>'vps','title'=>'Primeiro acesso à VPS Windows','excerpt'=>'Acesse o servidor pelo cliente de Área de Trabalho Remota.','sections'=>[
            ['heading'=>'Conexão RDP','paragraphs'=>['Este artigo só se aplica quando a proposta inclui Windows e uma licença válida. Use o IP e as credenciais exibidas no serviço.'],'items'=>['Abra Conexão de Área de Trabalho Remota.','Digite o IP do servidor.','Entre com o usuário administrativo fornecido.','Altere a senha inicial e mantenha as atualizações ativas.']],
        ]],
        'pagar-com-pix' => ['category'=>'financeiro','title'=>'Como pagar com Pix','excerpt'=>'Pague somente pelo QR Code gerado no checkout ou na fatura.','sections'=>[
            ['heading'=>'Pagamento seguro','paragraphs'=>['A Vortex usa o Mercado Pago para processar pagamentos. O QR Code e o código copia e cola devem ser gerados dentro do fluxo oficial.'],'items'=>['Abra o checkout ou a fatura.','Gere o Pix.','Confira valor e recebedor no aplicativo do banco.','Conclua o pagamento e acompanhe o status na área do cliente.','Nunca faça Pix enviado informalmente por mensagem.']],
        ]],
        'proteger-conta' => ['category'=>'seguranca','title'=>'Como proteger sua conta e seus serviços','excerpt'=>'Medidas simples que evitam a maioria dos incidentes.','sections'=>[
            ['heading'=>'Boas práticas','paragraphs'=>[],'items'=>['Use uma senha exclusiva e longa.','Não compartilhe códigos de verificação.','Mantenha WordPress, plugins e sistemas atualizados.','Prefira chaves SSH em servidores Linux.','Restrinja portas no firewall.','Guarde backups fora do servidor.']],
        ]],
        'site-fora-do-ar' => ['category'=>'problemas','title'=>'Meu site está fora do ar: o que verificar','excerpt'=>'Faça um diagnóstico rápido antes de abrir o chamado.','sections'=>[
            ['heading'=>'Checklist','paragraphs'=>[],'items'=>['Teste em outra rede ou no 4G.','Confirme se o domínio não venceu.','Verifique se o DNS aponta para o destino correto.','Confira o status do serviço e das faturas na área do cliente.','Teste se o problema ocorre em todas as páginas.','Anote horário, mensagem de erro e passos para reproduzir.']],
        ]],
        'glossario-hospedagem' => ['category'=>'glossario','title'=>'Glossário de hospedagem e VPS','excerpt'=>'Os principais termos técnicos em linguagem direta.','sections'=>[
            ['heading'=>null,'paragraphs'=>[],'items'=>['DNS: conecta o domínio ao servidor.','SSL: protege a conexão HTTPS.','IP: endereço numérico do servidor.','SSH: acesso remoto seguro, comum em Linux.','RDP: acesso remoto gráfico, comum em Windows.','CPU: capacidade de processamento.','RAM: memória usada pelos programas.','NVMe: tecnologia de armazenamento de alto desempenho.','Root: usuário administrativo no Linux.','Backup: cópia usada para recuperação.']],
        ]],
        'como-abrir-chamado' => ['category'=>'problemas','title'=>'Como abrir um chamado eficiente','excerpt'=>'Envie as informações certas para acelerar o atendimento.','sections'=>[
            ['heading'=>'Inclua no chamado','paragraphs'=>['Entre na sua conta e abra Suporte. Nunca envie senhas em canais públicos.'],'items'=>['Serviço ou número do pedido.','Descrição do que deveria acontecer.','Mensagem de erro completa.','Horário aproximado.','Passos para reproduzir.','Imagem sem dados sensíveis, quando ajudar.']],
        ]],
    ],
];
