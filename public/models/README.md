# Saturn Cloud — modelo volumétrico da marca

Fonte visual: `public/img/saturno_branco_quadrado.svg`, fornecida pelo usuário.

`saturn-brand.glb` contém 5 malhas, 39.896 triângulos e materiais violeta metalizado, com núcleo escuro sem reflexos. Não contém imagens ou texturas. O globo é uma esfera com núcleo sólido, meridianos e paralelos tubulares; órbitas são toros com seção circular; pontos orbitais são pequenas esferas; satélites são conjuntos de toro e esfera. Há volume em todos os eixos e oclusão entre as peças. Não é uma extrusão plana do contorno nem um plano texturizado.

Abra no Blender, em um visualizador glTF ou na rota local `/modelo-saturno`. O GLB usa metros, eixo Y para cima e materiais violeta e núcleo fosco sem iluminação especular. Geometria reconstruída em 3D a partir dos elementos da marca, com liberdade para inspeção por qualquer ângulo.

Regenerar: `npm run model:saturn`. O script verifica volumes, ausência de texturas e reimporta o GLB exportado com GLTFLoader. Metadados: `saturn-brand.json`.

O site constrói a mesma geometria a partir do módulo compartilhado, evitando baixar também o GLB. Em dispositivos menores ou limitados, usa uma versão com menos segmentos e exige ativação. No desktop, a renderização começa após o carregamento inicial e durante uma oportunidade ociosa. DPR e pixels limitados; 24 FPS; pausas fora da tela e em abas ocultas; movimento reduzido respeitado.

A esfera permanece com o eixo Y vertical e a câmera da animação fica no equador (Y=0). Somente o giro em Y é aplicado ao planeta. A oscilação de inclinação fica no grupo independente dos anéis; scroll não inclina a esfera. O conjunto foi reduzido para escala 0,88 na cena do site. O núcleo usa material unlit, sem brilho ou reflexos. O exportador valida 721 poses ao longo de seis minutos, incluindo variação de scroll, garantindo que o eixo do planeta não tombe.

A orientação do conjunto é fixa em 90° no eixo Y para apresentar a animação de lado. O globo gira internamente, mantendo o eixo vertical. As bolinhas da grade do globo foram removidas; marcadores e satélites dos anéis permanecem.

Os anéis têm escala 1,35 em relação à versão anterior, mantendo o tamanho do globo. No site a esfera é enquadrada pela metade na borda inferior do display; o modelo GLB permanece completo para edição.

Movimento atual: inclinação dos anéis fixa em 0,34 rad. A rotação acontece no eixo local inclinado, com a mesma velocidade angular do globo (0,042 rad/s). Sem oscilação, precessão, deslocamento ou mudança de escala. A normal do plano orbital é validada como constante em 721 amostras; outro teste confirma que os pontos orbitais realmente percorrem o plano.

Atualização de orientação: 75° em Y, abrindo a visualização dos anéis. Essa orientação substitui a anterior de 90°, sem alterar o eixo vertical da esfera nem o plano fixo da rotação orbital.

Acabamento atual: materiais não metálicos (metalness=0), totalmente rugosos (roughness=1), baixa emissão e iluminação suave. O núcleo mantém material sem reflexos. O GLB foi regenerado com esses materiais.
