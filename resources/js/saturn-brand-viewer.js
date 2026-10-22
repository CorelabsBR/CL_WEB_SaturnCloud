import * as THREE from "three";
import { createSaturnBrandModel } from "./saturn-brand-model.js";

export function createSaturnBrandViewer(
    container,
    { lowPower = false, preview = false } = {},
) {
    const renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: !lowPower,
        powerPreference: "low-power",
    });
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.setClearColor(0x050505, 0);
    container.append(renderer.domElement);
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(preview ? 34 : 16, 1, 0.1, 50);
    camera.position.set(0, 0, preview ? 7.8 : 3.9);
    camera.lookAt(0, 0, 0);
    const model = createSaturnBrandModel({ lowPower });
    model.scale.setScalar(preview ? 1 : 0.6336);
    scene.add(model);
    scene.add(new THREE.AmbientLight(0xd7c9ff, 1));
    const key = new THREE.DirectionalLight(0xffffff, 1.8);
    key.position.set(-3, 5, 5);
    scene.add(key);
    const rim = new THREE.DirectionalLight(0x8c46ff, 1);
    rim.position.set(4, 1, -3);
    scene.add(rim);
    const fill = new THREE.DirectionalLight(0xe0d0ff, 0.5);
    fill.position.set(2, -2, 4);
    scene.add(fill);
    const render = () => renderer.render(scene, camera);
    const resize = () => {
        const { width, height } = container.getBoundingClientRect();
        if (!width || !height) return;
        renderer.setPixelRatio(
            Math.min(
                devicePixelRatio,
                lowPower ? 1 : 1.3,
                Math.sqrt(1300000 / (width * height)),
            ),
        );
        renderer.setSize(width, height);
        camera.aspect = width / height;
        // A quarter of the globe peeks through the middle of the display's right edge.
        // Shift the projection instead of pitching the camera, preserving the equatorial view.
        if (!preview) {
            const radius = 1.11 * 0.6336;
            camera.fov = 16;
            let focalPixels =
                height / (2 * Math.tan((camera.fov * Math.PI) / 360));
            const distanceToSilhouette = Math.sqrt(
                camera.position.z ** 2 - radius ** 2,
            );
            let projectedRadius = (focalPixels * radius) / distanceToSilhouette;
            // A portrait viewport also needs enough area to retain the requested quarter.
            const radiusLimit = 0.72 * Math.sqrt((width * height) / (Math.PI * 0.32));
            if (projectedRadius > radiusLimit) {
                projectedRadius = radiusLimit;
                focalPixels = (projectedRadius * distanceToSilhouette) / radius;
                camera.fov =
                    (2 * Math.atan(height / (2 * focalPixels)) * 180) / Math.PI;
            }
            // Keep approximately a quarter visible even when the close-up also crops top/bottom.
            const cropOffset = getSaturnDisplayCrop(
                projectedRadius,
                width,
                height,
            );
            camera.setViewOffset(
                width,
                height,
                -width / 2 - cropOffset + width * 0.1,
                -height * 0.05,
                width,
                height,
            );
        }
        camera.updateProjectionMatrix();
        render();
    };
    const dispose = () => {
        const materials = new Set();
        model.traverse((object) => {
            object.geometry?.dispose();
            if (object.material) materials.add(object.material);
        });
        materials.forEach((material) => material.dispose());
        renderer.dispose();
        renderer.domElement.remove();
    };
    return { renderer, scene, camera, model, render, resize, dispose };
}

// Exact circular-cap area inside the display rectangle, used only on resize.
export function getSaturnDisplayCrop(radius, width, height) {
    const capArea = (offset) => {
        if (offset >= radius) return 0;
        const y = Math.min(
            height / 2,
            Math.sqrt(radius * radius - offset * offset),
        );
        return (
            y * Math.sqrt(radius * radius - y * y) +
            radius * radius * Math.asin(y / radius) -
            2 * offset * y
        );
    };
    const visibleArea = (offset) => capArea(offset) - capArea(offset + width);
    const target = Math.min(Math.PI * radius * radius * 0.25, visibleArea(0));
    let low = 0,
        high = radius;
    for (let step = 0; step < 32; step++) {
        const middle = (low + high) / 2;
        if (visibleArea(middle) > target) low = middle;
        else high = middle;
    }
    return (low + high) / 2;
}
