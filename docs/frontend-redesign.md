# Saturn Cloud — redesign do frontend

## Escopo e auditoria

Implementação na stack existente: Laravel 13, PHP 8.3, Blade e Vite 8. Não foi introduzido React. Three.js é carregado sob demanda; as animações da interface usam a API nativa do navegador. Nenhum controller, modelo, contrato de API, migration, autenticação, pagamento ou provisionamento foi alterado.

Rotas preservadas: home, VPS e suas variantes, hospedagem web, páginas institucionais, central de ajuda e artigos, conteúdos, autenticação e recuperação de conta, carrinho, checkout, área do cliente, suporte autenticado, administração, painéis Hestia/Pterodactyl e páginas legais. Os formulários de contratação continuam enviando POST com CSRF para `cart.addProduct`.

Havia alterações anteriores em `vendor/`, `yarn.lock`, além de `data/`, `skills/` e `package-lock.json` não rastreados. As alterações anteriores de backend foram preservadas. O npm atualizou `package-lock.json` ao instalar as dependências e corrigir vulnerabilidades compatíveis. Use `npm ci` e `npm run build` para reproduzir o frontend.

## Direção visual

Referência consultada visualmente em 22/10/2026: https://hyzecloud.com/pt-br/about. A página apresenta escultura monumental à direita, texto de grande escala integrado à imagem, uma primeira dobra violeta e transição para seções editoriais escuras. Foram aproveitados os princípios de escala, integração e ritmo; nenhum código, texto ou asset da Hyze foi copiado.

O briefing da Saturn prevalece na paleta: preto `#050505`, superfícies `#101012` e `#171619`, branco `#F2F0F5`, cinza `#A3A0AA`, destaque `#7C3AED`. Manrope é servida localmente, com licença OFL em `public/fonts/OFL-Manrope.txt`. Os logotipos são os SVG oficiais já existentes no repositório.

Plano de composição adotado:

```
Home                 Sobre                    VPS
marca / navegação    título editorial         título / escultura compute
headline monumental  introdução em duas áreas configuração e preços reais
Saturno sobreposto   Saturno amplo            comparação técnica
texto / ações        perspectiva / princípios usos / jornada / perguntas
soluções / catálogo  convite                  contratação
```

O Saturno concentra o impacto visual. As demais seções usam hierarquia tipográfica, espaço e poucas superfícies. Infraestrutura tem uma composição própria em camadas. Suporte reúne documentação pesquisável e acesso aos canais existentes. O shell compartilhado estende a identidade às páginas comerciais, institucionais, legais e de conta.

## Arquivos principais

- `public/css/cinematic.css`: tokens, tipografia, navegação, componentes, layouts e adaptação entre 320 px e desktop; carregado após os estilos existentes para preservar as páginas legadas.
- `public/css/brand.css`: resolve o import de tokens que estava ausente nos estilos existentes.
- `resources/js/app.js`: animações nativas, carregamento progressivo da cena, navegação durante scroll e feedback dos formulários de compra.
- `resources/js/saturn-scene.js`: escultura Three.js e ciclo de vida da GPU.
- `resources/views/partials/saturn-scene.blade.php`: cena compartilhada e alternativa sem JavaScript/WebGL.
- `public/img/saturn-fallback.svg`: ilustração vetorial original com bandas e anéis minerais.
- `resources/views/home.blade.php`, `vps/index.blade.php`, `pages/show.blade.php`, `help/index.blade.php`, `web/index.blade.php`: composições e conteúdos integrados aos dados existentes.
- Layouts `app` e `termos`, parciais de header/footer e `public/js/header.js`: identidade, módulos e navegação compartilhados.
- `resources/views/components/plan-card.blade.php`: remove os rótulos não comprovados “Mais vendido” e “NVMe”; mantém os dados do produto.
- `tests/Feature/ExampleTest.php` e `FrontendCatalogTest.php`: regressões visuais do HTML e preservação comercial.

## Cena e desempenho

Planeta oblato com textura procedural de bandas e turbulência, bump map, materiais minerais, anéis com variação radial de opacidade e divisões, seis filamentos tridimensionais, sombra do planeta, iluminação branca e contraluz violeta, partículas discretas. Todos os materiais são originais e gerados localmente: não há download de modelos ou texturas de terceiros.

Three.js fica em um módulo dinâmico, solicitado apenas perto de uma cena visível. Texturas procedurais de 512×256 (256×128 no modo leve) dispensam transferência de imagens grandes. DPR limitado a 1,25 no desktop e 1 em dispositivos menores, com orçamento de 1,4 milhão de pixels; geometria reduzida e sombras desligadas no modo leve. Limite de 24 FPS ou 18 FPS. Sombras são reutilizadas entre quadros e o scroll não gera renderizações extras. Renderização suspensa fora da viewport, em abas ocultas, por solicitação do usuário ou quando `prefers-reduced-motion` está ativo. Scroll altera posição, inclinação, escala e iluminação sem usar estado React ou listeners síncronos pesados. Recursos de geometria, materiais e texturas são liberados ao sair.

