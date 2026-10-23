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

// The brand model loads after the page is ready. Mobile stays opt-in.
const manualModel =
    matchMedia("(max-width: 760px)").matches ||
    (navigator.hardwareConcurrency || 4) <= 4 ||
    navigator.connection?.saveData ||
    (navigator.deviceMemory !== undefined && navigator.deviceMemory < 4) ||
    ["slow-2g", "2g", "3g"].includes(navigator.connection?.effectiveType) ||
    reducedMotion.matches;
document.querySelectorAll("[data-saturn-scene]").forEach((host) => {
    const button = host.nextElementSibling;
    button.hidden = reducedMotion.matches;
    reducedMotion.addEventListener("change", () => {
        if (host.dataset.renderMode !== "webgl")
            button.hidden = reducedMotion.matches;
    });
    let loading = false,
        scheduled = false;
    const load = async () => {
        if (
            loading ||
            reducedMotion.matches ||
            host.dataset.renderMode === "webgl"
        )
            return;
        loading = true;
        button.disabled = true;
        button.textContent = "Preparando modelo…";
        button.setAttribute("aria-busy", "true");
        try {
            const { mountSaturn } = await import("./saturn-scene.js");
            if (!reducedMotion.matches) mountSaturn(host, reducedMotion);
        } catch {
            host.dataset.renderMode = "static";
        } finally {
            loading = false;
            button.disabled = false;
            button.removeAttribute("aria-busy");
            button.removeEventListener("click", load);
            if (host.dataset.renderMode !== "webgl") button.hidden = true;
        }
    };
    const observer = new IntersectionObserver(([entry]) => {
        if (!entry.isIntersecting || manualModel || scheduled) return;
        scheduled = true;
        const schedule = () =>
            setTimeout(() => {
                const whenIdle = () => {
                    const rect = host.getBoundingClientRect();
                    if (
                        !document.hidden &&
                        rect.bottom > 0 &&
                        rect.top < innerHeight
                    ) {
                        observer.disconnect();
                        load();
                    } else scheduled = false;
                };
                if ("requestIdleCallback" in window)
                    requestIdleCallback(whenIdle, { timeout: 2500 });
                else setTimeout(whenIdle, 200);
            }, 1200);
        if (document.readyState === "complete") schedule();
        else window.addEventListener("load", schedule, { once: true });
    });
    observer.observe(host);
    button.addEventListener("click", load);
});

// Keep native validation and existing commercial POST contracts.
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
