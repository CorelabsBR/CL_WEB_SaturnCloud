const reducedMotion = matchMedia("(prefers-reduced-motion: reduce)");
const header = document.querySelector(".site-header");
let headerTick = false;
window.addEventListener(
    "scroll",
    () => {
        if (headerTick || !header) return;
        headerTick = true;
        requestAnimationFrame(() => {
            header.classList.toggle("is-scrolled", scrollY > 32);
            headerTick = false;
        });
    },
    { passive: true },
);

// Native compositor animations keep the initial bundle independent of animation libraries.
if (!reducedMotion.matches) {
    document
        .querySelectorAll(".cosmic-heading h1 span")
        .forEach((line, index) => {
            line.animate(
                [
                    { transform: "translateY(18px)" },
                    { transform: "translateY(0)" },
                ],
                {
                    duration: 800,
                    delay: index * 90,
                    easing: "cubic-bezier(.2,.8,.2,1)",
                },
            );
        });
    const reveals = new IntersectionObserver(
        (entries) => {
            for (const entry of entries)
                if (entry.isIntersecting) {
                    if (!reducedMotion.matches)
                        entry.target.animate(
                            [
                                {
                                    clipPath: "inset(0 0 100% 0)",
                                    transform: "translateY(18px)",
                                },
                                {
                                    clipPath: "inset(0 0 0% 0)",
                                    transform: "translateY(0)",
                                },
                            ],
                            {
                                duration: 900,
                                easing: "cubic-bezier(.2,.8,.2,1)",
                            },
                        );
                    reveals.unobserve(entry.target);
                }
        },
        { threshold: 0.18 },
    );
    document
        .querySelectorAll("[data-reveal]")
        .forEach((el) => reveals.observe(el));
}

// Load the visible planet on every device; reduced motion only pauses its spin.
document.querySelectorAll("[data-saturn-scene]").forEach((host) => {
    const button = host.nextElementSibling;
    let loading = false;
    const load = async () => {
        if (loading || host.dataset.renderMode === "webgl") return;
        loading = true;
        button.disabled = true;
        button.textContent = "Preparando modelo…";
        button.setAttribute("aria-busy", "true");
        try {
            const { mountSaturn } = await import("./saturn-scene.js");
            mountSaturn(host, reducedMotion);
            if (host.dataset.renderMode !== "webgl") {
                throw new Error("Não foi possível iniciar o WebGL.");
            }
            button.removeEventListener("click", load);
        } catch (error) {
            host.dataset.renderMode = "static";
            button.hidden = false;
            button.textContent = "Tentar carregar modelo 3D";
            console.warn("Falha ao carregar Saturno:", error);
        } finally {
            loading = false;
            button.disabled = false;
            button.removeAttribute("aria-busy");
        }
    };
    const observer = new IntersectionObserver((entries) => {
        if (!entries.some((entry) => entry.isIntersecting)) return;
        observer.disconnect();
        load();
    });
    observer.observe(host);
    button.addEventListener("click", load);
});

// Mostra o progresso ao adicionar um plano ao carrinho.
document
    .querySelectorAll(
        ".vtx-plan-card form, .vps-plan form, .plan-checkout-form",
    )
    .forEach((form) => {
        form.addEventListener("submit", () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button || !form.checkValidity()) return;
            button.dataset.originalLabel = button.textContent;
            button.setAttribute("aria-busy", "true");
            button.textContent = "Abrindo carrinho…";
            button.disabled = true;
        });
    });
window.addEventListener("pageshow", () => {
    // Restore feedback on back navigation without discarding the browser's page cache.
    document
        .querySelectorAll("button[data-original-label]")
        .forEach((button) => {
            button.textContent = button.dataset.originalLabel;
            button.disabled = false;
            button.removeAttribute("aria-busy");
        });
});
