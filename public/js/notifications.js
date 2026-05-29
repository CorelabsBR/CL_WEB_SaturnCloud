(() => {
    const alerts = document.querySelectorAll(
        '[data-auto-dismiss], .flash-success, .admin-alert-success, .register-alert-success, .auth-success-box, .checkout-alert-success'
    );

    alerts.forEach((alert) => {
        window.setTimeout(() => {
            alert.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';

            window.setTimeout(() => {
                alert.remove();
            }, 380);
        }, 4500);
    });
})();
