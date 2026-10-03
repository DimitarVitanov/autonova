<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    dealers: { type: Object, required: true },
    filters: { type: Object, default: () => ({ q: '' }) },
});

const form = useForm({ q: props.filters.q ?? '' });

const submit = () => {
    form.get(route('dealers.index'), { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head :title="t('nav.dealers', 'Dealers')" />
    <MarketplaceLayout>
        <!-- Page header -->
        <section class="head divider-x">
            <div class="wrap head-inner">
                <div class="head-text">
                    <div class="mono mono-accent">{{ t('dlr.kicker', 'Directory · Verified sellers') }}</div>
                    <h1 class="head-title">{{ t('nav.dealers', 'Dealers') }}</h1>
                    <p class="text-muted head-sub">{{ t('dlr.sub', 'Browse verified dealerships across Macedonia. Real inventory, real photos, direct contact — no duplicate listings.') }}</p>
                </div>
                <form class="search" @submit.prevent="submit">
                    <span class="mono">{{ t('dlr.find', 'Find a dealer') }}</span>
                    <div class="search-row">
                        <input
                            v-model="form.q"
                            class="input"
                            type="text"
                            name="q"
                            :placeholder="t('dlr.search_ph', 'Search by name or city')"
                            autocomplete="off"
                        />
                        <button type="submit" class="btn btn-primary">{{ t('common.search', 'Search') }} →</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Dealer grid -->
        <section class="wrap section">
            <div class="section-head">
                <h3>{{ t('dlr.all', 'All dealers') }}</h3>
                <span v-if="dealers.total !== undefined" class="mono">{{ num(dealers.total) }} {{ t('dlr.count', 'dealers') }}</span>
            </div>

            <div v-if="dealers.data.length" class="dealers-grid">
                <div v-for="d in dealers.data" :key="d.id" class="dealer-card">
                    <div class="dealer-top">
                        <div v-if="d.logo_url" class="dealer-logo">
                            <img :src="d.logo_url" :alt="d.name" loading="lazy" />
                        </div>
                        <div v-else class="dealer-logo dealer-logo-empty">{{ d.name.charAt(0) }}</div>

                        <span v-if="Number(d.rating) > 0" class="rating">★ {{ Number(d.rating).toFixed(1) }}</span>
                    </div>

                    <Link :href="route('dealers.show', d.slug)" class="dealer-name">{{ d.name }}</Link>
                    <div class="mono dealer-meta">{{ (d.city ? t('city.' + d.city, d.city) : '') }} · {{ num(d.vehicles_count) }} {{ t('common.vehicles_word', 'vehicles') }}</div>

                    <div class="dealer-foot">
                        <span class="tag dealer-plan" :class="d.verified ? 'tag-accent' : 'tag-neutral'">
                            {{ d.verified ? t('common.verified', 'Verified') : d.package }}
                        </span>
                        <span v-if="d.founded_year" class="mono founded">{{ t('dlr.est', 'Est.') }} {{ d.founded_year }}</span>
                    </div>
                </div>
            </div>

            <p v-else class="text-muted empty">{{ t('dlr.empty', 'No dealers match your search.') }}</p>

            <Pagination :links="dealers.links" />
        </section>
    </MarketplaceLayout>
</template>

<style scoped>
/* Page header */
.head { background: var(--color-surface); }
.head-inner { display: grid; grid-template-columns: 1.3fr 1fr; gap: 40px; align-items: end; padding: 48px 32px; }
.head-title { font-size: clamp(38px, 4.4vw, 60px); line-height: 0.96; letter-spacing: -0.03em; margin: 16px 0 14px; }
.head-sub { font-size: 16px; max-width: 46ch; margin: 0; }
.search { display: flex; flex-direction: column; gap: 10px; }
.search-row { display: flex; gap: 8px; }
.search .input { flex: 1; min-width: 0; }
.search .btn { flex: none; }

/* Sections */
.section { padding: 48px 32px; }
.section-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--color-divider); padding-bottom: 12px; margin-bottom: 26px; }

/* Dealer grid */
.dealers-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
.dealer-card {
    display: flex; flex-direction: column;
    background: var(--color-card); border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-card); padding: 22px;
    transition: border-color .22s var(--ease), box-shadow .22s var(--ease), transform .22s var(--ease);
}
.dealer-card:hover { border-color: color-mix(in srgb, var(--color-text) 8%, transparent); box-shadow: var(--shadow-card-hover); transform: translateY(-4px); }
.dealer-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.dealer-logo { width: 64px; height: 64px; position: relative; overflow: hidden; background: var(--color-surface-2); border: 1px solid var(--color-hairline); border-radius: var(--radius-md); flex: none; box-shadow: var(--shadow-xs); }
.dealer-logo img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.dealer-logo-empty { display: grid; place-items: center; background: var(--color-text); color: var(--color-bg); font: 800 28px var(--font-heading); border-color: transparent; }
.rating { display: inline-flex; align-items: center; gap: 4px; font: 800 12px var(--font-heading); color: var(--color-accent-700); background: var(--color-accent-100); padding: 5px 10px; border-radius: var(--radius-pill); white-space: nowrap; }
.dealer-name { font: 800 17px/1.2 var(--font-heading); color: var(--color-text); margin: 18px 0 7px; }
.dealer-name:hover { color: var(--color-accent); }
.dealer-meta { color: var(--color-neutral-700); }
.dealer-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--color-hairline); }
.dealer-plan { text-transform: capitalize; }
.founded { color: var(--color-neutral-600); }
.empty { padding: 40px 0; text-align: center; }

@media (max-width: 1000px) {
    .head-inner { grid-template-columns: 1fr; }
    .dealers-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 620px) {
    .head-inner { padding: 36px 18px; }
    .section { padding: 36px 18px; }
    .dealers-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 460px) {
    .dealers-grid { grid-template-columns: 1fr; }
}
</style>
