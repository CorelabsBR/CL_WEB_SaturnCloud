# Saturno — atmosfera e anéis de partículas

Modelo procedural original em 3D, substituindo a antiga representação em grade do símbolo. A esfera achatada tem faixas atmosféricas calculadas por vértice. O material é não metálico, com rugosidade máxima e intensidade especular zero.

Os anéis não são tubos ou planos texturizados. Há 48.000 partículas em posições 3D, com espessura, densidades radiais, divisão de Cassini e outra separação exterior. Mais 1.200 fragmentos minerais são instâncias de geometria sólida. No mobile são 14.000 partículas e 360 fragmentos, com geometria da atmosfera reduzida. O campo gira dentro de um plano com inclinação fixa, sem oscilação. A esfera permanece com o eixo vertical.

O GLB preserva as partículas como primitivas POINTS e os fragmentos via EXT_mesh_gpu_instancing; visualizadores glTF podem representar os pontos com tamanhos diferentes. O site gera a mesma geometria localmente e não baixa o GLB adicional. Nenhuma textura ou imagem é usada para construir o planeta.

Regenerar e validar: `npm run model:saturn`. Prévia interativa: `/modelo-saturno`. Metadados: `saturn-brand.json`. A validação cobre 721 poses do eixo e do plano orbital, o espaço vazio da divisão dos anéis e a reimportação de todas as partículas e instâncias do GLB.
