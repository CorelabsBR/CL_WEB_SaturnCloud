# Saturn Cloud Marketing

## Arquitetura

O módulo estende o Laravel existente. Não há backend paralelo. Campanhas, conteúdo, revisões, integrações, eventos e auditoria usam tabelas `marketing_*`. O acesso administrativo exige sessão válida e `tipo_usuario=root`; mutações web usam CSRF e o grupo possui rate limit.

Segredos de integrações usam o cast `encrypted:array` do Laravel e nunca são serializados pelo modelo. Nenhuma credencial deve ser registrada em logs ou enviada ao navegador.

## Instalação e operação

1. Faça backup do banco.
2. Execute `php artisan migrate --force`.
3. Mantenha `php artisan schedule:run` no cron a cada minuto ou use `php artisan schedule:work` sob um supervisor.
4. Acesse o painel administrativo e selecione a aba `Marketing` (`/admin#marketing`) com uma conta root.
5. Para filas gerais do Laravel, mantenha `php artisan queue:work --tries=3` sob supervisor.

O comando `marketing:process-schedule` é idempotente por estado: somente registros aprovados em `scheduled` e com horário vencido avançam. Ele não chama redes sociais nem inicia mídia paga.

## Fluxos de aprovação

- Campanhas nascem em `draft`. Ativação ou agendamento sem `approved_at` é recusado.
- Conteúdo nasce em `draft` e cria uma revisão. Agendamento/publicação sem `approved_by` é recusado.
- Aprovar não publica. O responsável ainda agenda explicitamente o conteúdo.
- Toda criação, alteração e aprovação administrativa gera auditoria com usuário e hash do IP.

## Analytics e LGPD

`POST /marketing/events` aceita apenas `page_view`, `cta_click` e `signup_started`, exige `consent=true` e limita chamadas. O sistema não persiste IP bruto; mantém hash da sessão, caminho, host referenciador e UTMs. Retenção e banner de consentimento ainda precisam de decisão jurídica/comercial antes da ativação no frontend.

## SEO e conteúdo público

- `/sitemap.xml` é gerado a partir das rotas institucionais e conteúdos realmente publicados.
- `/conteudos` lista somente materiais aprovados e publicados.
- `robots.txt` bloqueia áreas privadas.
- O corpo Markdown é exibido escapado; adicionar um renderizador só deve ocorrer após escolher uma biblioteca mantida e sanitização adequada.

## Integrações e limitações

Instagram, Facebook, LinkedIn, Discord, Telegram e Search Console aparecem inicialmente como **não configurados**. Não há simulação de conectividade. Cada integração futura exige app oficial, OAuth/permissões, política da plataforma, callback seguro, fila idempotente e autorização rastreável.

## Primeira campanha sem mídia paga

1. Selecione um produto que já exista no catálogo.
2. Defina objetivo mensurável, público e uma única página de destino.
3. Crie a campanha no canal `organic` e revise a URL/UTMs.
4. Produza um tutorial que resolva uma dúvida real do público.
5. Solicite aprovação de campanha e conteúdo.
6. Publique manualmente e registre apenas eventos consentidos.
7. Avalie cadastros e pedidos confirmados, não curtidas isoladas.

## Backup e troubleshooting

Inclua todas as tabelas `marketing_*` no backup do banco. Antes de restaurar, preserve `APP_KEY`, pois ela protege credenciais criptografadas. Se agendamentos não avançarem, execute `php artisan schedule:list`, `php artisan marketing:process-schedule` e consulte `storage/logs/laravel.log`. Se o painel retornar 403, confirme autenticação e `tipo_usuario=root`.

## API

A especificação inicial está em `docs/marketing-openapi.yaml` e é disponibilizada somente ao administrador em `/admin/marketing/openapi.yaml`.
