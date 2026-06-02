// Profile dropdown functionality
document.querySelectorAll('.profile-dropdown').forEach((dropdown) => {
    const profileBtn = dropdown.querySelector('.profile-btn');
    const profileMenu = dropdown.querySelector('.profile-menu');

    if (!profileBtn || !profileMenu || profileBtn.dataset.headerBound) {
        return;
    }

    profileBtn.dataset.headerBound = '1';

    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        document.querySelectorAll('.profile-menu.active').forEach((menu) => {
            if (menu !== profileMenu) {
                menu.classList.remove('active');
            }
        });
        profileMenu.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) {
            profileMenu.classList.remove('active');
        }
    });

    profileMenu.querySelectorAll('a').forEach(item => {
        item.addEventListener('click', () => {
            profileMenu.classList.remove('active');
        });
    });
});

// Mobile hamburger menu
const hamburger = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobile-nav');

if (hamburger && mobileNav && !hamburger.dataset.headerBound) {
    hamburger.dataset.headerBound = '1';

    hamburger.addEventListener('click', () => {
        mobileNav.classList.toggle('open');
        hamburger.classList.toggle('is-open');
    });

    // Close mobile menu when clicking a link
    const mobileLinks = mobileNav.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileNav.classList.remove('open');
            hamburger.classList.remove('is-open');
        });
    });
}
