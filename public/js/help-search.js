(() => {
    const input = document.querySelector('[data-help-search]');
    if (!input) return;
    const articles = [...document.querySelectorAll('[data-help-article]')];
    const categories = [...document.querySelectorAll('[data-help-category]')];
    const status = document.querySelector('[data-help-status]');
    const empty = document.querySelector('[data-help-empty]');
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    input.addEventListener('input', () => {
        const term = normalize(input.value);
        let visible = 0;
        articles.forEach(article => {
            const match = !term || normalize(article.dataset.search || '').includes(term);
            article.hidden = !match;
            if (match) visible++;
        });
        categories.forEach(category => category.hidden = !category.querySelector('[data-help-article]:not([hidden])'));
        empty.hidden = visible !== 0;
        status.textContent = term ? `${visible} ${visible === 1 ? 'artigo encontrado' : 'artigos encontrados'}` : '';
    });
})();
