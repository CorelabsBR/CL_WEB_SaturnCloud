<div class="admin-form-section admin-form-section-main">
    <div class="admin-form-section-head">
        <strong>Dados do produto</strong>
        <span>Nome, preço, imagem e descrição exibidos na loja.</span>
    </div>

    <div class="admin-form-grid">
        <label>
            Nome
            <input type="text" name="nome" value="{{ old('nome', $product?->nome) }}" placeholder="Ex: Tier Diamante" required>
        </label>

        <label>
            Preço
            <input type="number" name="preco" min="0" step="0.01" value="{{ old('preco', $product?->preco) }}" placeholder="49.90" required>
        </label>

        <label>
            Imagem
            <input type="file" name="imagem" accept="image/*">
        </label>

        <label class="admin-form-full">
            Descrição
            <textarea name="descricao" rows="3" placeholder="Resumo curto dos recursos e benefícios do plano.">{{ old('descricao', $product?->descricao) }}</textarea>
        </label>
    </div>
</div>

<div class="admin-form-section">
    <div class="admin-form-section-head">
        <strong>Organização</strong>
        <span>Defina onde o produto aparece e a ordem dentro da pasta.</span>
    </div>

    <div class="admin-form-grid">
        <label>
            Categoria
            <select name="categoria_id" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('categoria_id', $product?->categoria_id) === $category->id)>
                        {{ $category->nome }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            Pasta do jogo
            <select name="jogo_id">
                <option value="">Sem jogo vinculado</option>
                @foreach($games as $game)
                    <option value="{{ $game->id }}" @selected((int) old('jogo_id', $product?->jogo_id) === $game->id)>
                        {{ $game->nome }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            Ordem na pasta
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $product?->sort_order ?? 1) }}" placeholder="1">
        </label>

        <label>
            ID do plano
            <input type="number" name="plan" min="1" value="{{ old('plan', $product?->plan) }}" placeholder="ID no painel">
        </label>
    </div>
</div>

<div class="admin-form-section">
    <div class="admin-form-section-head">
        <strong>Recursos do plano</strong>
        <span>Campos opcionais para destacar especificações técnicas.</span>
    </div>

    <div class="admin-form-grid">
        <label>
            RAM
            <input type="text" name="ram" value="{{ old('ram', $product?->ram) }}" placeholder="Ex: 8 GB">
        </label>

        <label>
            Armazenamento
            <input type="text" name="armazenamento" value="{{ old('armazenamento', $product?->armazenamento) }}" placeholder="Ex: 80 GB NVMe">
        </label>

        <label>
            Processamento
            <input type="text" name="processamento" value="{{ old('processamento', $product?->processamento) }}" placeholder="Ex: CPU dedicada">
        </label>

        <label>
            Bancos de dados
            <input type="text" name="bancos_dados" value="{{ old('bancos_dados', $product?->bancos_dados) }}" placeholder="Ex: 3">
        </label>

        <label>
            Domínios
            <input type="text" name="dominios" value="{{ old('dominios', $product?->dominios) }}" placeholder="Ex: 2">
        </label>

        <div class="admin-form-switches">
            <label class="form-check-row">
                <input type="checkbox" name="featured" value="1" @checked(old('featured', $product?->featured))>
                Produto em destaque
            </label>

            <label class="form-check-row">
                <input type="checkbox" name="ddos_protection" value="1" @checked(old('ddos_protection', $product?->ddos_protection))>
                Proteção DDoS
            </label>
        </div>
    </div>
</div>
