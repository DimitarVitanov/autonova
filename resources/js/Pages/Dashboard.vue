<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import PromoPopup from '@/Components/PromoPopup.vue';
import { eur, num, statusLabel, statusClass } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    listings: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    dealer: { type: Object, default: null },
});

const accountName = computed(() => props.dealer?.name || t('acct.my_listings', 'My listings'));
const packageName = computed(() => props.dealer?.package || t('acct.free', 'Free'));

const statCards = computed(() => {
    const s = props.stats ?? {};
    const cards = [
        { value: s.active, label: t('dash.stat_active', 'Active listings') },
        { value: s.total_views, label: t('dash.views', 'Views') },
        { value: s.leads, label: t('dash.stat_leads', 'Leads') },
        { value: s.pending, label: t('dash.stat_pending', 'Pending review') },
    ];
    if (props.dealer) {
        cards.push({ value: s.promo_credits, label: t('dash.stat_credits', 'Promo credits') });
    }
    return cards;
});

const destroy = (row) => {
    if (confirm(t('dash.confirm_delete', 'Delete “:title”? This can’t be undone.').replace(':title', row.title))) {
        router.delete(route('vehicles.destroy', row.slug), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="t('acct.inventory', 'Inventory')" />
    <MarketplaceLayout>
        <div class="wrap dash">
            <!-- Sidebar -->
            <aside class="dash-side">
                <div class="side-account panel">
                    <div class="mono">{{ t('acct.account', 'Account') }}</div>
                    <div class="side-name">{{ accountName }}</div>
                </div>

                <nav class="side-nav">
                    <Link :href="route('dashboard')" class="side-link">{{ t('acct.overview', 'Overview') }}</Link>
                    <span class="side-link current">{{ t('acct.inventory', 'Inventory') }}</span>
                    <Link :href="route('inbox')" class="side-link">{{ t('nav.messages', 'Messages') }}</Link>
                    <Link :href="route('saved')" class="side-link">{{ t('nav.saved', 'Saved') }}</Link>
                    <Link :href="route('profile.edit')" class="side-link">{{ t('nav.profile', 'Profile') }}</Link>
                </nav>

                <div class="side-pkg panel-surface">
                    <div class="mono">{{ t('acct.package', 'Package') }}</div>
                    <div class="side-pkg-name">{{ packageName }}</div>
                    <Link :href="route('pricing')" class="btn btn-primary btn-sm btn-block">{{ t('acct.upgrade', 'Upgrade') }}</Link>
                </div>
            </aside>

            <!-- Main -->
            <main class="dash-main">
                <div class="dash-head">
                    <div>
                        <div class="mono mono-accent">{{ t('dash.kicker', 'Seller dashboard') }}</div>
                        <h2 class="dash-title">{{ t('acct.inventory', 'Inventory') }}</h2>
                    </div>
                    <Link :href="route('vehicles.create')" class="btn btn-primary">{{ t('dash.sell', 'Sell a vehicle') }}</Link>
                </div>

                <div class="stats-strip">
                    <div v-for="(c, i) in statCards" :key="i" class="stat-cell">
                        <div class="stat-num">{{ num(c.value) }}</div>
                        <div class="mono">{{ c.label }}</div>
                    </div>
                </div>

                <div v-if="listings.length" class="table-wrap panel">
                    <table class="table dash-table">
                        <thead>
                            <tr>
                                <th>{{ t('dash.col_vehicle', 'Vehicle') }}</th>
                                <th>{{ t('dash.col_price', 'Price') }}</th>
                                <th>{{ t('dash.views', 'Views') }}</th>
                                <th>{{ t('dash.col_status', 'Status') }}</th>
                                <th class="col-actions">{{ t('dash.col_actions', 'Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in listings" :key="row.id">
                                <td>
                                    <div class="veh-cell">
                                        <div class="veh-thumb frame frame-43">
                                            <img v-if="row.cover_url" :src="row.cover_url" :alt="row.title" loading="lazy" />
                                            <div v-else class="img-placeholder">{{ t('dash.no_photo', 'No photo') }}</div>
                                        </div>
                                        <div class="veh-info">
                                            <div class="veh-title">
                                                {{ row.title }}<span v-if="row.version" class="veh-version"> · {{ row.version }}</span>
                                            </div>
                                            <div class="mono">{{ row.year }} · {{ num(row.mileage_km) }} {{ t('unit.km', 'km') }} · ID {{ row.id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="veh-price">{{ eur(row.price) }}</td>
                                <td>{{ num(row.views) }}</td>
                                <td><span :class="['tag', statusClass[row.status]]">{{ t('status.' + row.status, statusLabel[row.status] || row.status) }}</span></td>
                                <td class="col-actions">
                                    <div class="veh-actions">
                                        <Link :href="route('vehicles.edit', row.slug)" class="btn btn-secondary btn-sm">{{ t('dash.edit', 'Edit') }}</Link>
                                        <button type="button" class="btn btn-danger btn-sm" @click="destroy(row)">{{ t('dash.delete', 'Delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="empty panel">
                    <div class="mono mono-accent">{{ t('dash.empty_kicker', 'No listings yet') }}</div>
                    <h3 class="empty-title">{{ t('dash.empty_title', 'Your inventory is empty.') }}</h3>
                    <p class="text-muted">{{ t('dash.empty_text', 'List your first vehicle and reach buyers across Macedonia.') }}</p>
                    <Link :href="route('vehicles.create')" class="btn btn-primary">{{ t('dash.sell', 'Sell a vehicle') }}</Link>
                </div>
            </main>
        </div>
        <PromoPopup />
    </MarketplaceLayout>
</template>

<style scoped>
.dash { display: grid; grid-template-columns: 220px 1fr; gap: 32px; align-items: start; padding: 40px 32px; }

/* Sidebar */
.dash-side { display: flex; flex-direction: column; gap: 18px; position: sticky; top: calc(var(--header-h) + 20px); }
.side-account { padding: 16px; }
.side-name { font: 800 18px/1.2 var(--font-heading); margin-top: 8px; }
.side-nav { display: flex; flex-direction: column; border: 1px solid var(--color-hairline); border-radius: var(--radius-lg); background: var(--color-card); box-shadow: var(--shadow-sm); overflow: hidden; }
.side-link { padding: 12px 14px; font: 700 14px var(--font-heading); color: var(--color-text); border-bottom: 1px solid var(--color-hairline); }
.side-link:last-child { border-bottom: 0; }
.side-link:hover { background: var(--color-surface); color: var(--color-accent); }
.side-link.current { color: var(--color-accent); background: var(--color-accent-100); box-shadow: inset 3px 0 0 var(--color-accent); }
.side-pkg { padding: 16px; display: flex; flex-direction: column; gap: 12px; align-items: flex-start; }
.side-pkg-name { font: 800 20px/1 var(--font-heading); text-transform: capitalize; }

/* Main */
.dash-main { min-width: 0; display: flex; flex-direction: column; gap: 24px; }
.dash-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--color-divider); padding-bottom: 16px; }
.dash-title { margin: 6px 0 0; font-size: 34px; }

/* Stats strip */
.stats-strip { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; }
.stat-cell { padding: 18px 20px; background: var(--color-card); border: 1px solid var(--color-hairline); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
.stat-num { font: 800 30px/1 var(--font-heading); }
.stat-cell .mono { margin-top: 8px; }

/* Table */
.table-wrap { overflow: hidden; overflow-x: auto; }
.dash-table { min-width: 660px; }
.veh-cell { display: flex; align-items: center; gap: 12px; }
.veh-thumb { width: 64px; flex: none; border-radius: var(--radius-md); overflow: hidden; }
.veh-info { min-width: 0; }
.veh-title { font: 800 14px/1.25 var(--font-heading); }
.veh-version { color: var(--color-neutral-600); font-weight: 600; }
.veh-info .mono { margin-top: 6px; }
.veh-price { font: 800 16px var(--font-heading); color: var(--color-accent-700); white-space: nowrap; }
.col-actions { text-align: right; }
.veh-actions { display: inline-flex; gap: 8px; justify-content: flex-end; }

/* Empty */
.empty { padding: 48px 32px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-title { margin: 6px 0 0; }
.empty p { margin: 0; max-width: 40ch; }

@media (max-width: 900px) {
    .dash { grid-template-columns: 1fr; gap: 24px; padding: 28px 18px; }
    .dash-side { position: static; }
}
@media (max-width: 560px) {
    .dash-head { flex-direction: column; align-items: flex-start; }
}
</style>
