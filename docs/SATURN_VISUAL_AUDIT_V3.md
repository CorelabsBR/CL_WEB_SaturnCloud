# Saturn Cloud — Auditoria visual V3

## Comparação objetiva

| Área | Implementação anterior | Referências estudadas | Direção V3 |
| --- | --- | --- | --- |
| Marca | Símbolo isolado no header e logo horizontal comprimido no footer | Marca legível e proporcional na navegação | Asset horizontal oficial no header/footer; símbolo preservado no favicon e grafismos |
| CSS | Home, autenticação, VPS, carrinho, dashboard e checkout carregados juntos | Folhas e componentes com responsabilidade visual clara | Home e VPS carregam somente CSS base, header, footer e a folha da própria página |
| Hero | Abstração orbital sem demonstrar o produto | Hyze combina mensagem direta com visualização concreta do produto | Home mostra uma representação da área do cliente; VPS mostra a arquitetura do servidor |
| Produtos | Cards e seções com peso visual semelhante | Produtos aparecem cedo e têm caminhos de decisão claros | Duas entradas comerciais distintas e planos conectados aos dados reais |
| VPS | Hero com terminal genérico, cards básicos e três casos de uso | Astral e Hostinger facilitam a comparação de recursos | Planos, tabela comparativa condicional, explicação, jornada e FAQ específico |
| Tipografia | Escala irregular e textos auxiliares pequenos | Títulos expressivos, corpo confortável e metadados discretos | Escala responsiva com corpo entre 0,8 e 1,05 rem e títulos fluidos |
| Espaçamento | Grandes vazios sem função em alguns trechos | Ritmo alterna narrativa, produto e prova funcional | Espaço usado para separar decisões; divisórias constroem continuidade |
| Mobile | Desktop comprimido e navegação concorrendo com ações | Hierarquia reduzida e CTAs de largura total | Grid de uma coluna, menu compacto, tabelas roláveis e cenas simplificadas |

## Referências consultadas

- Hyze Cloud: homepage e página Sobre, como referência principal de ritmo, clareza e demonstração visual do produto.
- Astral Cloud: homepage e página de VPS, para organização das escolhas e leitura de especificações.
- Kronic Host: homepage e ofertas públicas, para observar hierarquia comercial.
- Hostinger: referência secundária de organização de catálogo e comparação.

Nenhum texto, imagem, dado comercial ou componente proprietário foi reutilizado.

## Restrições de conteúdo

- Nenhum uptime, SLA, localização, tráfego, sistema operacional ou certificação foi presumido.
- Preço, RAM, armazenamento, processamento e proteção DDoS vêm dos campos existentes de `Product`.
- A comparação VPS só aparece quando há produtos cadastrados.
- Quando não há ofertas, a interface apresenta um estado vazio comercial sem fabricar planos.
