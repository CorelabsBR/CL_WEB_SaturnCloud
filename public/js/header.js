(() => {
    const closeProfileMenu = (dropdown, returnFocus = false) => {
        const button = dropdown.querySelector('.profile-btn');
        const menu = dropdown.querySelector('.profile-menu');
        if (!button || !menu) return;
        menu.hidden = true;
        menu.classList.remove('is-open');
        menu.style.removeProperty('top');
        menu.style.removeProperty('right');
        button.setAttribute('aria-expanded', 'false');
        if (returnFocus) button.focus();
    };

    const positionProfileMenu = (button, menu) => {
        const rect = button.getBoundingClientRect();
        const gutter = 12;
        const right = Math.max(gutter, window.innerWidth - rect.right);
        const preferredTop = rect.bottom + 10;
        const availableBelow = window.innerHeight - preferredTop - gutter;
        const top = availableBelow >= menu.offsetHeight
            ? preferredTop
            : Math.max(gutter, rect.top - menu.offsetHeight - 10);
        menu.style.top = `${Math.round(top)}px`;
        menu.style.right = `${Math.round(right)}px`;
    };

    document.querySelectorAll('.profile-dropdown').forEach((dropdown) => {
        const button = dropdown.querySelector('.profile-btn');
        const menu = dropdown.querySelector('.profile-menu');
        if (!button || !menu || button.dataset.headerBound) return;
        button.dataset.headerBound = '1';

        const openMenu = () => {
            document.querySelectorAll('.profile-dropdown').forEach((other) => {
                if (other !== dropdown) closeProfileMenu(other);
            });
            menu.hidden = false;
            menu.classList.add('is-open');
            button.setAttribute('aria-expanded', 'true');
            positionProfileMenu(button, menu);
        };

        button.addEventListener('click', (event) => {
            event.stopPropagation();
            menu.hidden ? openMenu() : closeProfileMenu(dropdown);
        });

        dropdown.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !menu.hidden) {
                event.preventDefault();
                closeProfileMenu(dropdown, true);
            } else if (event.key === 'ArrowDown' && document.activeElement === button) {
                event.preventDefault();
                if (menu.hidden) openMenu();
                menu.querySelector('[role="menuitem"]')?.focus();
            }
        });

        menu.addEventListener('keydown', (event) => {
            if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
            event.preventDefault();
            const items = [...menu.querySelectorAll('[role="menuitem"]')];
            const current = items.indexOf(document.activeElement);
            const direction = event.key === 'ArrowDown' ? 1 : -1;
            items[(current + direction + items.length) % items.length]?.focus();
        });
    });

    document.addEventListener('click', (event) => {
        document.querySelectorAll('.profile-dropdown').forEach((dropdown) => {
            if (!dropdown.contains(event.target)) closeProfileMenu(dropdown);
        });
    });
    window.addEventListener('resize', () => document.querySelectorAll('.profile-dropdown').forEach((dropdown) => closeProfileMenu(dropdown)));
    window.addEventListener('scroll', () => document.querySelectorAll('.profile-dropdown').forEach((dropdown) => closeProfileMenu(dropdown)), { passive: true });

    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobile-nav');
    if (!hamburger || !mobileNav || hamburger.dataset.headerBound) return;
    hamburger.dataset.headerBound = '1';

    const closeMobileMenu = (returnFocus = false) => {
        mobileNav.classList.remove('open');
        hamburger.classList.remove('is-open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.setAttribute('aria-label', 'Abrir menu');
        if (returnFocus) hamburger.focus();
    };

    hamburger.addEventListener('click', () => {
        const opening = !mobileNav.classList.contains('open');
        mobileNav.classList.toggle('open', opening);
        hamburger.classList.toggle('is-open', opening);
        hamburger.setAttribute('aria-expanded', String(opening));
        hamburger.setAttribute('aria-label', opening ? 'Fechar menu' : 'Abrir menu');
    });
    mobileNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMobileMenu()));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileNav.classList.contains('open')) closeMobileMenu(true);
    });
})();
