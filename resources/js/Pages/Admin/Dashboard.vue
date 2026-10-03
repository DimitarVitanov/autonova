<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import AdminNav from '@/Components/AdminNav.vue';
import { eur, num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    pending: { type: Array, default: () => [] },
    role: { type: String, default: '' },
});

const tiles = computed(() => [
    { key: 'pending', label: t('adm.pending_review', 'Pending review') },
    { key: 'active', label: t('adm.stat_active', 'Active') },
    { key: 'total', label: t('adm.stat_total', 'Total listings') },
    { key: 'sold', label: t('adm.stat_sold', 'Sold') },
    { key: 'users', label: t('adm.users', 'Users') },
    { key: 'dealers', label: t('nav.dealers', 'Dealers') },
    { key: 'views', label: t('adm.stat_views', 'Views') },
    { key: 'categories', label: t('adm.categories', 'Categories') },
]);

const approve = (slug) => router.post(route('admin.listings.approve', slug), {}, { preserveScroll: true });

const reject = (slug) => {
    const reason = window.prompt(t('adm.reject_prompt', 'Reason for rejection?'));
    if (reason === null) return;
    router.post(route('admin.listings.reject', slug), { reason }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="t('adm.moderation_overview', 'Moderation overview')" />
    <MarketplaceLayout>
        <section class="wrap admin">
            <div class="admin-grid">
                <aside class="admin-side">
                    <AdminNav current="admin.dashboard" />
                </aside>

                <div class="admin-main">
                    <div class="admin-head">
                        <div class="mono mono-accent">{{ t('adm.moderation', 'Moderation') }}</div>
                        <h2>{{ t('adm.moderation_overview', 'Moderation overview') }}</h2>
                    </div>

                    <div class="stats-grid">
                        <div v-for="tile in tiles" :key="tile.key" class="stat">
                            <div class="stat-num">{{ num(stats[tile.key] ?? 0) }}</div>
                            <div class="mono">{{ tile.label }}</div>
                        </div>
                    </div>

                    <div class="section-head">
                        <h3>{{ t('adm.pending_review', 'Pending review') }}</h3>
                        <span class="mono">{{ num(pending.length) }} {{ t('adm.in_queue', 'in queue') }}</span>
                    </div>

                    <div v-if="pending.length" class="table-scroll panel">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ t('adm.vehicle', 'Vehicle') }}</th>
                                    <th>{{ t('search.category', 'Category') }}</th>
                                    <th>{{ t('adm.price', 'Price') }}</th>
                                    <th>{{ t('adm.submitted', 'Submitted') }}</th>
                                    <th class="col-actions">{{ t('adm.actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in pending" :key="row.id">
                                    <td>
                                        <div class="veh">
                                            <Link :href="route('vehicles.show', row.slug)" class="veh-thumb">
                                                <div class="frame frame-43">
                                                    <img v-if="row.cover_url" :src="row.cover_url" :alt="row.title" />
                                                    <div v-else class="img-placeholder">{{ t('common.no_photo', 'No photo') }}</div>
                                                </div>
                                            </Link>
                                            <div class="veh-info">
                                                <div class="veh-title">
                                                    {{ row.title }}<span v-if="row.version" class="veh-version"> · {{ row.version }}</span>
                                                </div>
                                                <div class="mono">{{ row.year }} · {{ (row.city ? t('city.' + row.city, row.city) : '') }} · {{ row.seller }} ({{ row.seller_type === 'dealer' ? t('flt.dealer', 'Dealer') : t('flt.private', 'Private') }})</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ row.category }}</td>
                                    <td class="nowrap price">{{ eur(row.price) }}</td>
                                    <td class="nowrap text-muted">{{ row.created_at }}</td>
                                    <td>
                                        <div class="actions">
                                            <Link :href="route('vehicles.show', row.slug)" class="btn btn-secondary btn-sm">{{ t('adm.view', 'View') }}</Link>
                                            <button class="btn btn-primary btn-sm" @click="approve(row.slug)">{{ t('adm.approve', 'Approve') }}</button>
                                            <button class="btn btn-secondary btn-sm" @click="reject(row.slug)">{{ t('adm.reject', 'Reject') }}</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="empty panel-surface">
                        <div class="mono mono-accent">{{ t('adm.queue_clear', 'Queue is clear') }}</div>
                        <p class="text-muted">{{ t('adm.queue_clear_text', 'No listings are waiting for review right now.') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </MarketplaceLayout>
</template>

<style scoped>
.admin { padding: 40px 32px 64px; }
.admin-grid { display: grid; grid-template-columns: 200px 1fr; gap: 32px; align-items: start; }
.admin-side { position: sticky; top: calc(var(--header-h) + 16px); }
.admin-head { margin-bottom: 22px; }
.admin-head .mono { margin-bottom: 8px; }
.admin-head h2 { margin: 0; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 40px; }
.stat { padding: 18px; background: var(--color-card); border: 1px solid var(--color-hairline); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
.stat-num { font: 800 30px/1 var(--font-heading); color: var(--color-accent-700); }
.stat .mono { margin-top: 8px; }

.section-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--color-divider); padding-bottom: 12px; margin-bottom: 18px; }
.section-head h3 { margin: 0; }

.table-scroll { overflow-x: auto; }
.table-scroll .table tbody tr:last-child td { border-bottom: 0; }
.veh { display: flex; align-items: center; gap: 12px; min-width: 240px; }
.veh-thumb { width: 60px; flex: none; display: block; border-radius: var(--radius-md); overflow: hidden; }
.veh-info { min-width: 0; }
.veh-title { font: 800 14px/1.25 var(--font-heading); }
.veh-version { color: var(--color-neutral-600); font-weight: 600; }
.veh-info .mono { margin-top: 5px; }
.price { font-weight: 800; color: var(--color-accent-700); }
.nowrap { white-space: nowrap; }
.col-actions { text-align: right; }
.actions { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }

.empty { padding: 40px 24px; text-align: center; }
.empty p { margin: 10px 0 0; }

@media (max-width: 820px) {
    .admin { padding: 28px 18px 48px; }
    .admin-grid { grid-template-columns: 1fr; gap: 20px; }
    .admin-side { position: static; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
