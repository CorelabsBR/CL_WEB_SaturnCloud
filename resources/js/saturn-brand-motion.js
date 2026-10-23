// Upright atmosphere; a fixed ring plane with particles orbiting inside it.
export function applySaturnBrandPose(model, time) {
    const globe = model.getObjectByName("Spherical network");
    const orbits = model.getObjectByName("Orbital framework");
    const field = model.getObjectByName("Particle ring field");
    model.rotation.set(0, (75 * Math.PI) / 180, 0);
    model.position.set(0, 0, 0);
    model.scale.setScalar(0.6336);
    globe.rotation.set(0, time * 0.042, 0);
    orbits.rotation.set(0.34, 0, 0.35);
    field.rotation.set(0, time * 0.042, 0);
}
