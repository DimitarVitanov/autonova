<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    dealer: { type: Object, required: true },
    vehicles: { type: Object, required: true },
    stats: { type: Object, default: () => ({ active: 0, sold: 0, rating: 0, years: 0 }) },
    filters: { type: Object, default: () => ({ sort: '' }) },
    options: { type: Object, default: () => ({ sorts: [] }) },
    favoriteIds: { type: Array, default: () => [] },
});

const favSet = computed(() => new Set(props.favoriteIds));

const sort = ref(props.filters.sort ?? '');
const applySort = () => {
    router.get(route('dealers.show', props.dealer.slug), { sort: sort.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const websiteLabel = computed(() => (props.dealer.website || '').replace(/^https?:\/\//, '').replace(/\/$/, ''));
const ratingLabel = computed(() => Number(props.stats.rating || 0).toFixed(1));
</script>

<template>
    <Head :title="dealer.name" />
    <MarketplaceLayout>
        <div class="wrap page">
            <!-- Cover banner -->
            <div class="frame cover" :class="{ 'cover-empty': !dealer.cover_url }">
                <img v-if="dealer.cover_url" :src="dealer.cover_url" :alt="dealer.name" />
            </div>

            <!-- Overlapping header -->
            <header class="dealer-head">
                <div class="logo" :class="{ 'logo-empty': !dealer.logo_url }">
                    <img v-if="dealer.logo_url" :src="dealer.logo_url" :alt="dealer.name" />
                    <span v-else>{{ dealer.name.charAt(0) }}</span>
                </div>

                <div class="head-main">
                    <h2 class="head-name">{{ dealer.name }}</h2>
                    <div class="tags-row">
                        <span v-if="dealer.verified" class="tag tag-accent">{{ t('common.verified', 'Verified') }}</span>
                        <span v-if="dealer.city || dealer.address" class="tag tag-neutral">
                            {{ [dealer.city ? t('city.' + dealer.city, dealer.city) : null, dealer.address].filter(Boolean).join(', ') }}
                        </span>
                        <span v-if="dealer.founded_year" class="tag tag-neutral">{{ t('dsh.since', 'Since') }} {{ dealer.founded_year }}</span>
                    </div>
                </div>

                <div class="head-actions">
                    <button type="button" class="btn btn-secondary">{{ t('dsh.follow', 'Follow') }}</button>
                    <button type="button" class="btn btn-primary">{{ t('dsh.contact', 'Contact') }}</button>
                </div>
            </header>

            <!-- Stats strip -->
            <div class="stats">
                <div class="stat">
                    <div class="stat-num">{{ num(stats.active) }}</div>
                    <div class="mono">{{ t('dsh.stat_active', 'vehicles for sale') }}</div>
                </div>
                <div class="stat">
                    <div class="stat-num">{{ num(stats.sold) }}</div>
                    <div class="mono">{{ t('dsh.stat_sold', 'sold') }}</div>
                </div>
                <div class="stat">
                    <div class="stat-num">{{ ratingLabel }}</div>
                    <div class="mono">{{ t('dsh.stat_rating', 'avg. rating') }}</div>
                </div>
                <div class="stat">
                    <div class="stat-num">{{ num(stats.years) }}</div>
                    <div class="mono">{{ t('dsh.stat_years', 'years on market') }}</div>
                </div>
            </div>

            <!-- Main two-column -->
            <div class="body">
                <!-- Left: vehicles -->
                <section class="col-main">
                    <div class="section-head">
                        <h3>{{ t('dsh.for_sale', 'Vehicles for sale') }}</h3>
                        <label class="sort">
                            <span class="mono">{{ t('dsh.sort', 'Sort') }}</span>
                            <select v-model="sort" class="input" @change="applySort">
                                <option v-for="o in options.sorts" :key="o.value" :value="o.value">{{ t('sort.' + o.value, o.label) }}</option>
                            </select>
                        </label>
                    </div>

                    <div v-if="vehicles.data.length" class="grid-cards">
                        <VehicleCard
                            v-for="v in vehicles.data"
                            :key="v.id"
                            :vehicle="v"
                            :favorited="favSet.has(v.id)"
                        />
                    </div>
                    <p v-else class="text-muted empty">{{ t('dsh.empty', 'This dealer has no active listings right now.') }}</p>

                    <Pagination :links="vehicles.meta.links" />
                </section>

                <!-- Right: sidebar -->
                <aside class="col-side">
                    <div class="panel">
                        <div class="panel-head"><h4>{{ t('dsh.about', 'About') }}</h4></div>
                        <div class="panel-body">
                            <p v-if="dealer.about" class="about">{{ dealer.about }}</p>
                            <div v-if="dealer.phone" class="spec-row">
                                <span class="mono">{{ t('dsh.phone', 'Phone') }}</span>
                                <a :href="`tel:${dealer.phone}`">{{ dealer.phone }}</a>
                            </div>
                            <div v-if="dealer.hours" class="spec-row">
                                <span class="mono">{{ t('dsh.hours', 'Hours') }}</span>
                                <span>{{ dealer.hours }}</span>
                            </div>
                            <div v-if="dealer.website" class="spec-row">
                                <span class="mono">{{ t('dsh.website', 'Website') }}</span>
                                <a :href="dealer.website" target="_blank" rel="noopener noreferrer">{{ websiteLabel }}</a>
                            </div>
                            <div v-if="dealer.address" class="spec-row">
                                <span class="mono">{{ t('dsh.address', 'Address') }}</span>
                                <span>{{ dealer.address }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="map-box">
                        <span class="mono">{{ t('dsh.map', 'Map — location') }}</span>
                        <span v-if="dealer.city" class="map-city">{{ t('city.' + dealer.city, dealer.city) }}</span>
                    </div>
                </aside>
            </div>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.page { padding: 24px 32px 56px; }

/* Cover */
.cover { height: 240px; width: 100%; border-radius: var(--radius-xl); box-shadow: var(--shadow-card); }
.cover-empty {
    background:
        radial-gradient(80% 130% at 86% -20%, color-mix(in srgb, var(--color-accent) 34%, transparent), transparent 55%),
        linear-gradient(120deg, var(--color-ink), var(--color-ink-3));
}

/* Overlapping header */
.dealer-head { display: flex; align-items: flex-end; gap: 22px; margin-top: 18px; }
.logo {
    width: 120px; height: 120px; flex: none; position: relative; overflow: hidden;
    background: var(--color-text); border: 4px solid var(--color-card); border-radius: var(--radius-lg);
    margin-top: -64px; display: grid; place-items: center; box-shadow: var(--shadow-md);
}
.logo img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.logo span { color: var(--color-bg); font: 800 46px var(--font-heading); }
.head-main { flex: 1; min-width: 0; padding-bottom: 4px; }
.head-name { margin: 0; font-size: clamp(26px, 3vw, 38px); letter-spacing: -0.02em; }
.tags-row { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.head-actions { display: flex; gap: 8px; padding-bottom: 6px; flex: none; }

/* Stats strip */
.stats {
    display: grid; grid-template-columns: repeat(4, 1fr);
    background: var(--color-card); border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    margin-top: 32px; overflow: hidden;
}
.stat { padding: 22px 24px; }
.stat + .stat { border-left: 1px solid var(--color-hairline); }
.stat-num { font: 800 30px/1 var(--font-heading); color: var(--color-accent-700); }
.stat .mono { margin-top: 8px; }

/* Body */
.body { display: grid; grid-template-columns: 1fr 340px; gap: 32px; margin-top: 40px; align-items: start; }
.section-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--color-divider); padding-bottom: 12px; margin-bottom: 24px; }
.sort { display: flex; align-items: center; gap: 10px; }
.sort .mono { flex: none; }
.sort .input { min-height: 36px; }
.grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px; }
.empty { padding: 40px 0; text-align: center; }

/* Sidebar */
.col-side { display: flex; flex-direction: column; gap: 20px; }
.panel-head { padding: 16px 18px; border-bottom: 1px solid var(--color-hairline); }
.panel-head h4 { margin: 0; }
.panel-body { padding: 4px 16px 12px; }
.about { font-size: 14px; color: var(--color-neutral-800); margin: 14px 0 6px; }
.panel-body .spec-row a { color: var(--color-accent-700); }
.panel-body .spec-row a:hover { color: var(--color-accent); }
.map-box {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
    height: 200px; background: var(--color-surface); border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;
    background-image: repeating-linear-gradient(0deg, transparent 0 27px, var(--color-hairline) 27px 28px),
                      repeating-linear-gradient(90deg, transparent 0 27px, var(--color-hairline) 27px 28px);
}
.map-city { font: 800 15px var(--font-heading); color: var(--color-text); }

@media (max-width: 960px) {
    .body { grid-template-columns: 1fr; }
}
@media (max-width: 700px) {
    .page { padding: 20px 18px 48px; }
    .dealer-head { flex-wrap: wrap; }
    .head-actions { width: 100%; padding-bottom: 0; }
    .head-actions .btn { flex: 1; }
    .stats { grid-template-columns: 1fr 1fr; }
    .stat { padding: 18px 20px; }
    .stat + .stat { border-left: 0; }
    .stat:nth-child(odd) { border-right: 1px solid var(--color-hairline); }
    .stat:nth-child(n+3) { border-top: 1px solid var(--color-hairline); }
}
</style>
