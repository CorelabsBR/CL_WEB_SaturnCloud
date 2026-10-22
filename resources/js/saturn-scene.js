import { createSaturnBrandViewer } from "./saturn-brand-viewer.js";
import { applySaturnBrandPose } from "./saturn-brand-motion.js";

export function mountSaturn(host, reducedMotion) {
    let viewer;
    try {
        viewer = createSaturnBrandViewer(
            host.querySelector("[data-saturn-canvas]"),
            {
                lowPower:
                    matchMedia("(max-width: 760px)").matches ||
                    (navigator.hardwareConcurrency || 4) <= 4,
            },
        );
    } catch {
        host.dataset.renderMode = "static";
        return;
    }
    const button = host.nextElementSibling;
    let visible = true,
        paused = false,
        disposed = false,
        frame = 0,
        timer = 0,
        last = 0,
        time = 0,
        progress = 0;
    const draw = () => {
        applySaturnBrandPose(viewer.model, time, progress);
        viewer.render();
    };
    const start = () => {
        if (
            disposed ||
            frame ||
            timer ||
            !visible ||
            paused ||
            reducedMotion.matches ||
            document.hidden
        )
            return;
        frame = requestAnimationFrame(tick);
    };
    const tick = (now) => {
        frame = 0;
        if (
            disposed ||
            !visible ||
            paused ||
            reducedMotion.matches ||
            document.hidden
        )
            return;
        time += last ? Math.min((now - last) / 1000, 0.1) : 0;
        last = now;
        draw();
        timer = setTimeout(() => {
            timer = 0;
            start();
        }, 1000 / 24);
    };
    const sync = () => {
        button.hidden = reducedMotion.matches;
        button.setAttribute("aria-pressed", String(paused));
        button.innerHTML = paused ? "▷ Retomar animação" : "Ⅱ Pausar animação";
        host.dataset.motion = reducedMotion.matches
            ? "reduced"
            : paused
              ? "paused"
              : visible && !document.hidden
                ? "running"
                : "offscreen";
        if (reducedMotion.matches) {
            time = 0;
            progress = 0;
            draw();
        }
        start();
    };
    const toggle = () => {
        paused = !paused;
        sync();
    };
    const scroll = () => {
        if (!visible || paused || reducedMotion.matches) return;
        const rect = host.parentElement.getBoundingClientRect();
        progress = Math.max(
            0,
            Math.min(1, -rect.top / Math.max(rect.height, innerHeight)),
        );
    };
    const visibility = new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        sync();
    });
    visibility.observe(host);
    const resize = new ResizeObserver(() => {
        viewer.resize();
        draw();
    });
    resize.observe(host);
    const dispose = () => {
        if (disposed) return;
        disposed = true;
        clearTimeout(timer);
        cancelAnimationFrame(frame);
        visibility.disconnect();
        resize.disconnect();
        window.removeEventListener("scroll", scroll);
        window.removeEventListener("pageshow", sync);
        document.removeEventListener("visibilitychange", sync);
        reducedMotion.removeEventListener("change", sync);
        button.removeEventListener("click", toggle);
        host.classList.remove("is-rendered");
        host.dataset.renderMode = "static";
        button.hidden = true;
        viewer.dispose();
    };
    viewer.renderer.domElement.addEventListener(
        "webglcontextlost",
        (event) => {
            event.preventDefault();
            dispose();
        },
        { once: true },
    );
    window.addEventListener("pagehide", (event) => {
        if (!event.persisted) dispose();
    });
    window.addEventListener("scroll", scroll, { passive: true });
    window.addEventListener("pageshow", sync);
    document.addEventListener("visibilitychange", sync);
    reducedMotion.addEventListener("change", sync);
    button.addEventListener("click", toggle);
    viewer.resize();
    draw();
    sync();
    host.classList.add("is-rendered");
    host.dataset.renderMode = "webgl";
}
