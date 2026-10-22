<div class="saturn-scene {{ $sceneClass ?? '' }}" data-saturn-scene data-render-mode="static" role="img" aria-label="Símbolo Saturn Cloud em 3D: globo em grade, órbitas tubulares e satélites">
    <div class="saturn-display-viewport">
    <img class="saturn-artwork" src="{{ asset('img/saturno_branco_quadrado.svg') }}" alt="" width="1536" height="1536" decoding="async" @if($priority ?? false) fetchpriority="high" @else loading="lazy" @endif>
    <div class="saturn-canvas" data-saturn-canvas aria-hidden="true"></div>
    </div>
</div>
<button class="scene-motion" data-scene-motion type="button" aria-pressed="false" hidden>▷ Ativar modelo 3D</button>
