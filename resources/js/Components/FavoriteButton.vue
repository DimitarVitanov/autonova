<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    slug: { type: String, required: true },
    favorited: { type: Boolean, default: false },
    variant: { type: String, default: 'overlay' }, // 'overlay' | 'button'
});

const page = usePage();
const active = ref(props.favorited);
watch(() => props.favorited, (v) => (active.value = v));

const label = computed(() => (active.value ? t('fav.saved', 'Saved') : t('fav.save', 'Save')));

const toggle = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!page.props.auth.user) {
        router.visit(route('login'));
        return;
    }
    active.value = !active.value;
    router.post(route('favorites.toggle', props.slug), {}, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <button
        type="button"
        :class="['fav', variant === 'overlay' ? 'fav-overlay' : 'btn btn-secondary fav-btn', { on: active }]"
        :aria-pressed="active"
        @click="toggle"
    >
        <svg width="18" height="18" viewBox="0 0 24 24" :fill="active ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z" />
        </svg>
        <span v-if="variant === 'button'">{{ label }}</span>
    </button>
</template>

<style scoped>
.fav-overlay {
    position: absolute; right: 12px; bottom: 12px; z-index: 3;
    width: 38px; height: 38px; display: grid; place-items: center;
    background: color-mix(in srgb, #fff 88%, transparent);
    color: var(--color-text); border: 0; border-radius: var(--radius-pill); cursor: pointer;
    box-shadow: var(--shadow-sm);
    backdrop-filter: blur(6px);
    transition: color .16s var(--ease), background .16s var(--ease), transform .16s var(--ease), box-shadow .16s var(--ease);
}
.fav-overlay:hover { color: var(--color-accent); transform: scale(1.08); box-shadow: var(--shadow-md); }
.fav-overlay:active { transform: scale(0.96); }
.fav-overlay.on { color: var(--color-accent); background: #fff; }
.fav-btn.on { color: var(--color-accent); border-color: var(--color-accent); }
</style>
