// Evaluate clouds per fragment so close-ups stay detailed between mesh vertices.
// Object-space sampling makes the atmosphere rotate with the globe without seams.
export function refineSaturnAtmosphere(material, { lowPower = false } = {}) {
    material.onBeforeCompile = (shader) => {
        shader.vertexShader = `varying vec3 vAtmospherePosition;\n${shader.vertexShader}`
            .replace('#include <begin_vertex>', '#include <begin_vertex>\nvAtmospherePosition = position / vec3(1.065, 0.9585, 1.065);');
        shader.fragmentShader = `
            varying vec3 vAtmospherePosition;
            float atmosphereHash(vec3 p) {
                p = fract(p * 0.1031);
                p += dot(p, p.yzx + 33.33);
                return fract((p.x + p.y) * p.z);
            }
            float atmosphereNoise(vec3 p) {
                vec3 i = floor(p), f = fract(p);
                f = f * f * f * (f * (f * 6.0 - 15.0) + 10.0);
                return mix(
                    mix(mix(atmosphereHash(i), atmosphereHash(i + vec3(1,0,0)), f.x),
                        mix(atmosphereHash(i + vec3(0,1,0)), atmosphereHash(i + vec3(1,1,0)), f.x), f.y),
                    mix(mix(atmosphereHash(i + vec3(0,0,1)), atmosphereHash(i + vec3(1,0,1)), f.x),
                        mix(atmosphereHash(i + vec3(0,1,1)), atmosphereHash(i + vec3(1,1,1)), f.x), f.y), f.z);
            }
            float atmosphereClouds(vec3 p) {
                float sum = 0.0, weight = 0.5;
                for (int i = 0; i < ${lowPower ? 3 : 5}; i++) {
                    sum += weight * atmosphereNoise(p);
                    p = p * 2.03 + vec3(17.1, 9.2, 13.7);
                    weight *= 0.5;
                }
                return sum;
            }
            vec3 saturnAtmosphere(vec3 p) {
                p = normalize(p);
                float latitude = asin(clamp(p.y, -1.0, 1.0));
                float weather = atmosphereClouds(vec3(p.x * 4.0, latitude * 13.0, p.z * 4.0));
                // Stretch turbulence along the winds, retaining quiet, broad belts.
                float flow = latitude + (weather - 0.45) * 0.065;
                vec3 cloudPosition = vec3(p.x * 15.0, flow * 145.0, p.z * 15.0);
                float clouds = atmosphereClouds(cloudPosition);
                float belts = atmosphereNoise(vec3(2.0, flow * 19.0, 6.0));
                float ribbons = atmosphereNoise(vec3(4.0, flow * 66.0, 1.0));
                float equator = exp(-pow(latitude / 0.23, 2.0));
                float poles = smoothstep(0.85, 1.52, abs(latitude));
                float tone = clamp(0.20 + belts * 0.40 + ribbons * 0.13
                    + (clouds - 0.45) * 0.30 + equator * 0.12 - poles * 0.12, 0.0, 1.0);
                // Linear-light, muted mineral purples rather than saturated stripes.
                vec3 plum = vec3(0.080, 0.040, 0.125);
                vec3 violet = vec3(0.260, 0.155, 0.345);
                vec3 haze = vec3(0.620, 0.475, 0.690);
                return tone < 0.5 ? mix(plum, violet, tone * 2.0)
                    : mix(violet, haze, (tone - 0.5) * 2.0);
            }
        \n${shader.fragmentShader}`.replace(
            '#include <color_fragment>',
            'diffuseColor.rgb *= saturnAtmosphere(vAtmospherePosition);',
        );
    };
    material.customProgramCacheKey = () => `saturn-atmosphere-v1-${lowPower ? 'mobile' : 'full'}`;
}
