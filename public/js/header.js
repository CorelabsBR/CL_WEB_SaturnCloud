(() => {
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobile-nav');
    if (!hamburger || !mobileNav || hamburger.dataset.headerBound) return;
    hamburger.dataset.headerBound = '1';

    const closeMobileMenu = (returnFocus = false) => {
        mobileNav.classList.remove('open');
        mobileNav.inert = true;
        hamburger.classList.remove('is-open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.setAttribute('aria-label', 'Abrir menu');
        if (returnFocus) hamburger.focus();
    };

    hamburger.addEventListener('click', () => {
        const opening = !mobileNav.classList.contains('open');
        mobileNav.classList.toggle('open', opening);
        mobileNav.inert = !opening;
        hamburger.classList.toggle('is-open', opening);
        hamburger.setAttribute('aria-expanded', String(opening));
        hamburger.setAttribute('aria-label', opening ? 'Fechar menu' : 'Abrir menu');
    });
    window.addEventListener('resize', () => { if (innerWidth > 1050) closeMobileMenu(); });
    mobileNav.addEventListener('keydown', event => {
        if (event.key !== 'Tab') return;
        const items = [...mobileNav.querySelectorAll('a, button')];
        if (!event.shiftKey && document.activeElement === items.at(-1)) { event.preventDefault(); hamburger.focus(); }
    });
    mobileNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMobileMenu()));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileNav.classList.contains('open')) closeMobileMenu(true);
    });
})();
