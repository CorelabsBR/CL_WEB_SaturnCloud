// Profile dropdown functionality
const profileBtn = document.getElementById('profile-btn');
const profileMenu = document.getElementById('profile-menu');

if (profileBtn && profileMenu) {
    // Toggle menu on button click
    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.classList.toggle('active');
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.remove('active');
        }
    });

    // Close menu when clicking a menu item (except form submit)
    const menuItems = profileMenu.querySelectorAll('a');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            profileMenu.classList.remove('active');
        });
    });
}

// Mobile hamburger menu
const hamburger = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobile-nav');

if (hamburger && mobileNav) {
    hamburger.addEventListener('click', () => {
        mobileNav.classList.toggle('active');
    });

    // Close mobile menu when clicking a link
    const mobileLinks = mobileNav.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileNav.classList.remove('active');
        });
    });
}
