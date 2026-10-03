<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import AdminNav from '@/Components/AdminNav.vue';
import Pagination from '@/Components/Pagination.vue';
import { eur, statusLabel, statusClass } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    vehicles: { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({}) },
    statusCounts: { type: Object, default: () => ({}) },
});

const tabs = computed(() => [
    { key: 'all', label: t('common.all_prefix', 'All') },
    { key: 'pending', label: t('status.pending', 'Pending') },
    { key: 'active', label: t('status.active', 'Active') },
    { key: 'sold', label: t('status.sold', 'Sold') },
    { key: 'rejected', label: t('status.rejected', 'Rejected') },
    { key: 'draft', label: t('status.draft', 'Draft') },
]);

const activeStatus = computed(() => props.filters?.status || 'all');

const countFor = (key) => {
    if (key === 'all') {
        return Object.values(props.statusCounts ?? {}).reduce((a, b) => a + Number(b || 0), 0);
    }
    return Number(props.statusCounts?.[key] ?? 0);
};

const q = ref(props.filters?.q ?? '');

const search = () => {
    router.get(
        route('admin.listings'),
        { status: props.filters?.status || undefined, q: q.value || undefined },
        { preserveState: true, preserveScroll: true },
    );
};

const approve = (slug) => router.post(route('admin.listings.approve', slug), {}, { preserveScroll: true });

const reject = (slug) => {
    const reason = window.prompt(t('adm.reject_prompt', 'Reason for rejection?'));
    if (reason === null) return;
    router.post(route('admin.listings.reject', slug), { reason }, { preserveScroll: true });
};

const feature = (slug) => router.post(route('admin.listings.feature', slug), {}, { preserveScroll: true });

const destroy = (slug) => {
    if (window.confirm(t('adm.delete_confirm', 'Delete this listing permanently? This cannot be undone.'))) {
        router.delete(route('admin.listings.destroy', slug), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="t('adm.listings', 'Listings')" />
    <MarketplaceLayout>
        <section class="wrap admin">
            <div class="admin-grid">
                <aside class="admin-side">
                    <AdminNav current="admin.listings" />
                </aside>

                <div class="admin-main">
                    <div class="admin-head">
                        <div class="mono mono-accent">{{ t('footer.inventory', 'Inventory') }}</div>
                        <h2>{{ t('adm.listings', 'Listings') }}</h2>
                    </div>

                    <div class="tabs">
                        <Link
                            v-for="tab in tabs"
                            :key="tab.key"
                            :href="route('admin.listings', { status: tab.key, q: filters.q || undefined })"
                            class="tab"
                            :class="{ current: activeStatus === tab.key }"
                        >
                            <span>{{ tab.label }}</span>
                            <span class="tab-count">{{ countFor(tab.key) }}</span>
                        </Link>
                    </div>

                    <form class="search" @submit.prevent="search">
                        <input v-model="q" class="input" type="search" :placeholder="t('adm.listings_search_ph', 'Search by title, seller, city…')" />
                        <button type="submit" class="btn btn-secondary">{{ t('common.search', 'Search') }}</button>
                    </form>

                    <div v-if="vehicles.data.length" class="table-scroll panel">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ t('adm.vehicle', 'Vehicle') }}</th>
                                    <th>{{ t('show.seller', 'Seller') }}</th>
                                    <th>{{ t('adm.price', 'Price') }}</th>
                                    <th>{{ t('adm.status', 'Status') }}</th>
                                    <th class="col-actions">{{ t('adm.actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in vehicles.data" :key="row.id">
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
                                                <div class="mono">{{ row.year }} · {{ (row.city ? t('city.' + row.city, row.city) : '') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="nowrap">{{ row.seller }} · {{ row.seller_type === 'dealer' ? t('flt.dealer', 'Dealer') : t('flt.private', 'Private') }}</td>
                                    <td class="nowrap price">{{ eur(row.price) }}</td>
                                    <td><span class="tag" :class="statusClass[row.status]">{{ t('status.' + row.status, statusLabel[row.status] ?? row.status) }}</span></td>
                                    <td>
                                        <div class="actions">
                                            <template v-if="row.status === 'pending'">
                                                <button class="btn btn-primary btn-sm" @click="approve(row.slug)">{{ t('adm.approve', 'Approve') }}</button>
                                                <button class="btn btn-secondary btn-sm" @click="reject(row.slug)">{{ t('adm.reject', 'Reject') }}</button>
                                            </template>
                                            <button class="btn btn-secondary btn-sm" @click="feature(row.slug)">{{ t('adm.feature', 'Feature') }}</button>
                                            <Link :href="route('vehicles.show', row.slug)" class="btn btn-secondary btn-sm">{{ t('adm.view', 'View') }}</Link>
                                            <button class="btn btn-danger btn-sm" @click="destroy(row.slug)">{{ t('adm.delete', 'Delete') }}</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="empty panel-surface">
                        <div class="mono mono-accent">{{ t('adm.no_listings', 'No listings') }}</div>
                        <p class="text-muted">{{ t('adm.no_match', 'Nothing matches this filter.') }}</p>
                    </div>

                    <Pagination :links="vehicles.links" />
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

.tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
.tab { display: inline-flex; align-items: center; gap: 8px; padding: 9px 15px; font: 700 13px/1 var(--font-heading); color: var(--color-text); background: var(--color-card); border: 1px solid var(--color-hairline); border-radius: var(--radius-pill); box-shadow: var(--shadow-xs); transition: background .16s var(--ease), color .16s var(--ease), border-color .16s var(--ease), box-shadow .16s var(--ease); }
.tab:hover { background: var(--color-surface); color: var(--color-accent); border-color: var(--color-divider); }
.tab.current { background: var(--color-accent); color: #fff; border-color: var(--color-accent); box-shadow: var(--shadow-accent); }
.tab-count { font: 700 11px/1 var(--font-mono); letter-spacing: 0.06em; padding: 3px 7px; border-radius: var(--radius-pill); background: color-mix(in srgb, var(--color-text) 8%, transparent); }
.tab.current .tab-count { background: color-mix(in srgb, #fff 22%, transparent); color: #fff; }

.search { display: flex; gap: 8px; margin-bottom: 22px; max-width: 460px; }
.search .input { flex: 1; }

.table-scroll { overflow-x: auto; }
.table-scroll .table tbody tr:last-child td { border-bottom: 0; }
.veh { display: flex; align-items: center; gap: 12px; min-width: 220px; }
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
}
</style>