A alternativa SVG está presente no HTML antes do JavaScript e continua disponível sem WebGL, com economia de dados ou após perda de contexto. A área da cena tem tamanho reservado, sem depender do carregamento do canvas. Sem movimento preferido, a cena renderiza um quadro estático. Existe controle de pausa acessível.

## Validação

- Build de produção Vite.
- Compilação de todas as views Blade.
- Suíte PHPUnit incluindo preservação de valores de produtos, POST/CSRF e proteção das rotas comerciais.
- Sintaxe JavaScript com `node --check`; PHP com Pint nos testes alterados.
- `npm audit`: zero vulnerabilidades após atualizações compatíveis.
- Chromium real com Playwright: 11 páginas em 1440, 768, 390 e 320 px, 44 navegações com HTTP 200 e sem overflow horizontal.
- Exercícios de menu mobile e Escape, pausa/retomada, movimento reduzido, busca com resultados e vazia, ausência de JavaScript, ausência de WebGL e perda de contexto.
- Capturas de revisão em `output/playwright/` (desktop, mobile e fallbacks).

## Limites práticos

O catálogo disponível neste ambiente está vazio. São exibidos estados reais de consulta comercial, sem inventar planos, preços, localização, banda ou disponibilidade. Valores e especificações com produtos foram exercitados em testes de renderização com dados de teste, sem escrever no catálogo.

Não foram efetuados pagamento real, provisionamento real ou autenticação com credenciais de cliente. A cena foi revisada em Chromium; a renderização em GPUs físicas e outros navegadores ainda exige validação nesses dispositivos. O build sinaliza o tamanho do módulo opcional Three.js (aproximadamente 540 kB minificado / 135 kB gzip), carregado separadamente. Não foi realizado deploy.

Resultados finais: 10 testes / 60 assertions; Pint passou; sintaxe JavaScript válida; auditoria npm sem vulnerabilidades. Busca verificada: 19 artigos inicialmente, 3 para “DNS”, 0 para termo inexistente. Movimento reduzido foi verificado tanto na carga inicial quanto na alteração da preferência. A regra global `[hidden]` garante que os componentes pesquisáveis realmente desapareçam mesmo com estilos `display` próprios.

## Ajuste de carregamento e logos

Atendendo ao relato de carregamento pesado, GSAP foi removido do bundle. JavaScript de entrada: aproximadamente 72,6 kB → 4,2 kB minificado (cerca de 94% menor). No celular, o módulo opcional de aproximadamente 540 kB não é solicitado até o visitante ativar a animação. O mesmo vale para dispositivos limitados, conexões lentas, economia de dados e movimento reduzido. No desktop adequado, a escultura estática aparece imediatamente e o 3D começa a carregar após o evento load, um intervalo de 1,2 s e uma oportunidade ociosa do navegador.

As fontes WOFF2 passam de aproximadamente 190 kB para 61 kB; os arquivos TTF originais foram preservados. O layout carrega os estilos de autenticação, hospedagem, dashboard e checkout apenas nas rotas correspondentes. A volta pelo histórico não força mais um recarregamento completo.

Marca atualizada com os arquivos fornecidos em public/img: logo-128.png (2,3 kB) em header, footer, autenticação e favicon; saturno_branco_quadrado.png nos metadados da organização; Minimalist_Saturn_Orbit_Emblem.webp no compartilhamento. Os originais foram preservados.

Validado em Chromium: chegada mobile sem solicitar Three.js, ativação manual e pausa funcionais; desktop com imagem imediata e upgrade posterior para WebGL; logos e fontes novas carregadas; páginas internas com HTTP 200 e sem overflow. Capturas: output/playwright/optimized-home-mobile.png e optimized-home-desktop.png.

## Saturno fornecido pelo usuário

Todas as cenas grandes compartilham agora `public/img/image.svg`, com proporção original 1024×1024 e transparência preservadas. A parcial `saturn-scene` usa somente a imagem; não há canvas, botão de ativação nem importação do módulo Three.js. O planeta não é substituído após a carga. Logos pequenas continuam usando os assets da marca. O SVG incorpora o PNG original e não é vetorização por curvas.

## Animações do Saturno SVG

O SVG fornecido mantém flutuação e inclinação suaves em um ciclo de 48 segundos, movimento de luz de 60 segundos, parallax, pequena rotação e escala vinculadas ao scroll. São transformações da imagem, sem reconstruir uma superfície 3D. A API nativa de animação compõe os movimentos em camadas separadas. Não há download de Three.js nem canvas; o bundle de entrada tem aproximadamente 3,7 kB minificado / 1,5 kB gzip.

O controle permite pausar/retomar. A animação pausa fora da tela e em abas ocultas; `prefers-reduced-motion` cancela os movimentos e restaura a imagem estática. Scroll usa um único requestAnimationFrame por evento agrupado, sem loop de renderização contínuo em JavaScript. Deslocamentos menores no mobile. Validados build, testes de backend e interações em navegador.

## Modelo 3D do símbolo original — versão atual

