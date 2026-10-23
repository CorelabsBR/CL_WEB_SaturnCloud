# Saturn Cloud — site público

Site institucional e catálogo de hospedagem e VPS. O visitante monta um carrinho sem conta e, quando configurado, segue para o portal do cliente. Login, pagamento e gestão dos serviços pertencem ao portal.

## Executar

Requisitos: PHP 8.3+, Composer e Node.js com npm.

```sh
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan serve
```

Não é necessário criar banco ou executar migrations. Sessões e cache usam arquivos em `storage/`; mantenha essa pasta e `bootstrap/cache` graváveis. O carrinho usa um cookie de sessão e não depende de login. Não há worker de filas ou tarefas de cobrança para executar.

Edite o catálogo em `config/catalog.php`. Configure os destinos do portal por `PORTAL_URL` e `PORTAL_CART_URL` quando estiverem disponíveis. Veja [o contrato proposto e a configuração do catálogo](docs/portal.md).

```sh
php artisan test
npm run build
```
