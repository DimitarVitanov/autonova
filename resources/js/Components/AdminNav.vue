<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    current: { type: String, default: '' },
});

const page = usePage();
const role = computed(() => page.props.auth?.user?.role ?? null);

const items = computed(() => {
    const base = [
        { name: 'admin.dashboard', label: t('adm.overview', 'Overview') },
        { name: 'admin.listings', label: t('adm.listings', 'Listings') },
    ];
    if (role.value === 'admin') {
        base.push(
            { name: 'admin.users', label: t('adm.users', 'Users') },
            { name: 'admin.categories', label: t('adm.categories', 'Categories') },
            { name: 'admin.translations', label: t('adm.translations', 'Translations') },
        );
    }
    return base;
});

const isCurrent = (name) => {
    try {
        if (route().current(name)) return true;
    } catch (e) {
        /* route helper not resolvable — fall back to prop */
    }
    return props.current === name;
};
</script>

<template>
    <nav class="admin-nav">
        <div class="mono admin-nav-label">{{ t('adm.staff_area', 'Staff area') }}</div>
        <div class="admin-nav-list">
            <Link
                v-for="item in items"
                :key="item.name"
                :href="route(item.name)"
                class="admin-nav-link"
                :class="{ current: isCurrent(item.name) }"
            >
                {{ item.label }}
            </Link>
        </div>
    </nav>
</template>

<style scoped>
.admin-nav-label { padding: 0 4px 12px; }
.admin-nav-list { display: flex; flex-direction: column; gap: 4px; }
.admin-nav-link {
    display: block;
    padding: 11px 14px;
    font: 700 14px/1.2 var(--font-heading);
    color: var(--color-text);
    border-radius: var(--radius-md);
    transition: background .16s var(--ease), color .16s var(--ease);
}
.admin-nav-link:hover { background: var(--color-surface); color: var(--color-accent); }
.admin-nav-link.current { background: var(--color-accent-100); color: var(--color-accent-700); }

@media (max-width: 820px) {
    .admin-nav-list { flex-direction: row; flex-wrap: wrap; }
    .admin-nav-link { flex: 1 1 auto; text-align: center; }
}
</style>
