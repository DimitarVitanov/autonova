<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});

const clean = (label) => label.replace('&laquo;', '‹').replace('&raquo;', '›').replace(/Previous|Next/g, (m) => (m === 'Previous' ? '‹' : '›'));
</script>

<template>
    <nav v-if="links.length > 3" class="pagination">
        <template v-for="(link, i) in links" :key="i">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                :class="['page-btn', { active: link.active }]"
                v-html="clean(link.label)"
            />
            <span v-else class="page-btn disabled" v-html="clean(link.label)" />
        </template>
    </nav>
</template>

<style scoped>
.pagination { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 28px; }
.page-btn {
    min-width: 40px; height: 40px; display: inline-grid; place-items: center; padding: 0 12px;
    font: 700 13px var(--font-heading); color: var(--color-text);
    background: var(--color-card); border: 1px solid var(--color-hairline);
    border-radius: var(--radius-md); box-shadow: var(--shadow-xs);
    transition: transform .16s var(--ease), box-shadow .16s var(--ease), border-color .16s var(--ease), color .16s var(--ease), background .16s var(--ease);
}
.page-btn:hover { border-color: var(--color-accent); color: var(--color-accent); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
.page-btn.active { background: var(--color-accent); color: #fff; border-color: var(--color-accent); box-shadow: var(--shadow-accent); }
.page-btn.active:hover { color: #fff; transform: translateY(-2px); }
.page-btn.disabled { opacity: 0.4; cursor: default; box-shadow: none; }
.page-btn.disabled:hover { border-color: var(--color-hairline); color: var(--color-text); transform: none; box-shadow: none; }
</style>
