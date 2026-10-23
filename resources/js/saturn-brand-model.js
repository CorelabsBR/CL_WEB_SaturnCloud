import * as THREE from "three";

// A physical globe and a three-dimensional field of grains. No image planes or textures.
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
        lowPower ? 64 : 128,
        lowPower ? 128 : 256,
    );
    const positions = surface.attributes.position;
    const colors = new Float32Array(positions.count * 3);
    for (let index = 0; index < positions.count; index++) {
        const latitude = positions.getY(index) / 1.065;
        const longitude = Math.atan2(
            positions.getZ(index),
            positions.getX(index),
        );
        const turbulence =
            Math.sin(longitude * 7 + latitude * 22) * 0.2 +
            Math.sin(longitude * 13 - latitude * 14) * 0.09;
        const band =
            Math.sin(latitude * 83 + turbulence) * 0.018 +
            Math.sin(latitude * 167 + turbulence) * 0.009 +
            Math.sin(latitude * 28) * 0.027;
        const polar = Math.abs(latitude) ** 5 * 0.05;
        colors[index * 3] = 0.29 + band - polar;
        colors[index * 3 + 1] = 0.235 + band * 0.83 - polar;
        colors[index * 3 + 2] = 0.205 + band * 0.71 - polar * 0.65;
    }
    surface.setAttribute("color", new THREE.BufferAttribute(colors, 3));
    surface.deleteAttribute("uv");
    surface.scale(1, 0.9, 1);
    const coreMaterial = new THREE.MeshPhysicalMaterial({
        name: "Matte banded atmosphere — zero specular",
        vertexColors: true,
        metalness: 0,
        roughness: 1,
        specularIntensity: 0,
    });
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
    const count = lowPower ? 14000 : 48000;
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
        size: lowPower ? 0.009 : 0.0075,
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
    const rockCount = lowPower ? 360 : 1200;
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
        const size = 0.003 + random() * 0.0045;
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
