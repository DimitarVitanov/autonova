<script setup>
defineProps({
    variant: { type: String, default: 'full' }, // 'full' | 'mark'
    size: { type: Number, default: 22 },
    inverse: { type: Boolean, default: false },
});
</script>

<template>
    <span class="logo" :class="{ 'logo-inverse': inverse }">
        <svg class="mark" :width="size" :height="size" viewBox="0 0 100 100" aria-hidden="true">
            <rect width="100" height="100" fill="var(--color-accent)" />
            <path
                d="M50 12 C55 38 62 45 88 50 C62 55 55 62 50 88 C45 62 38 55 12 50 C38 45 45 38 50 12 Z"
                fill="var(--color-bg)"
            />
        </svg>
        <span v-if="variant === 'full'" class="word" :style="{ fontSize: size * 0.86 + 'px' }">
            AUTO<span class="accent">NOVA</span>
        </span>
    </span>
</template>

<style scoped>
.logo {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    line-height: 1;
    user-select: none;
}
.mark {
    display: block;
    flex: none;
}
.word {
    font-family: var(--font-heading);
    font-weight: 900;
    letter-spacing: -0.03em;
    color: var(--color-text);
}
.word .accent {
    color: var(--color-accent);
}
.logo-inverse .word {
    color: var(--color-bg);
}
</style>
