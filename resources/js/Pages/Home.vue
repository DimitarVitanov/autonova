<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import VehicleGlyph from '@/Components/VehicleGlyph.vue';
import BrandLogo from '@/Components/BrandLogo.vue';
import { eur, num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

// category slug → vehicle glyph key
const catGlyph = { cars: 'car', motorcycles: 'motorcycle', vans: 'van', trucks: 'truck', machinery: 'machinery', trailers: 'trailer' };

const props = defineProps({
    categories: Array,
    hero: Object,
    featured: Object,
    newest: Object,
    dealers: Array,
    brands: { type: Array, default: () => [] },
    stats: Object,
    options: Object,
    favoriteIds: Array,
});

// Body/sub types per category, each mapped to its vehicle glyph. Values match
// config('marketplace.body_types') so the filter links resolve.
const bodyTypesByCat = {
    cars: [
        { label: 'Hatchback', value: 'Hatchback', glyph: 'hatchback' },
        { label: 'SUV', value: 'SUV', glyph: 'suv' },
        { label: 'Sedan', value: 'Sedan', glyph: 'sedan' },
        { label: 'Estate', value: 'Estate', glyph: 'estate' },
        { label: 'Coupe', value: 'Coupe', glyph: 'coupe' },
        { label: 'Convertible', value: 'Convertible', glyph: 'convertible' },
        { label: 'Minivan', value: 'Minivan', glyph: 'minivan' },
        { label: 'Pickup', value: 'Pickup', glyph: 'pickup' },
    ],
    motorcycles: [
        { label: 'Sport', value: 'Sport', glyph: 'moto_sport' },
        { label: 'Naked', value: 'Naked', glyph: 'moto_naked' },
        { label: 'Cruiser', value: 'Cruiser', glyph: 'moto_cruiser' },
        { label: 'Touring', value: 'Touring', glyph: 'moto_touring' },
        { label: 'Enduro', value: 'Enduro', glyph: 'moto_enduro' },
        { label: 'Cross', value: 'Cross', glyph: 'moto_cross' },
        { label: 'Scooter', value: 'Scooter', glyph: 'scooter' },
        { label: 'Chopper', value: 'Chopper', glyph: 'moto_chopper' },
    ],
    vans: [
        { label: 'Panel van', value: 'Panel van', glyph: 'van' },
        { label: 'Combi', value: 'Combi', glyph: 'minibus' },
        { label: 'Minibus', value: 'Minibus', glyph: 'minibus' },
        { label: 'Box', value: 'Box', glyph: 'van' },
        { label: 'Pickup', value: 'Pickup', glyph: 'pickup' },
        { label: 'Chassis cab', value: 'Chassis cab', glyph: 'tractorunit' },
    ],
    trucks: [
        { label: 'Tractor unit', value: 'Tractor unit', glyph: 'tractorunit' },
        { label: 'Box truck', value: 'Box truck', glyph: 'truck' },
        { label: 'Tipper', value: 'Tipper', glyph: 'tipper' },
        { label: 'Flatbed', value: 'Flatbed', glyph: 'flatbedtruck' },
        { label: 'Refrigerated', value: 'Refrigerated', glyph: 'truck' },
        { label: 'Chassis', value: 'Chassis', glyph: 'tractorunit' },
    ],
    machinery: [
        { label: 'Tractor', value: 'Tractor', glyph: 'machinery' },
        { label: 'Excavator', value: 'Excavator', glyph: 'excavator' },
        { label: 'Loader', value: 'Loader', glyph: 'loader' },
        { label: 'Forklift', value: 'Forklift', glyph: 'forklift' },
        { label: 'Combine', value: 'Combine', glyph: 'machinery' },
        { label: 'Bulldozer', value: 'Bulldozer', glyph: 'loader' },
    ],
    trailers: [
        { label: 'Curtainsider', value: 'Curtainsider', glyph: 'trailer' },
        { label: 'Flatbed', value: 'Flatbed', glyph: 'flatbedtrailer' },
        { label: 'Tipper', value: 'Tipper', glyph: 'trailer' },
        { label: 'Car transporter', value: 'Car transporter', glyph: 'flatbedtrailer' },
        { label: 'Caravan', value: 'Caravan', glyph: 'caravan' },
        { label: 'Camper', value: 'Camper', glyph: 'caravan' },
    ],
};
const bodyTypes = computed(() => bodyTypesByCat[search.category] ?? bodyTypesByCat.cars);

const favSet = computed(() => new Set(props.favoriteIds ?? []));

const search = reactive({ category: 'cars', make_id: '', price_max: '', city: '' });
const makes = ref(props.options.makes ?? []);

watch(() => search.category, async (slug) => {
    search.make_id = '';
    const { data } = await window.axios.get(route('api.makes'), { params: { category: slug } });
    makes.value = data;
});

const submit = () => {
    const params = Object.fromEntries(Object.entries(search).filter(([, v]) => v !== '' && v !== null));
    router.get(route('vehicles.index'), params);
};

// Custom category picker (native <select> can't show icons)
const catOpen = ref(false);
const catField = ref(null);
const categoryName = (slug) => t('cat.' + slug, props.categories.find((c) => c.slug === slug)?.name_plural ?? slug);
const selectCat = (slug) => { search.category = slug; catOpen.value = false; };
const onDocClick = (e) => { if (catField.value && !catField.value.contains(e.target)) catOpen.value = false; };
onMounted(() => document.addEventListener('click', onDocClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocClick));

const featuredList = computed(() => props.featured?.data ?? []);
const newestList = computed(() => props.newest?.data ?? []);
</script>

<template>
    <Head :title="t('hero.title_1', 'Find your next') + ' ' + t('hero.title_2', 'vehicle.')" />
    <MarketplaceLayout>
        <!-- ===== Showroom hero (dark) ===== -->
        <section class="hero">
            <div class="hero-glow" aria-hidden="true"></div>
            <div class="wrap hero-grid">
                <div class="hero-copy">
                    <div class="eyebrow"><span class="dot"></span> {{ t('hero.eyebrow', 'MK · Vehicle marketplace · Est. 2026') }}</div>
                    <h1 class="hero-title">{{ t('hero.title_1', 'Find your next') }}<br /><span class="accent">{{ t('hero.title_2', 'vehicle.') }}</span></h1>
                    <p class="hero-sub">{{ t('hero.sub', 'Cars, motorcycles, vans, trucks, machinery and trailers — from verified dealers and private sellers. Real photos, no duplicate listings.') }}</p>

                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-num">{{ num(stats.vehicles) }}</div>
                            <div class="hero-stat-label">{{ t('hero.stat_vehicles', 'Vehicles listed') }}</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">{{ num(stats.dealers) }}</div>
                            <div class="hero-stat-label">{{ t('hero.stat_dealers', 'Verified dealers') }}</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">{{ num(stats.new_today) }}</div>
                            <div class="hero-stat-label">{{ t('hero.stat_new', 'New today') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Floating glass search -->
                <div class="hero-search-wrap">
                <form class="search-bar" @submit.prevent="submit">
                    <div ref="catField" class="search-field cat-field">
                        <span class="search-label">{{ t('search.category', 'Category') }}</span>
                        <button type="button" class="cat-btn" @click="catOpen = !catOpen">
                            <VehicleGlyph :name="catGlyph[search.category] || 'car'" :size="30" class="cat-btn-ic" />
                            <span class="cat-btn-txt">{{ categoryName(search.category) }}</span>
                            <svg class="cat-caret" width="11" height="7" viewBox="0 0 12 8"><path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.9" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                        <div v-if="catOpen" class="cat-menu">
                            <button
                                v-for="c in categories" :key="c.id" type="button"
                                class="cat-opt" :class="{ active: c.slug === search.category }"
                                @click="selectCat(c.slug)"
                            >
                                <VehicleGlyph :name="catGlyph[c.slug] || 'car'" :size="34" />
                                <span>{{ t('cat.' + c.slug, c.name_plural) }}</span>
                            </button>
                        </div>
                    </div>
                    <label class="search-field">
                        <span class="search-label">{{ t('search.make', 'Make') }}</span>
                        <select v-model="search.make_id" class="search-select">
                            <option value="">{{ t('common.any_make', 'Any make') }}</option>
                            <option v-for="m in makes" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </label>
                    <label class="search-field">
                        <span class="search-label">{{ t('search.price', 'Max price (EUR)') }}</span>
                        <input v-model="search.price_max" class="search-select" inputmode="numeric" :placeholder="t('search.price_ph', 'e.g. 15 000')" />
                    </label>
                    <label class="search-field">
                        <span class="search-label">{{ t('search.city', 'City') }}</span>
                        <select v-model="search.city" class="search-select">
                            <option value="">{{ t('common.all_macedonia', 'All Macedonia') }}</option>
                            <option v-for="c in options.cities" :key="c" :value="c">{{ t('city.' + c, c) }}</option>
                        </select>
                    </label>
                    <button type="submit" class="search-submit">
                        <span>{{ t('common.search', 'Search') }}</span>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m0 0l-6-6m6 6l-6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </form>

                <div class="hero-chips">
                    <Link v-for="c in categories" :key="c.id" :href="route('vehicles.index', { category: c.slug })" class="hero-chip">
                        <VehicleGlyph :name="catGlyph[c.slug] || 'car'" :size="30" class="hero-chip-ic" />
                        {{ t('cat.' + c.slug, c.name_plural) }}
                        <span class="hero-chip-count">{{ num(c.count) }}</span>
                    </Link>
                </div>

                <div class="hero-bt-label mono">{{ t('hero.bodytype_label', 'Or jump straight to a body type') }}</div>
                <div class="hero-bodytypes">
                    <Link
                        v-for="b in bodyTypes" :key="b.value"
                        :href="route('vehicles.index', { category: search.category, body_type: b.value })"
                        class="hbt"
                    >
                        <VehicleGlyph :name="b.glyph" :size="66" />
                        <span class="hbt-label">{{ t('body.' + b.value, b.label) }}</span>
                    </Link>
                </div>
                </div>

                <Link v-if="hero" :href="route('vehicles.show', hero.slug)" class="showcase">
                    <div class="showcase-spot" aria-hidden="true"></div>
                    <div class="showcase-frame">
                        <img v-if="hero.cover_url" :src="hero.cover_url" :alt="hero.title" />
                        <div class="showcase-caption">
                            <span class="showcase-kicker">{{ t('hero.pick', 'Pick of the week') }}</span>
                            <span class="showcase-title">{{ hero.title }}</span>
                            <span class="showcase-price">{{ eur(hero.price) }}</span>
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <!-- ===== Featured ===== -->
        <section class="wrap section">
            <div class="section-head">
                <div>
                    <div class="mono mono-accent">{{ t('home.featured_kicker', 'Handpicked') }}</div>
                    <h2 class="section-title">{{ t('home.featured_title', 'Featured vehicles') }}</h2>
                </div>
                <Link :href="route('vehicles.index')" class="section-link">{{ t('common.all_prefix', 'All') }} {{ num(stats.vehicles) }} {{ t('common.vehicles_word', 'vehicles') }} →</Link>
            </div>
            <div class="grid-cards">
                <VehicleCard v-for="v in featuredList" :key="v.id" :vehicle="v" :favorited="favSet.has(v.id)" />
            </div>
        </section>

        <!-- ===== Explore: body type + brands (dark showroom band) ===== -->
        <section class="explore">
            <div class="explore-glow" aria-hidden="true"></div>
            <div class="wrap explore-inner">
                <div class="explore-head">
                    <div>
                        <div class="mono mono-accent">{{ t('home.brands_kicker', 'The badges you know') }}</div>
                        <h2 class="explore-title">{{ t('home.brands_title', 'Popular brands') }}</h2>
                    </div>
                    <Link :href="route('vehicles.index', { category: 'cars' })" class="explore-link">{{ t('home.all_brands', 'All brands') }} →</Link>
                </div>
                <div class="brand-grid">
                    <Link
                        v-for="b in brands" :key="b.id"
                        :href="route('vehicles.index', { category: 'cars', make_id: b.id })"
                        class="brand-tile"
                    >
                        <div class="brand-logo"><BrandLogo :brand="b.logo" :size="42" /></div>
                        <div class="brand-name">{{ b.name }}</div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ===== Browse by category ===== -->
        <section class="wrap section cats-section">
            <div class="section-head">
                <div>
                    <div class="mono mono-accent">{{ t('home.cats_kicker', 'Every kind of vehicle') }}</div>
                    <h2 class="section-title">{{ t('home.cats_title', 'Browse by category') }}</h2>
                </div>
            </div>
            <div class="cats-grid">
                <Link v-for="c in categories" :key="c.id" :href="route('vehicles.index', { category: c.slug })" class="cat-tile">
                    <div class="cat-icon"><CategoryIcon :name="c.icon" :size="24" /></div>
                    <div class="cat-body">
                        <div class="cat-name">{{ t('cat.' + c.slug, c.name_plural) }}</div>
                        <div class="mono cat-hint">{{ t('cathint.' + c.slug, c.hint) }}</div>
                    </div>
                    <div class="cat-count">{{ num(c.count) }}</div>
                </Link>
            </div>
        </section>

        <!-- ===== Dealer CTA ===== -->
        <section class="wrap cta-section">
            <div class="cta">
                <div class="cta-copy">
                    <div class="mono cta-kicker">{{ t('home.cta_kicker', 'For dealerships') }}</div>
                    <h2 class="cta-title">{{ t('home.cta_title', 'Put your whole inventory in one place.') }}</h2>
                    <p class="cta-text">{{ t('home.cta_text', 'Dealer profile, XML/CSV import, per-listing analytics and leads straight to your inbox.') }}</p>
                </div>
                <div class="cta-actions">
                    <Link :href="route('pricing')" class="btn btn-light btn-lg">{{ t('home.cta_packages', 'See packages') }} →</Link>
                    <Link :href="route('dealers.index')" class="btn btn-glass btn-lg">{{ t('home.cta_dealers', 'Browse dealers') }}</Link>
                </div>
            </div>
        </section>

        <!-- ===== Newest ===== -->
        <section class="wrap section">
            <div class="section-head">
                <div>
                    <div class="mono mono-accent">{{ t('home.new_kicker', 'Just added') }}</div>
                    <h2 class="section-title">{{ t('home.new_title', 'Fresh listings') }}</h2>
                </div>
                <Link :href="route('vehicles.index', { sort: 'newest' })" class="section-link">{{ t('home.browse_newest', 'Browse newest') }} →</Link>
            </div>
            <div class="grid-cards">
                <VehicleCard v-for="v in newestList" :key="v.id" :vehicle="v" :favorited="favSet.has(v.id)" />
            </div>
        </section>

        <!-- ===== Dealers ===== -->
        <section class="wrap section dealers-sec">
            <div class="section-head">
                <div>
                    <div class="mono mono-accent">{{ t('home.dealers_kicker', 'Trusted sellers') }}</div>
                    <h2 class="section-title">{{ t('home.dealers_title', 'Verified dealers') }}</h2>
                </div>
                <Link :href="route('dealers.index')" class="section-link">{{ t('home.all_dealers', 'All dealers') }} →</Link>
            </div>
            <div class="dealers-grid">
                <Link v-for="d in dealers" :key="d.id" :href="route('dealers.show', d.slug)" class="dealer-tile">
                    <div class="dealer-logo">{{ d.name.charAt(0) }}</div>
                    <div class="dealer-name">{{ d.name }}</div>
                    <div class="mono dealer-meta">{{ (d.city ? t('city.' + d.city, d.city) : '') }} · {{ num(d.vehicles_count) }} {{ t('common.vehicles_word', 'vehicles') }}</div>
                    <span class="tag tag-accent dealer-plan">{{ d.verified ? t('common.verified', 'Verified') : d.package }}</span>
                </Link>
            </div>
        </section>
    </MarketplaceLayout>
</template>

<style scoped>
.accent { color: var(--color-accent); }

/* ===== Hero ===== */
.hero {
    position: relative;
    background: var(--color-ink);
    color: var(--color-on-ink);
    padding-bottom: 40px;
}
.hero-glow {
    position: absolute; inset: 0; pointer-events: none; overflow: hidden;
    background:
        radial-gradient(60% 80% at 82% 8%, color-mix(in srgb, var(--color-accent) 26%, transparent), transparent 60%),
        radial-gradient(50% 60% at 6% 0%, rgba(255, 255, 255, 0.06), transparent 60%);
}
.hero-grid {
    position: relative;
    display: grid;
    grid-template-columns: 1.02fr 1fr;
    grid-template-areas: "copy showcase" "search search";
    gap: 40px 48px; align-items: center;
    padding: 60px 32px 40px;
}
.hero-copy { grid-area: copy; min-width: 0; }
.eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.16em; text-transform: uppercase;
    color: var(--color-on-ink-soft);
}
.eyebrow .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--color-accent); box-shadow: 0 0 12px 2px color-mix(in srgb, var(--color-accent) 70%, transparent); }
.hero-title {
    font-size: clamp(50px, 6vw, 92px); line-height: 0.94; letter-spacing: -0.04em;
    margin: 20px 0 20px; color: #fff;
}
.hero-sub { font-size: 17px; line-height: 1.6; max-width: 46ch; margin: 0; color: var(--color-on-ink-soft); }
.hero-stats { display: flex; gap: 44px; margin-top: 40px; }
.hero-stat-num { font: 800 36px/1 var(--font-heading); letter-spacing: -0.02em; color: #fff; }
.hero-stat-label { margin-top: 8px; font: 600 12px var(--font-heading); color: var(--color-on-ink-muted); }

/* Spotlit showcase car */
.showcase { grid-area: showcase; position: relative; display: block; min-width: 0; }
.showcase-spot {
    position: absolute; inset: -14% -8% -20% -8%;
    background: radial-gradient(52% 60% at 50% 42%, rgba(255,255,255,0.14), transparent 70%);
    filter: blur(6px); pointer-events: none;
}
.showcase-frame {
    position: relative; border-radius: var(--radius-xl); overflow: hidden;
    box-shadow: var(--shadow-ink);
    border: 1px solid var(--color-ink-line-2);
    aspect-ratio: 4 / 3;
    transition: transform .4s var(--ease), box-shadow .4s var(--ease);
}
.showcase-frame > img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s var(--ease); }
.showcase:hover .showcase-frame { transform: translateY(-6px); }
.showcase:hover .showcase-frame > img { transform: scale(1.05); }
.showcase-caption {
    position: absolute; left: 16px; right: 16px; bottom: 16px; z-index: 2;
    display: flex; align-items: baseline; flex-wrap: wrap; gap: 4px 12px;
    padding: 14px 18px; border-radius: var(--radius-md);
    background: color-mix(in srgb, var(--color-ink) 55%, transparent);
    backdrop-filter: blur(10px); border: 1px solid var(--color-ink-line-2);
}
.showcase-kicker { flex: 1 0 100%; font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--color-accent-300); }
.showcase-title { font: 800 18px var(--font-heading); color: #fff; letter-spacing: -0.01em; }
.showcase-price { font: 800 18px var(--font-heading); color: #fff; margin-left: auto; }

/* Floating glass search */
.hero-search-wrap { grid-area: search; position: relative; padding: 0; z-index: 5; min-width: 0; }
.search-bar {
    display: flex; flex-wrap: wrap; align-items: stretch; gap: 4px;
    padding: 8px; border-radius: var(--radius-lg);
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid var(--color-ink-line-2);
    backdrop-filter: blur(14px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
}
.search-field {
    flex: 1 1 180px; min-width: 0; padding: 11px 18px;
    display: flex; flex-direction: column; gap: 6px; border-radius: var(--radius-md);
    transition: background .15s var(--ease);
}
.search-field:hover { background: rgba(255, 255, 255, 0.06); }
.search-field + .search-field { position: relative; }
.search-field + .search-field::before { content: ""; position: absolute; left: -2px; top: 12px; bottom: 12px; width: 1px; background: var(--color-ink-line); }
.search-label { font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-on-ink-muted); }
.search-select {
    border: 0; background: transparent; color: #fff; width: 100%; padding: 0;
    font: 800 15px var(--font-heading); letter-spacing: -0.01em; appearance: none; cursor: pointer;
}
.search-select:focus-visible { outline: none; }
.search-select option { color: var(--color-text); }
.search-select::placeholder { color: var(--color-on-ink-muted); font-weight: 700; }
.search-submit {
    flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    border: 0; background: var(--color-accent); color: #fff; cursor: pointer;
    padding: 0 30px; min-height: 62px; border-radius: var(--radius-md);
    font: 800 15px var(--font-heading); box-shadow: var(--shadow-accent);
    transition: background .16s var(--ease), box-shadow .16s var(--ease), transform .16s var(--ease);
}
.search-submit:hover { background: var(--color-accent-600); box-shadow: 0 12px 30px rgba(236, 48, 19, 0.45); transform: translateY(-1px); }

/* Custom category picker (icons in options) */
.cat-field { position: relative; }
.cat-btn {
    display: flex; align-items: center; gap: 9px; width: 100%; padding: 0;
    background: transparent; border: 0; cursor: pointer; text-align: left;
    color: #fff; font: 800 15px var(--font-heading); letter-spacing: -0.01em;
}
.cat-btn-ic { flex: none; }
.cat-btn-txt { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cat-caret { color: var(--color-on-ink-muted); flex: none; }
.cat-menu {
    position: absolute; top: calc(100% + 14px); left: 6px; z-index: 60; min-width: 232px;
    max-height: 320px; overflow-y: auto;
    background: var(--color-ink-2); border: 1px solid var(--color-ink-line-2);
    border-radius: var(--radius-md); box-shadow: var(--shadow-ink);
    padding: 6px; display: flex; flex-direction: column; gap: 2px;
}
.cat-opt {
    display: flex; align-items: center; gap: 11px; width: 100%; padding: 8px 10px;
    background: transparent; border: 0; cursor: pointer; text-align: left;
    color: var(--color-on-ink-soft); font: 700 14px var(--font-heading);
    border-radius: var(--radius-sm);
    transition: background .14s var(--ease), color .14s var(--ease);
}
.cat-opt:hover { background: rgba(255, 255, 255, 0.07); color: #fff; }
.cat-opt.active { color: #fff; background: color-mix(in srgb, var(--color-accent) 16%, transparent); }

.hero-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
.hero-chip {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: var(--radius-pill);
    background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-ink-line);
    color: var(--color-on-ink-soft); font: 700 13px var(--font-heading);
    transition: background .15s var(--ease), border-color .15s var(--ease), color .15s var(--ease);
}
.hero-chip:hover { background: rgba(255, 255, 255, 0.11); border-color: var(--color-ink-line-2); color: #fff; }
.hero-chip-count { font-size: 11px; color: var(--color-on-ink-muted); }

/* Body-type quick-picks (AutoScout-style icon row) */
.hero-bt-label { margin: 24px 0 10px; color: var(--color-on-ink-muted); }
.hero-bodytypes { display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px; }
.hbt {
    display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 7px;
    padding: 14px 8px 12px; border-radius: var(--radius-md);
    background: rgba(255, 255, 255, 0.035); border: 1px solid var(--color-ink-line);
    color: #fff;
    transition: transform .2s var(--ease), box-shadow .2s var(--ease), border-color .2s var(--ease), background .2s var(--ease);
}
.hbt :deep(svg) { transition: transform .3s var(--ease); }
.hbt-label { font: 700 12px var(--font-heading); color: var(--color-on-ink-soft); letter-spacing: -0.01em; }
.hbt:hover {
    transform: translateY(-3px); background: rgba(255, 255, 255, 0.06);
    border-color: color-mix(in srgb, var(--color-accent) 55%, transparent);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4), 0 0 0 1px color-mix(in srgb, var(--color-accent) 28%, transparent);
    color: #fff;
}
.hbt:hover :deep(svg) { transform: scale(1.06); }
.hbt:hover .hbt-label { color: #fff; }

/* ===== Sections ===== */
.section { padding: 60px 32px; }
.section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 30px; }
.section-title { margin: 6px 0 0; letter-spacing: -0.025em; }
.section-link { font: 700 13px var(--font-heading); white-space: nowrap; }
.grid-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }

/* ===== Explore band (dark) ===== */
.explore {
    position: relative; overflow: hidden;
    background: var(--color-ink); color: var(--color-on-ink);
    margin: 24px 0; padding: 56px 32px 60px;
}
.explore-glow {
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(45% 70% at 92% 6%, color-mix(in srgb, var(--color-accent) 22%, transparent), transparent 60%),
        radial-gradient(45% 70% at 4% 100%, color-mix(in srgb, var(--color-accent) 12%, transparent), transparent 60%);
}
.explore-inner { position: relative; }
.explore-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 26px; }
.explore-title { margin: 6px 0 0; color: #fff; letter-spacing: -0.025em; }
.explore-link { font: 700 13px var(--font-heading); color: var(--color-accent-300); white-space: nowrap; }
.explore-link:hover { color: #fff; }

.brand-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
.brand-tile {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
    padding: 22px 12px; text-align: center;
    background: rgba(255, 255, 255, 0.04); border: 1px solid var(--color-ink-line);
    border-radius: var(--radius-lg); color: var(--color-on-ink);
    transition: transform .2s var(--ease), box-shadow .2s var(--ease), border-color .2s var(--ease), background .2s var(--ease);
}
.brand-logo { color: #fff; opacity: 0.88; height: 42px; display: grid; place-items: center; transition: opacity .2s var(--ease); }
.brand-name { font: 700 13px var(--font-heading); color: var(--color-on-ink-soft); }
.brand-tile:hover {
    transform: translateY(-4px); background: rgba(255, 255, 255, 0.07);
    border-color: color-mix(in srgb, var(--color-accent) 55%, transparent);
    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.4), 0 0 0 1px color-mix(in srgb, var(--color-accent) 30%, transparent);
}
.brand-tile:hover .brand-logo { opacity: 1; }
.brand-tile:hover .brand-name { color: #fff; }

/* Category tiles */
.cats-section { padding-top: 12px; }
.cats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.cat-tile {
    display: flex; align-items: center; gap: 16px;
    padding: 18px 20px; color: var(--color-text);
    background: var(--color-card); border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    transition: transform .2s var(--ease), box-shadow .2s var(--ease), border-color .2s var(--ease);
}
.cat-tile:hover { color: var(--color-text); transform: translateY(-4px); box-shadow: var(--shadow-card-hover); border-color: color-mix(in srgb, var(--color-text) 8%, transparent); }
.cat-icon { flex: none; color: var(--color-accent); width: 48px; height: 48px; display: grid; place-items: center; background: var(--color-accent-100); border-radius: var(--radius-md); }
.cat-body { flex: 1; min-width: 0; }
.cat-name { font: 800 16px/1.2 var(--font-heading); }
.cat-hint { margin-top: 5px; }
.cat-count { flex: none; font: 800 22px/1 var(--font-heading); color: var(--color-text); letter-spacing: -0.02em; }

/* CTA */
.cta-section { padding: 8px 32px 60px; }
.cta {
    position: relative; overflow: hidden;
    display: grid; grid-template-columns: 1.4fr 1fr; gap: 32px; align-items: center;
    background:
        radial-gradient(90% 180% at 100% 0%, color-mix(in srgb, #fff 20%, transparent), transparent 50%),
        linear-gradient(120deg, var(--color-accent-700), var(--color-accent) 60%, var(--color-accent-500));
    color: #fff; padding: 48px 52px; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);
}
.cta-kicker { color: rgba(255, 255, 255, 0.75); margin-bottom: 12px; }
.cta-title { margin: 0; font-size: clamp(28px, 3.2vw, 42px); line-height: 1.04; letter-spacing: -0.025em; max-width: 18ch; }
.cta-text { margin: 14px 0 0; opacity: 0.94; max-width: 44ch; }
.cta-actions { display: flex; flex-direction: column; gap: 12px; align-items: stretch; }

/* Dealers */
.dealers-sec { padding-top: 12px; }
.dealers-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.dealer-tile {
    padding: 22px 20px; color: var(--color-text); display: block;
    background: var(--color-card); border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    transition: transform .2s var(--ease), box-shadow .2s var(--ease);
}
.dealer-tile:hover { transform: translateY(-4px); box-shadow: var(--shadow-card-hover); }
.dealer-logo { width: 48px; height: 48px; display: grid; place-items: center; background: var(--color-ink); color: #fff; font: 800 20px var(--font-heading); border-radius: var(--radius-md); }
.dealer-name { font: 800 16px/1.2 var(--font-heading); margin: 14px 0 8px; }
.dealer-meta { color: var(--color-neutral-600); }
.dealer-plan { margin-top: 14px; text-transform: capitalize; }

@media (max-width: 1040px) {
    /* Mobile order: copy → search → image */
    .hero-grid { grid-template-columns: 1fr; grid-template-areas: "copy" "search" "showcase"; gap: 30px; padding-top: 46px; }
    .showcase { max-width: 560px; width: 100%; margin: 0 auto; }
    .grid-cards, .dealers-grid { grid-template-columns: repeat(2, 1fr); }
    .cats-grid { grid-template-columns: repeat(2, 1fr); }
    .brand-grid { grid-template-columns: repeat(4, 1fr); }
    .hero-bodytypes { grid-template-columns: repeat(4, 1fr); }
    .cta { grid-template-columns: 1fr; padding: 40px; }
}
@media (max-width: 620px) {
    .hero-grid { padding: 32px 18px 26px; gap: 22px; }
    .section, .cats-section, .cta-section, .explore { padding-left: 18px; padding-right: 18px; }
    .hero-title { font-size: clamp(26px, 7.4vw, 40px); overflow-wrap: break-word; word-break: break-word; }
    .hero-sub { font-size: 15px; }
    .hero-stats { gap: 12px 16px; flex-wrap: wrap; }
    .hero-stat { flex: 1 1 28%; min-width: 0; }
    .hero-stat-num { font-size: 27px; }
    .hero-stat-label { font-size: 11px; }
    .search-field { flex-basis: 100%; }
    .search-field + .search-field::before { display: none; }
    .search-submit { flex-basis: 100%; margin-top: 4px; }
    .grid-cards, .dealers-grid { grid-template-columns: 1fr 1fr; }
    .cats-grid { grid-template-columns: 1fr; } /* horizontal tiles: single column on phones so the count never clips */
    .hero-chips { display: none; }
    .hero-bt-label { margin-top: 18px; }
    .hero-bodytypes { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .brand-grid { grid-template-columns: repeat(3, 1fr); }
    .cat-menu { min-width: min(280px, calc(100vw - 40px)); }
    .cta-actions { flex-direction: column; }
    .section-head { flex-wrap: wrap; }
}
@media (max-width: 420px) {
    .grid-cards, .dealers-grid, .cats-grid { grid-template-columns: 1fr; }
    .hero-bodytypes { grid-template-columns: repeat(2, 1fr); }
    .brand-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
