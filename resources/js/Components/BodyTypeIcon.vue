<script setup>
// Detailed, original side-profile car icons (one per body type) in a premium
// showroom style: a filled body with a cut-out greenhouse + wheel wells
// (fill-rule evenodd), and wheels drawn as rims with a brand-red hub.
// viewBox 0 0 64 30. Front of the car faces left.
const FRONT_WELL = 'M11.1,22 a4.9,4.9 0 1,0 9.8,0 a4.9,4.9 0 1,0 -9.8,0 z';
const REAR_WELL = 'M42.1,22 a4.9,4.9 0 1,0 9.8,0 a4.9,4.9 0 1,0 -9.8,0 z';

const SHAPES = {
    Sedan: {
        outline: 'M6,22 L6,17.2 L9,16 L19,15.4 L24.5,10.6 Q25.4,9.8 26.8,9.8 L37,9.8 Q38.6,9.8 39.6,10.8 L44.5,15 L55,16 L58,17.2 L58,22 Z',
        glass: 'M27,11.4 L37,11.4 Q38,11.4 38.7,12.2 L41.2,14.6 L25.4,14.6 L26,12.2 Q26.3,11.4 27,11.4 Z',
    },
    Hatchback: {
        outline: 'M8,22 L8,17 L11,15.8 L20,15.3 L24.5,10.6 Q25.4,9.8 26.8,9.8 L36,9.8 Q37.8,9.8 38.6,11 L41.6,15.4 L52,15.6 Q53.6,15.7 53.7,17 L53.7,22 Z',
        glass: 'M27,11.4 L36,11.4 L40,15 L25.6,15 L26,12 Q26.3,11.4 27,11.4 Z',
    },
    Estate: {
        outline: 'M6,22 L6,17.2 L9,16 L19,15.4 L24.5,10.4 Q25.4,9.6 26.8,9.6 L50,9.6 Q52,9.6 52.4,10.8 L53.8,15 L56,16 L58,17.2 L58,22 Z',
        glass: 'M27,11.2 L49,11.2 L51.4,14.8 L25.4,14.8 L26,12 Q26.3,11.2 27,11.2 Z',
    },
    SUV: {
        outline: 'M6,22 L6,15.5 L9,14.2 L18,13.8 L22,8 Q23,7 25,7 L46,7 Q48.5,7 49.5,8.6 L52.5,13.6 L57,14.4 L58,15.5 L58,22 Z',
        glass: 'M25,9 L46,9 L49,13 L23.8,13 L24.2,9.6 Q24.4,9 25,9 Z',
    },
    Coupe: {
        outline: 'M6,22 L6,17.6 L10,16.2 L18,15 Q22,14 24,11 L30,8.6 Q33,7.6 37,8.4 L52,13.4 Q58,15 58,17.4 L58,22 Z',
        glass: 'M27,11.6 Q31,10 35,10.6 L45.5,13.2 L25.6,13.6 Q25.7,12.4 27,11.6 Z',
    },
    Convertible: {
        outline: 'M6,22 L6,17.4 L10,16 L26,15 L29,11.5 L30.6,15 L52,15.6 Q58,16 58,17.6 L58,22 Z',
        glass: 'M27.6,15 L29,11.9 L30,15 Z',
    },
    Roadster: {
        outline: 'M6,22 L6,17.4 L10,16 L26,15 L29,11.5 L30.6,15 L52,15.6 Q58,16 58,17.6 L58,22 Z',
        glass: 'M27.6,15 L29,11.9 L30,15 Z',
    },
    Minivan: {
        outline: 'M6,22 L6,13.5 Q6,12 8,11.4 L12,7.6 Q13.6,6.4 16.4,6.2 L44,6.2 Q49,6.4 52,9 L56.5,13 Q58,14 58,15.4 L58,22 Z',
        glass: 'M14,8.2 L44,8.2 Q47,8.3 49.5,10.4 L52,12.6 L11,12.6 Q11.6,9.6 13,8.6 Q13.4,8.3 14,8.2 Z',
    },
    Pickup: {
        outline: 'M6,22 L6,14.5 L9,13.4 L12,8.5 Q12.9,7.6 14.6,7.6 L24,7.6 Q25.8,7.6 26,9.4 L26,13.6 L27.5,14 L58,14 L58,22 Z',
        glass: 'M15,9 L24,9 L24,12.8 L13.6,12.8 L14,9.8 Q14.2,9 15,9 Z',
    },
    Van: {
        outline: 'M6,22 L6,10 L6.6,8.8 L10,7 Q11,6.4 12.6,6.4 L52,6.4 Q54,6.4 54.6,7.8 L57.6,14 L58,15 L58,22 Z',
        glass: 'M10,8.8 L15.6,8.2 L15.6,12 L9,12 L9.3,9.8 Q9.5,8.9 10,8.8 Z',
    },
    Transporter: {
        outline: 'M6,22 L6,10 L6.6,8.8 L10,7 Q11,6.4 12.6,6.4 L52,6.4 Q54,6.4 54.6,7.8 L57.6,14 L58,15 L58,22 Z',
        glass: 'M10,8.8 L15.6,8.2 L15.6,12 L9,12 L9.3,9.8 Q9.5,8.9 10,8.8 Z',
    },
};

const props = defineProps({
    type: { type: String, required: true },
    size: { type: Number, default: 64 },
});

const shape = () => SHAPES[props.type] ?? SHAPES.Sedan;
const bodyPath = () => `${shape().outline} ${shape().glass} ${FRONT_WELL} ${REAR_WELL}`;
const wheels = [16, 47];
</script>

<template>
    <svg
        :width="size" :height="size * 30 / 64"
        viewBox="0 0 64 30" fill="none" aria-hidden="true"
    >
        <path :d="bodyPath()" fill="currentColor" fill-rule="evenodd" />
        <g v-for="cx in wheels" :key="cx">
            <circle :cx="cx" cy="22" r="4" fill="none" stroke="currentColor" stroke-width="1.8" />
            <circle :cx="cx" cy="22" r="1.25" fill="var(--color-accent)" />
        </g>
    </svg>
</template>
