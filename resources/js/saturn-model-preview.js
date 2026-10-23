import { createSaturnBrandViewer } from "./saturn-brand-viewer.js";
import { applySaturnBrandPose } from "./saturn-brand-motion.js";
import { OrbitControls } from "three/addons/controls/OrbitControls.js";

const container = document.querySelector("[data-model-preview]");
const status = document.querySelector("[data-model-status]");
try {
    const viewer = createSaturnBrandViewer(container, { preview: true });
    const controls = new OrbitControls(
        viewer.camera,
        viewer.renderer.domElement,
    );
    controls.enableDamping = false;
    controls.minDistance = 3.8;
    controls.maxDistance = 13;
    controls.enablePan = false;
    controls.addEventListener("change", viewer.render);
    const resize = new ResizeObserver(viewer.resize);
    resize.observe(container);
    viewer.resize();
    const reducedMotion = matchMedia("(prefers-reduced-motion: reduce)");
    let frame = 0, last = 0, time = 0;
    const animate = (now) => {
        if (!document.hidden && !reducedMotion.matches) {
            time += last ? Math.min((now - last) / 1000, 0.1) : 0;
            applySaturnBrandPose(viewer.model, time);
            viewer.render();
        }
        last = now;
        frame = requestAnimationFrame(animate);
    };
    frame = requestAnimationFrame(animate);
    document.querySelectorAll("[data-model-angle]").forEach((button) =>
        button.addEventListener("click", () => {
            const positions = {
                perspective: [0, 0, 7.8],
                side: [7, 0, 0],
                back: [0, 0, -7],
                top: [0, 9, 0.01],
            };
            viewer.camera.position.set(...positions[button.dataset.modelAngle]);
            controls.target.set(0, 0, 0);
            controls.update();
            viewer.render();
            document
                .querySelectorAll("[data-model-angle]")
                .forEach((other) =>
                    other.setAttribute(
                        "aria-pressed",
                        String(other === button),
                    ),
                );
            status.textContent = `Vista: ${button.textContent}. Arraste para explorar o volume.`;
        }),
    );
    status.textContent =
        "Modelo carregado. Arraste para girar. Use a roda do mouse ou gesto de pinça para aproximar.";
    window.addEventListener(
        "pagehide",
        () => {
            cancelAnimationFrame(frame);
            resize.disconnect();
            controls.dispose();
            viewer.dispose();
        },
        { once: true },
    );
} catch {
    status.textContent =
        "A prévia precisa de WebGL. Você pode baixar o modelo GLB e abri-lo em um editor 3D.";
}
