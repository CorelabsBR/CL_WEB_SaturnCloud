import { mkdir, writeFile } from "node:fs/promises";
import assert from "node:assert/strict";
import * as THREE from "three";
import { GLTFExporter } from "three/addons/exporters/GLTFExporter.js";
import { GLTFLoader } from "three/addons/loaders/GLTFLoader.js";
import { createSaturnBrandModel } from "../resources/js/saturn-brand-model.js";
import { applySaturnBrandPose } from "../resources/js/saturn-brand-motion.js";

// The exporter only needs FileReader for its binary buffer; there are no images.
globalThis.FileReader = class {
    readAsArrayBuffer(blob) {
        blob.arrayBuffer().then((result) => {
            this.result = result;
            this.onloadend?.();
        });
    }
};
const model = createSaturnBrandModel();
model.updateMatrixWorld(true);
assert.equal(
    model.getObjectByName("Spherical network junctions"),
    undefined,
    "The globe grid must not contain node balls",
);
let triangles = 0,
    meshes = 0;
model.traverse((object) => {
    if (!object.isMesh) return;
    meshes++;
    if (object.material.isMeshStandardMaterial) {
        assert.equal(
            object.material.metalness,
            0,
            "All surfaces must be nonmetallic",
        );
        assert.equal(
            object.material.roughness,
            1,
            "All surfaces must have a matte finish",
        );
    }
    assert(!object.material.map, "The emblem must not use an image texture");
    object.geometry.computeBoundingBox();
    const size = object.geometry.boundingBox.getSize(new THREE.Vector3());
    assert(
        size.x > 0.001 && size.y > 0.001 && size.z > 0.001,
        `${object.name} must have volume`,
    );
    triangles +=
        ((object.isInstancedMesh ? object.count : 1) *
            (object.geometry.index?.count ??
                object.geometry.attributes.position.count)) /
        3;
});
// Verify six minutes of motion: the globe axis never pitches/rolls, even on scroll.
const motionModel = createSaturnBrandModel({ lowPower: true });
const axis = new THREE.Vector3(0, 1, 0);
const quaternion = new THREE.Quaternion();
const referenceRingAxis = axis
    .clone()
    .applyEuler(new THREE.Euler(0.34, 0, 0.35))
    .applyEuler(new THREE.Euler(0, (75 * Math.PI) / 180, 0));
let firstSatellite, lastSatellite, firstCloud, lastCloud;
for (let time = 0; time <= 360; time += 0.5) {
    applySaturnBrandPose(motionModel, time, (time % 7) / 6);
    assert.equal(
        motionModel.rotation.y,
        (75 * Math.PI) / 180,
        "The display must retain the 75-degree side view",
    );
    motionModel.updateMatrixWorld(true);
    const globe = motionModel.getObjectByName("Spherical network");
    globe.getWorldQuaternion(quaternion);
    const worldAxis = axis.clone().applyQuaternion(quaternion);
    assert(
        worldAxis.distanceTo(axis) < 1e-10,
        "The planet must stay upright throughout the animation",
    );
    const cloud = new THREE.Vector3(1, 0, 0).applyMatrix4(globe.matrixWorld);
    if (time === 0) firstCloud = cloud;
    if (time === 5) lastCloud = cloud;
    const orbits = motionModel.getObjectByName("Orbital framework");
    orbits.getWorldQuaternion(quaternion);
    assert(
        axis.clone().applyQuaternion(quaternion).distanceTo(referenceRingAxis) <
            1e-10,
        "The rings must keep exactly the same inclination while spinning",
    );
    const satellite = new THREE.Vector3(2.13, 0, 0).applyMatrix4(
        motionModel.getObjectByName("Particle ring field").matrixWorld,
    );
    if (time === 0) firstSatellite = satellite;
    if (time === 30) lastSatellite = satellite;
}
assert(
    firstCloud.distanceTo(lastCloud) > 0.8,
    "The globe must visibly rotate around its own axis within five seconds",
);
assert(
    firstSatellite.distanceTo(lastSatellite) > 0.5,
    "The rings must actually rotate within their fixed plane",
);
assert(
    model.getObjectByName("Solid spherical core").material.specularIntensity ===
        0,
    "The atmosphere must have no specular shine",
);
const particles = model.getObjectByName("Saturn ring particles");
assert(
    particles.isPoints &&
        particles.geometry.attributes.position.count === 76000,
);
assert.equal(
    model.getObjectByName("Tubular longitude and latitude grid"),
    undefined,
);
const grainPositions = particles.geometry.attributes.position;
for (let index = 0; index < grainPositions.count; index++) {
    const radius = Math.hypot(
        grainPositions.getX(index),
        grainPositions.getZ(index),
    );
    assert(radius >= 1.22 - 1e-6 && radius <= 2.28 + 1e-6);
    assert(
        !(radius > 1.880001 && radius < 1.959999),
        "Cassini division must remain empty",
    );
}
// glTF represents particle fields as POINTS primitives. An unlit export material
// preserves their vertex colors without exporter warnings about PointsMaterial.
const exportModel = model.clone(true);
exportModel.traverse((object) => {
    if (object.isPoints)
        object.material = new THREE.MeshBasicMaterial({
            name: "Mineral ring grains",
            vertexColors: true,
        });
});
const binary = await new GLTFExporter().parseAsync(exportModel, {
    binary: true,
    onlyVisible: true,
});
const gltf = await new GLTFLoader().parseAsync(binary, "");
assert(gltf.scene.children.length > 0);
let loadedParticles = 0,
    loadedInstances = 0;
gltf.scene.traverse((object) => {
    if (object.isPoints)
        loadedParticles += object.geometry.attributes.position.count;
    if (object.isInstancedMesh) loadedInstances += object.count;
});
assert.equal(
    loadedParticles,
    76000,
    "The GLB must preserve every ring particle",
);
assert.equal(
    loadedInstances,
    1800,
    "The GLB must preserve the solid mineral fragments",
);
const bytes = Buffer.from(binary);
assert.equal(bytes.toString("ascii", 0, 4), "glTF");
await mkdir("public/models", { recursive: true });
await writeFile("public/models/saturn-brand.glb", bytes);
await writeFile(
    "public/models/saturn-brand.json",
    JSON.stringify(
        {
            source: model.userData.source,
            meshes,
            triangles,
            bytes: bytes.length,
            textures: 0,
            particles: model.userData.particles,
            rockInstances: model.userData.rockInstances,
            volumetric: true,
        },
        null,
        2,
    ) + "\n",
);
console.log(
    JSON.stringify({
        meshes,
        triangles,
        bytes: bytes.length,
        textures: 0,
        particles: model.userData.particles,
        rockInstances: model.userData.rockInstances,
        roundTripGLTF: "passed",
        uprightMotionSamples: 721,
        matteCore: true,
    }),
);
