<script setup>
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

defineProps({
    packages: {
        type: Array,
        default: () => [],
    },
    promotions: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="t('prc.head_title', 'Packages & pricing')" />
    <MarketplaceLayout>
        <!-- Header -->
        <section class="wrap price-head divider-x">
            <div class="mono mono-accent">{{ t('prc.kicker', 'For dealers') }}</div>
            <h1 class="price-title">{{ t('prc.title', 'Your whole inventory in one place.') }}</h1>
            <p class="text-muted price-sub">{{ t('prc.sub', 'Simple monthly plans for dealerships of every size. Start free, upgrade when your stock grows — no long contracts, cancel anytime.') }}</p>
        </section>

        <!-- Packages -->
        <section class="wrap section">
            <div class="pkg-grid">
                <div
                    v-for="pkg in packages"
                    :key="pkg.name"
                    class="pkg"
                    :class="{ 'pkg-hi': pkg.highlight }"
                >
                    <div v-if="pkg.highlight" class="pkg-badge">{{ t('prc.popular', 'Popular') }}</div>
                    <div class="pkg-top">
                        <div class="mono pkg-name">{{ pkg.name }}</div>
                        <div class="pkg-price">{{ pkg.price }}</div>
                        <div class="pkg-per">{{ t('pkg.' + pkg.name + '.per', pkg.per) }}</div>
                    </div>

                    <div class="pkg-divider"></div>

                    <ul class="pkg-feats">
                        <li v-for="feat in pkg.feats" :key="feat" class="pkg-feat">
                            <span class="pkg-dash">✓</span>
                            <span>{{ t('pkg.feat.' + feat, feat) }}</span>
                        </li>
                    </ul>

                    <Link
                        :href="route('register')"
                        class="btn btn-block btn-lg pkg-cta"
                        :class="pkg.highlight ? 'btn-primary' : 'btn-secondary'"
                    >
                        {{ t('pkg.' + pkg.name + '.cta', pkg.cta) }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Promotions -->
        <section class="promos">
            <div class="wrap promos-inner">
                <div class="mono promos-kicker">{{ t('prc.promos_kicker', 'Boost a single listing') }}</div>
                <h2 class="promos-title">{{ t('prc.promos_title', 'Promote a listing: Bump, Featured, Homepage.') }}</h2>
                <div class="promos-grid">
                    <div v-for="promo in promotions" :key="promo.key" class="promo">
                        <div class="mono promo-kicker">{{ t('promo.' + promo.key + '.kicker', promo.kicker) }}</div>
                        <div class="promo-name">{{ t('promo.' + promo.key + '.name', promo.name) }}</div>
                        <div class="promo-price">{{ promo.price }}</div>
                        <p class="promo-desc">{{ t('promo.' + promo.key + '.desc', promo.desc) }}</p>
                    </div>
                </div>
            </div>
        </section>
    </MarketplaceLayout>
</template>

<style scoped>
/* Header */
.price-head {
    padding: 56px 32px 44px;
}
.price-title {
    font-size: clamp(38px, 5vw, 68px);
    line-height: 0.96;
    letter-spacing: -0.03em;
    margin: 16px 0 16px;
    max-width: 16ch;
}
.price-sub {
    font-size: 17px;
    max-width: 52ch;
    margin: 0;
}

/* Packages */
.section {
    padding: 56px 32px 64px;
}
.pkg-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    align-items: stretch;
}
.pkg {
    position: relative;
    display: flex;
    flex-direction: column;
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-card);
    padding: 34px 30px;
    transition: transform .24s var(--ease), box-shadow .24s var(--ease), border-color .24s var(--ease);
}
.pkg:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-card-hover);
}
.pkg-hi {
    border-color: transparent;
    box-shadow: var(--shadow-lg), 0 0 0 2px var(--color-accent);
}
.pkg-hi:hover {
    transform: translateY(-4px) scale(1.005);
    box-shadow: var(--shadow-card-hover), 0 0 0 2px var(--color-accent);
}
.pkg-badge {
    position: absolute;
    top: -13px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--color-accent);
    color: #fff;
    font: 800 11px/1 var(--font-heading);
    letter-spacing: 0.09em;
    text-transform: uppercase;
    padding: 8px 16px;
    border-radius: var(--radius-pill);
    box-shadow: var(--shadow-accent);
}
.pkg-name {
    color: var(--color-accent);
    font-size: 12px;
}
.pkg-price {
    font: 900 46px/1 var(--font-heading);
    letter-spacing: -0.03em;
    margin: 16px 0 6px;
}
.pkg-per {
    font-size: 13px;
    color: color-mix(in srgb, var(--color-text) 55%, transparent);
}
.pkg-divider {
    height: 1px;
    background: var(--color-hairline);
    margin: 24px 0;
}
.pkg-feats {
    list-style: none;
    margin: 0 0 30px;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 13px;
    flex: 1;
}
.pkg-feat {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    font-size: 14px;
    line-height: 1.4;
}
.pkg-dash {
    flex: none;
    width: 20px;
    height: 20px;
    margin-top: 1px;
    display: grid;
    place-items: center;
    border-radius: var(--radius-pill);
    background: var(--color-accent-100);
    color: var(--color-accent-700);
    font-size: 11px;
    font-weight: 800;
}
.pkg-cta {
    margin-top: auto;
}

/* Promotions */
.promos {
    background: var(--color-accent);
    color: var(--color-bg);
}
.promos-inner {
    padding: 56px 32px;
}
.promos-kicker {
    color: color-mix(in srgb, #fff 80%, transparent);
}
.promos-title {
    color: var(--color-bg);
    font-size: clamp(28px, 3.4vw, 46px);
    letter-spacing: -0.02em;
    max-width: 22ch;
    margin: 14px 0 34px;
}
.promos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}
.promo {
    padding: 26px 24px 24px;
    border: 1px solid color-mix(in srgb, #fff 24%, transparent);
    border-radius: var(--radius-lg);
    background: color-mix(in srgb, #fff 10%, transparent);
    transition: background .2s var(--ease), transform .2s var(--ease);
}
.promo:hover {
    background: color-mix(in srgb, #fff 16%, transparent);
    transform: translateY(-3px);
}
.promo-kicker {
    color: color-mix(in srgb, #fff 78%, transparent);
}
.promo-name {
    font: 800 18px/1.1 var(--font-heading);
    margin: 12px 0 10px;
}
.promo-price {
    font: 800 40px/1 var(--font-heading);
    letter-spacing: -0.02em;
}
.promo-desc {
    margin: 12px 0 0;
    font-size: 14px;
    max-width: 32ch;
    color: color-mix(in srgb, var(--color-bg) 82%, transparent);
}

@media (max-width: 900px) {
    .pkg-grid {
        grid-template-columns: 1fr;
    }
    .promos-grid {
        grid-template-columns: 1fr;
    }
    .promo {
        padding: 24px 22px;
    }
}
</style>
