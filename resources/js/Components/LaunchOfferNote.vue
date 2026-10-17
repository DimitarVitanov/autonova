<script setup>
/**
 * "Every plan is free until …" pill, shown only while the launch offer runs.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const page = usePage();
const offer = computed(() => page.props.launchOffer ?? {});
</script>

<template>
    <div v-if="offer.active" class="launch-note">
        <span class="launch-dot" aria-hidden="true"></span>
        <span v-if="offer.until_label">{{ t('launch.until', 'Launch offer: every plan is free until') }} <strong>{{ offer.until_label }}</strong></span>
        <span v-else>{{ t('launch.now', 'Launch offer: every plan is free') }}</span>
    </div>
</template>

<style scoped>
.launch-note {
    display: inline-flex;
    max-width: 100%;
    align-items: center;
    gap: 10px;
    padding: 9px 16px;
    border-radius: var(--radius-pill);
    background: var(--color-accent-100);
    color: var(--color-accent-800);
    font-size: 13.5px;
    font-weight: 600;
    line-height: 1.35;
}
.launch-note strong { font-weight: 800; }
.launch-dot {
    flex: none;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color-accent);
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--color-accent) 22%, transparent);
}
</style>
