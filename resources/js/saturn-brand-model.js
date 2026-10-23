import * as THREE from "three";
import { refineSaturnAtmosphere } from "./saturn-atmosphere.js";

// Smooth spatial noise keeps cloud detail continuous across the longitude seam.
function cloudNoise(x, y, z) {
    const ix = Math.floor(x), iy = Math.floor(y), iz = Math.floor(z);
    const smooth = (v) => v * v * (3 - 2 * v);
    const tx = smooth(x - ix), ty = smooth(y - iy), tz = smooth(z - iz);
    const hash = (a, b, c) => {
        const n = Math.sin(a * 127.1 + b * 311.7 + c * 74.7) * 43758.5453;
        return n - Math.floor(n);
    };
    const row = (b, c) => THREE.MathUtils.lerp(hash(ix, b, c), hash(ix + 1, b, c), tx);
    return THREE.MathUtils.lerp(
        THREE.MathUtils.lerp(row(iy, iz), row(iy + 1, iz), ty),
        THREE.MathUtils.lerp(row(iy, iz + 1), row(iy + 1, iz + 1), ty), tz,
    ) * 2 - 1;
}

// Saturn-like atmospheric bands in violet, with a three-dimensional field of grains.
export function createSaturnBrandModel({ lowPower = false } = {}) {
    const root = new THREE.Group();
    root.name = "Saturn — atmospheric globe and particulate rings";
    root.userData = {
        source: "Procedural Saturn, original geometry",
        geometry:
            "Oblate banded sphere, 3D ring particles and instanced mineral grains",
        units: "meters",
    };
    let seed = 41721;
    const random = () => {
        seed = (seed * 1664525 + 1013904223) >>> 0;
        return seed / 4294967296;
    };
    const globe = new THREE.Group();
    globe.name = "Spherical network";
    root.add(globe);
    const surface = new THREE.SphereGeometry(
        1.065,
        lowPower ? 96 : 192,
        lowPower ? 192 : 384,
    );
    const positions = surface.attributes.position;
    const colors = new Float32Array(positions.count * 3);
    const atmosphere = new THREE.Color();
    const dark = new THREE.Color('#452960');
    const middle = new THREE.Color('#9468b1');
    const light = new THREE.Color('#e3c9ec');
    for (let index = 0; index < positions.count; index++) {
        const latitude = positions.getY(index) / 1.065;
        const longitude = Math.atan2(
            positions.getZ(index),
            positions.getX(index),
        );
        // Latitude belts dominate Saturn's atmosphere. Periodic longitude terms
        // add wind-sheared cloud detail without a seam around the globe.
        const latitudeAngle = Math.asin(THREE.MathUtils.clamp(latitude, -1, 1));
        const x = Math.cos(longitude), z = Math.sin(longitude);
        const weather = cloudNoise(x * 3, latitudeAngle * 11, z * 3);
        const wisps = cloudNoise(x * 12, latitudeAngle * 65 + weather, z * 12);
        const filaments = cloudNoise(x * 27, latitudeAngle * 140, z * 27);
        const turbulence = weather * 0.021 + wisps * 0.004;
        const beltLatitude = latitudeAngle + turbulence;
        const broadBelts =
            Math.sin(beltLatitude * 15 + Math.sin(beltLatitude * 8) * 0.9) * 0.44 +
            Math.sin(beltLatitude * 34 + 1.2) * 0.2 +
            cloudNoise(0, beltLatitude * 24, 0) * 0.36;
        const fineClouds =
            Math.sin(beltLatitude * 97 + weather * 1.3) * 0.035 +
            Math.sin(beltLatitude * 183 + wisps) * 0.018 +
            wisps * 0.065 + filaments * 0.025;
        const equatorialHaze = Math.exp(-((latitudeAngle / 0.19) ** 2)) * 0.16;
        const polarHaze = THREE.MathUtils.smoothstep(Math.abs(latitude), 0.7, 1) * 0.22;
        const shade = THREE.MathUtils.clamp(
            0.48 + broadBelts * 0.36 + fineClouds + equatorialHaze - polarHaze,
            0,
            1,
        );
        // Deep plum belts, amethyst midtones, and pale lavender cloud tops.
        if (shade < 0.5) atmosphere.copy(dark).lerp(middle, shade * 2);
        else atmosphere.copy(middle).lerp(light, (shade - 0.5) * 2);
        colors[index * 3] = atmosphere.r;
        colors[index * 3 + 1] = atmosphere.g;
        colors[index * 3 + 2] = atmosphere.b;
    }
    surface.setAttribute("color", new THREE.BufferAttribute(colors, 3));
    surface.deleteAttribute("uv");
    surface.scale(1, 0.9, 1);
    const coreMaterial = new THREE.MeshPhysicalMaterial({
        name: "Purple Saturn cloud belts — matte atmosphere",
        vertexColors: true,
        metalness: 0,
        roughness: 1,
        specularIntensity: 0,
    });
    refineSaturnAtmosphere(coreMaterial, { lowPower });
    const core = new THREE.Mesh(surface, coreMaterial);
    core.name = "Solid spherical core";
    globe.add(core);

    const orbits = new THREE.Group();
    orbits.name = "Orbital framework";
    orbits.rotation.set(0.34, 0, 0.35);
    orbits.scale.setScalar(1.15);
    root.add(orbits);
    const field = new THREE.Group();
    field.name = "Particle ring field";
    orbits.add(field);
    const inner = 1.22,
        outer = 2.28;
    const sampleGrain = () => {
        let radius;
        for (;;) {
            radius = Math.sqrt(
                inner * inner + random() * (outer * outer - inner * inner),
            );
            // Cassini division and a fine outer gap are actual empty spaces in the particle field.
            if (
                (radius > 1.88 && radius < 1.96) ||
                (radius > 2.22 && radius < 2.245)
            )
                continue;
            const density = radius < 1.38 ? 0.3 : radius < 1.88 ? 0.95 : 0.72;
            if (random() <= density) break;
        }
        const angle = random() * Math.PI * 2;
        return {
            radius,
            x: Math.cos(angle) * radius,
            y: (random() - 0.5) * 0.012,
            z: Math.sin(angle) * radius,
        };
    };
    const count = lowPower ? 22000 : 76000;
    const grains = new Float32Array(count * 3);
    const grainColors = new Float32Array(count * 3);
    for (let index = 0; index < count; index++) {
        const grain = sampleGrain();
        grains.set([grain.x, grain.y, grain.z], index * 3);
        const shade =
            0.15 + random() * 0.16 + Math.sin(grain.radius * 220) * 0.025;
        grainColors.set([shade * 1.05, shade * 0.96, shade * 1.1], index * 3);
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute("position", new THREE.BufferAttribute(grains, 3));
    geometry.setAttribute("color", new THREE.BufferAttribute(grainColors, 3));
    const material = new THREE.PointsMaterial({
        name: "Mineral ring grains",
        vertexColors: true,
        size: lowPower ? 0.012 : 0.0105,
        sizeAttenuation: true,
        depthWrite: false,
    });
    // Round grains without downloading a sprite or introducing a textured plane.
    material.onBeforeCompile = (shader) => {
        shader.fragmentShader = shader.fragmentShader.replace(
            "#include <clipping_planes_fragment>",
            "#include <clipping_planes_fragment>\nif (dot(gl_PointCoord - vec2(0.5), gl_PointCoord - vec2(0.5)) > 0.25) discard;",
        );
    };
    material.customProgramCacheKey = () => "round-mineral-grains-v1";
    const points = new THREE.Points(geometry, material);
    points.name = "Saturn ring particles";
    points.renderOrder = 1;
    field.add(points);

    // A second, instanced layer gives the nearby grains genuine solid volume in close-up.
    const rockCount = lowPower ? 540 : 1800;
    const rockGeometry = new THREE.IcosahedronGeometry(1, 0);
    rockGeometry.deleteAttribute("uv");
    const rockMaterial = new THREE.MeshStandardMaterial({
        name: "Matte mineral fragments",
        color: 0x8f859c,
        metalness: 0,
        roughness: 1,
    });
    const rocks = new THREE.InstancedMesh(
        rockGeometry,
        rockMaterial,
        rockCount,
    );
    rocks.name = "Volumetric ring fragments";
    const transform = new THREE.Object3D();
    const color = new THREE.Color();
    for (let index = 0; index < rockCount; index++) {
        const grain = sampleGrain();
        const size = 0.004 + random() * 0.0055;
        transform.position.set(grain.x, grain.y, grain.z);
        transform.rotation.set(
            random() * Math.PI,
            random() * Math.PI,
            random() * Math.PI,
        );
        transform.scale.set(
            size,
            size * (0.5 + random() * 0.5),
            size * (0.7 + random() * 0.6),
        );
        transform.updateMatrix();
        rocks.setMatrixAt(index, transform.matrix);
        color.setScalar(0.62 + random() * 0.38);
        rocks.setColorAt(index, color);
    }
    rocks.instanceMatrix.needsUpdate = true;
    rocks.computeBoundingSphere();
    field.add(rocks);
    root.rotation.set(0, (75 * Math.PI) / 180, 0);
    root.userData.particles = count;
    root.userData.rockInstances = rockCount;
    return root;
}
