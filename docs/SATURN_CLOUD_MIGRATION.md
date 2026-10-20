# Saturn Cloud — dependências legadas

Este rebranding altera apenas a identidade comercial. Os identificadores e destinos abaixo permanecem deliberadamente inalterados até uma migração operacional validada.

## URLs e canais pendentes de migração

- `https://vortex.corelabs.dev.br/sitemap.xml` em `public/robots.txt`: o novo domínio não foi presumido como publicado.
- `node.ion.vortex.corelabs.dev.br` em seeders e migrations do Pterodactyl: host de provisionamento existente.
- `https://www.instagram.com/vortexhostbr/` no rodapé: perfil oficial substituto ainda não foi validado.
- Fallback `vortexhost.com.br` usado na validação de callbacks do Mercado Pago: depende da configuração operacional de `APP_URL`.

## Identificadores técnicos preservados

- Classes e arquivos `VortexSeeder`.
- Origem de checkout `vortexhost`.
- Cookie administrativo `vortex_session`.
- Chaves de idempotência `vortex-renewal-pix-*`.
- IDs externos `vortex-user-*` e identificador de exemplo `srv.vortex`.
- Usuário interno `root@vortex.local`.
- Nome da access key Hestia `vortexhost`.

Nenhum endpoint, webhook, callback, banco, serviço, diretório ou contrato de API foi renomeado nesta etapa.
