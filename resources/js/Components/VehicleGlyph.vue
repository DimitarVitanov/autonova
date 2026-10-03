<script setup>
import { computed } from 'vue';
import { GLYPHS, GLYPH_VIEWBOX } from '@/lib/vehicleGlyphs.js';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: Number, default: 64 },
});

// viewBox is "minX minY width height" — derive the aspect for the height.
const ratio = computed(() => {
    const parts = GLYPH_VIEWBOX.split(/\s+/).map(Number);
    return parts[3] / parts[2];
});
const markup = computed(() => GLYPHS[props.name] ?? '');
</script>

<template>
    <svg
        v-if="markup"
        :width="size" :height="Math.round(size * ratio)"
        :viewBox="GLYPH_VIEWBOX"
        aria-hidden="true"
        v-html="markup"
    />
</template>
