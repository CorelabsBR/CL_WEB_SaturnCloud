// The display and ring plane stay fixed; only rotation around each local axis changes.
export function applySaturnBrandPose(model, time) {
    const globe = model.getObjectByName("Spherical network");
    const orbits = model.getObjectByName("Orbital framework");
    model.rotation.set(0, (75 * Math.PI) / 180, 0);
    model.position.set(0, 0, 0);
    model.scale.setScalar(0.6336);
    globe.rotation.set(0, time * 0.042, 0);
    // XYZ composes fixed inclination (X) with spin about the inclined local Y axis.
    // The orbit normal is invariant: there is no precession, bobbing or wobble.
    orbits.rotation.set(0.34, time * 0.042, 0, "XYZ");
}
