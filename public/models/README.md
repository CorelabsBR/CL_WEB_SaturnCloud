# Saturno — atmosfera e anéis de partículas

Modelo procedural original em 3D, substituindo a antiga representação em grade do símbolo. A esfera achatada tem faixas atmosféricas inspiradas em Saturno, com cinturões em ameixa e ametista, nuvens lavanda e turbulência longitudinal e ruído espacial em três escalas para filamentos de nuvens, calculadas por vértice. O material é não metálico, com rugosidade máxima e intensidade especular zero.

Os anéis não são tubos ou planos texturizados. Há 76.000 partículas em posições 3D, com espessura, densidades radiais, divisão de Cassini e outra separação exterior. Mais 1.800 fragmentos minerais são instâncias de geometria sólida. No mobile são 22.000 partículas e 540 fragmentos, com geometria da atmosfera reduzida. O campo gira dentro de um plano com inclinação fixa, sem oscilação. A esfera permanece com o eixo vertical.

O GLB preserva as partículas como primitivas POINTS e os fragmentos via EXT_mesh_gpu_instancing; visualizadores glTF podem representar os pontos com tamanhos diferentes. O site gera a mesma geometria localmente e não baixa o GLB adicional. Nenhuma textura ou imagem é usada para construir o planeta.

Regenerar e validar: `npm run model:saturn`. Prévia interativa: `/modelo-saturno`. Metadados: `saturn-brand.json`. A validação cobre 721 poses do eixo e do plano orbital, o espaço vazio da divisão dos anéis e a reimportação de todas as partículas e instâncias do GLB.

Na versão WebGL, as nuvens recebem detalhe procedural por fragmento, com ruído em múltiplas escalas, faixas irregulares e cores roxas suaves. A iluminação lateral dá volume à atmosfera. O GLB mantém a versão por vértice; shaders personalizados do site não fazem parte do formato exportado.