A solicitação mais recente substitui o Saturno fotográfico por uma reconstrução volumétrica de `saturno_branco_quadrado.svg`. O modelo usa esfera sólida, grade de tubos curvos, órbitas toroidais, marcadores e satélites com volume. Não usa plano, billboard ou imagem como textura.

Arquivos: `resources/js/saturn-brand-model.js` (geometria), `saturn-brand-viewer.js` (luz e renderização), `saturn-scene.js` (ciclo de vida e movimento) e `public/models/saturn-brand.glb` (modelo exportado). `npm run model:saturn` regenera e valida o GLB. São 6 malhas / 40.904 triângulos / 0 texturas; aproximadamente 834 kB no arquivo GLB. O runtime gera a mesma geometria localmente, sem baixar esse arquivo adicional.

Prévia interativa em `/modelo-saturno`, com controles de câmera, vistas lateral/traseira/superior e download GLB. O SVG original continua como fallback. Desktop carrega 3D depois da página; mobile exige ativação. O bundle de entrada permanece pequeno e o módulo Three.js fica separado. Pausa, visibilidade, DPR e movimento reduzido são tratados pelo renderer.

### Ajuste do display orbital

Planeta menor (escala base 0,88 em vez de 1,22), esfera interna sem brilho/reflexo, câmera alinhada ao equador e eixo do planeta permanentemente vertical. Rotação lenta apenas em Y; a oscilação ascendente/descendente pertence aos anéis, em um grupo separado. Nem scroll nem flutuação inclinam a esfera ou revelam os polos. O GLB foi regenerado. Verificação determinística: 721 poses cobrindo seis minutos de animação e scroll, com eixo global do planeta invariável.

O display foi orientado 90° em Y, com vista lateral fixa. O giro lento ficou somente no globo, preservando a velocidade combinada anterior; a oscilação dos anéis permanece igual. A orientação lateral não retorna à vista frontal durante a animação.

Removidas as bolinhas das junções da grade do globo; malha de linhas mantida. Modelo atual: 5 malhas, 39.896 triângulos, 810.036 bytes, sem texturas. GLB regenerado e a página de prévia lê as estatísticas diretamente dos metadados.

### Enquadramento na borda do display

A esfera fica centralizada na borda inferior do próprio display, com metade fora do enquadramento. A composição usa recorte de viewport e projeção deslocada (`setViewOffset`), preservando a câmera equatorial e o eixo vertical do planeta. Os anéis foram ampliados em 35% em relação à esfera, incluindo os elementos orbitais. Desktop e mobile foram conferidos sem overflow horizontal; o centro da esfera foi validado matematicamente na borda inferior da projeção. A prévia técnica mantém a peça inteira para inspeção.

### Giro com inclinação fixa

A oscilação dos anéis foi removida. A inclinação fica constante e a rotação acontece em torno do eixo local desse plano inclinado, na mesma velocidade angular do globo. O enquadramento lateral, o recorte na borda e o tamanho dos anéis permanecem. A escala também fica constante durante o scroll. Validação de 721 poses confirma que a normal do plano orbital nunca muda e que os elementos giram de fato nesse plano.

### Recorte lateral de aproximadamente 25%

Enquadramento ampliado e centralizado verticalmente na lateral direita do próprio display. A projeção desloca o centro 0,4 raios para fora da borda, deixando aproximadamente 25% da área aparente da esfera visível, em vez de metade na borda inferior. A câmera continua equatorial; inclinação fixa e rotação dos anéis preservadas. Conferido no navegador em 1440 px e 390 px, sem overflow. A prévia técnica continua mostrando a peça inteira.

Orientação atual ajustada de 90° para 75° em Y para abrir a visão da largura dos anéis. O giro mantém o plano orbital fixo, a esfera vertical e o recorte lateral de aproximadamente 25%. GLB regenerado e 721 poses validadas na nova orientação.

### Close-up monumental

A lente do display usa campo de visão de 16° (prévia técnica permanece em 34°), ampliando a esfera em cerca de 2,2 vezes sem aumentar o número de pixels renderizados. O cálculo do recorte considera também as bordas superior e inferior, preservando aproximadamente 25% da área aparente da esfera dentro da câmera. A esfera completa projetaria cerca de 808 px de diâmetro no display desktop de 439 px de altura, e 633 px no display mobile de 344 px. O planeta continua na borda lateral, reto, fosco e com anéis girando em inclinação fixa.

### Área inteira da primeira dobra e acabamento fosco

A cena ocupa agora 100% da hero, com inset zero; a janela retangular interna e sua borda foram removidas. O recorte ocorre somente nos limites externos da seção. O mesmo enquadramento em tela cheia foi aplicado à cena Sobre. Materiais do grid, anéis e satélites: metalness 0 e roughness 1, emissão reduzida e iluminação suavizada. A esfera interna continua sem reflexos. O exportador verifica também os parâmetros foscos antes de regenerar o GLB. Em telas verticais a lente é ajustada para manter o recorte aproximado de um quarto sem perder o preenchimento da seção. O orçamento de pixels continua limitado.
