# Site e futuro portal

Este repositório contém o site público. Não gerencia contas, banco de dados, pagamentos, faturas, cupons, suporte autenticado ou provisionamento. O Laravel renderiza as páginas e mantém o carrinho anônimo em sessão de arquivo. Nenhum banco existente é apagado pela aplicação.

## Catálogo

Edite `config/catalog.php`. Não havia produtos cadastrados no momento da separação, por isso as listas começam vazias. Exemplo de estrutura (use somente preços e recursos reais):

```php
return [
    'web' => [
        [
            'id' => 'identificador-estavel-no-portal',
            'nome' => 'Nome do plano',
            'preco' => 100.00, // valor ilustrativo; anual para web, mensal para VPS
            'descricao' => 'Descrição do plano',
            'ram' => 'Recursos publicados',
            'armazenamento' => 'Recursos publicados',
            'bancos_dados' => 'Limite publicado',
            'dominios' => 'Limite publicado',
            'featured' => false,
        ],
    ],
    'vps' => [],
];
```

IDs devem ser únicos entre todas as categorias e permanecer estáveis. Campos opcionais: `processamento`, `ddos_protection`, `imagem` (caminho público). A ausência de produtos exibe o estado vazio existente.

## Integração proposta

O portal ainda não existe. Este contrato é uma proposta local, não uma integração validada. Até configurá-lo, o carrinho informa que a finalização estará disponível em breve, sem criar pedido nem cobrar o visitante.

Quando o portal estiver pronto, configure:

```dotenv
PORTAL_URL=https://portal.seu-dominio.com
PORTAL_CART_URL=https://portal.seu-dominio.com/carrinho/importar
```

O botão **Continuar no portal** envia um formulário HTML `POST`, `application/x-www-form-urlencoded`, com:

```text
version=1
items[0][id]=identificador-estavel-no-portal
items[0][quantity]=2
```

Apenas IDs e quantidades são enviados; preços, dados pessoais, credenciais e o token CSRF do site não são enviados. Não há chamada ao portal antes do clique. O carrinho local permanece disponível após a saída.

O endpoint receptor deverá validar os IDs e quantidades, consultar seus próprios preços e disponibilidade, importar as escolhas para a sessão do portal e conduzir o cadastro/login e pagamento. Esse endpoint público de importação não deve cobrar nem modificar serviços; a confirmação da compra pertence ao fluxo autenticado do portal. Defina o contrato definitivo na implementação do portal e ajuste o formulário se necessário. O site só habilita o envio para uma URL HTTPS válida.

Após editar configurações em produção, atualize o cache com `php artisan config:cache`.
