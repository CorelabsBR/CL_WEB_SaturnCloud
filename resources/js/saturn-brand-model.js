import * as THREE from "three";
import { mergeGeometries } from "three/addons/utils/BufferGeometryUtils.js";

// Reconstructs the globe grid, concentric orbits and satellites from the brand SVG
// as closed, volumetric surfaces. No image, plane, billboard or texture is used.
export function createSaturnBrandModel({ lowPower = false } = {}) {
    const root = new THREE.Group();
    root.name = "Saturn Cloud — orbital emblem";
    root.userData = {
        source: "saturno_branco_quadrado.svg",
        geometry:
            "Volumetric sphere, tubular meridians, latitudes, toroidal orbits and spherical nodes",
        units: "meters",
    };
    const segments = lowPower ? 64 : 112;
    const tubeSides = lowPower ? 5 : 8;
    const purple = new THREE.MeshStandardMaterial({
        name: "Matte Saturn violet",
        color: 0x6627c7,
        metalness: 0,
        roughness: 1,
        emissive: 0x4200a8,
        emissiveIntensity: 0.04,
    });
    // The inner ball stays uniformly matte: lighting cannot add a specular highlight.
    const coreMaterial = new THREE.MeshBasicMaterial({
        name: "Matte unlit globe",
        color: 0x09070e,
    });
    const addMesh = (geometry, material, name, parent = root) => {
        geometry.deleteAttribute("uv");
        const mesh = new THREE.Mesh(geometry, material);
        mesh.name = name;
        parent.add(mesh);
        return mesh;
    };
    const merge = (parts, material, name, parent = root) => {
        const geometry = mergeGeometries(parts);
        parts.forEach((part) => part.dispose());
        return addMesh(geometry, material, name, parent);
    };
    const globe = new THREE.Group();
    globe.name = "Spherical network";
    globe.rotation.set(0, 0, 0);
    root.add(globe);
    addMesh(
        new THREE.SphereGeometry(1.065, lowPower ? 32 : 56, lowPower ? 24 : 40),
        coreMaterial,
        "Solid spherical core",
        globe,
    );
    const ribs = [];
    for (let index = 0; index < 6; index++) {
        const meridian = new THREE.TorusGeometry(
            1.11,
            0.014,
            tubeSides,
            segments,
        );
        meridian.rotateY((index * Math.PI) / 6);
        ribs.push(meridian);
    }
    for (const latitude of [-60, -30, 0, 30, 60]) {
        const angle = THREE.MathUtils.degToRad(latitude);
        const band = new THREE.TorusGeometry(
            1.11 * Math.cos(angle),
            0.014,
            tubeSides,
            segments,
        );
        band.rotateX(Math.PI / 2);
        band.translate(0, 1.11 * Math.sin(angle), 0);
        ribs.push(band);
    }
    merge(ribs, purple, "Tubular longitude and latitude grid", globe);
    const orbits = new THREE.Group();
    orbits.name = "Orbital framework";
    orbits.rotation.x = 0.34;
    orbits.scale.setScalar(1.35);
    root.add(orbits);
    const ringParts = [];
    for (const [radius, thickness] of [
        [1.88, 0.014],
        [2.13, 0.035],
        [2.25, 0.015],
    ]) {
        const ring = new THREE.TorusGeometry(
            radius,
            thickness,
            tubeSides,
            segments * 2,
        );
        ring.rotateX(Math.PI / 2);
        ringParts.push(ring);
    }
    merge(ringParts, purple, "Three solid toroidal orbits", orbits);
    const dottedOrbit = [];
    for (let index = 0; index < 64; index++) {
        const angle = (index / 64) * Math.PI * 2;
        const dot = new THREE.SphereGeometry(0.018, 6, 4);
        dot.translate(Math.cos(angle) * 2.52, 0, Math.sin(angle) * 2.52);
        dottedOrbit.push(dot);
    }
    merge(dottedOrbit, purple, "Outer orbit — 64 solid markers", orbits);
    const satellites = [];
    for (const angle of [0.25, 1.05, 2.8, 3.9, 5.3]) {
        const x = Math.cos(angle) * 2.13,
            z = Math.sin(angle) * 2.13;
        const collar = new THREE.TorusGeometry(0.088, 0.022, tubeSides, 24);
        collar.rotateX(Math.PI / 2);
        collar.translate(x, 0, z);
        satellites.push(collar);
        const body = new THREE.SphereGeometry(0.045, 12, 8);
        body.translate(x, 0, z);
        satellites.push(body);
    }
    merge(satellites, purple, "Five volumetric orbital satellites", orbits);
    root.rotation.set(0, (75 * Math.PI) / 180, 0);
    return root;
}
