<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import FilterSidebar from '@/Components/FilterSidebar.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    vehicles: Object,
    filters: Object,
    options: Object,
    resultCount: Number,
    favoriteIds: Array,
});

const page = usePage();
const favSet = computed(() => new Set(props.favoriteIds ?? []));
const view = ref(props.filters.view === 'list' ? 'list' : 'grid');
const q = ref(props.filters.q ?? '');
const filtersOpen = ref(false);

const heading = computed(() => {
    const makeName = props.options.makes?.find((m) => m.id == props.filters.make_id)?.name;
    const modelName = props.options.models?.find((m) => m.id == props.filters.car_model_id)?.name;
    const cat = props.filters.category;
    const catName = cat ? t('cat.' + cat, props.options.categories?.find((c) => c.slug === cat)?.name_plural) : null;
    return [makeName, modelName].filter(Boolean).join(' ') || catName || t('idx.all_vehicles', 'All vehicles');
});

const baseParams = () => {
    const { view: _v, ...rest } = props.filters;
    return { ...rest };
};

const navigate = (overrides) => {
    const params = { ...baseParams(), ...overrides };
    Object.keys(params).forEach((k) => (params[k] === '' || params[k] == null) && delete params[k]);
    router.get(route('vehicles.index'), params, { preserveScroll: true, preserveState: false });
};

const onApply = (params) => {
    params.view = view.value;
    router.get(route('vehicles.index'), params, { preserveScroll: true });
    filtersOpen.value = false;
};
const onSort = (e) => navigate({ sort: e.target.value, view: view.value });
const submitSearch = () => navigate({ q: q.value, view: view.value });

const saveSearch = (params) => {
    if (!page.props.auth.user) { router.visit(route('login')); return; }
    const title = window.prompt(t('idx.save_prompt', 'Name this saved search'), heading.value);
    if (!title) return;
    const { sort, view: _v, ...clean } = params;
    router.post(route('saved-searches.store'), { title, filters: clean }, { preserveScroll: true });
};

const list = computed(() => props.vehicles.data ?? []);
const pageLinks = computed(() => props.vehicles.meta?.links ?? []);
</script>

<template>
    <Head :title="`${heading} — ${t('idx.search_title', 'search')}`" />
    <MarketplaceLayout>
        <div class="results wrap-narrow">
            <!-- Breadcrumb -->
            <div class="crumb text-muted">
                <Link :href="route('home')">{{ t('common.home', 'Home') }}</Link> / <Link :href="route('vehicles.index')">{{ t('nav.vehicles', 'Vehicles') }}</Link> / {{ heading }}
            </div>

            <div class="results-grid">
                <!-- Sidebar -->
                <div class="sidebar-col" :class="{ open: filtersOpen }">
                    <FilterSidebar :filters="filters" :options="options" @apply="onApply" @save="saveSearch" />
                </div>

                <!-- Results -->
                <section class="results-main">
                    <form class="searchbar" @submit.prevent="submitSearch">
                        <input v-model="q" class="input" :placeholder="t('idx.search_ph', 'Search make, model or keyword…')" />
                        <button class="btn btn-secondary" type="submit">{{ t('common.search', 'Search') }}</button>
                        <button class="btn btn-secondary filters-toggle" type="button" @click="filtersOpen = !filtersOpen">{{ t('idx.filters', 'Filters') }}</button>
                    </form>

                    <div class="results-head">
                        <h4 class="results-title">{{ heading }} <span class="text-muted results-count">· {{ num(resultCount) }} {{ t('common.vehicles_word', 'vehicles') }}</span></h4>
                        <div class="results-tools">
                            <select class="input sort-select" :value="filters.sort || 'newest'" @change="onSort">
                                <option v-for="opt in options.sorts" :key="opt.value" :value="opt.value">{{ t('sort.' + opt.value, opt.label) }}</option>
                            </select>
                            <div class="seg">
                                <label class="seg-opt"><input type="radio" value="grid" v-model="view" />{{ t('idx.grid', 'Grid') }}</label>
                                <label class="seg-opt"><input type="radio" value="list" v-model="view" />{{ t('idx.list', 'List') }}</label>
                            </div>
                        </div>
                    </div>

                    <div v-if="list.length" :class="view === 'grid' ? 'cards-grid' : 'cards-list'">
                        <VehicleCard v-for="v in list" :key="v.id" :vehicle="v" :favorited="favSet.has(v.id)" :layout="view" />
                    </div>
                    <div v-else class="empty panel-surface">
                        <h4>{{ t('idx.empty_title', 'No vehicles match your filters') }}</h4>
                        <p class="text-muted">{{ t('idx.empty_text', 'Try widening the price range, removing a filter, or a different category.') }}</p>
                    </div>

                    <Pagination :links="pageLinks" />
                </section>
            </div>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.results { padding: 22px 24px 56px; }
.crumb { font-size: 12px; margin-bottom: 16px; }
.results-grid { display: grid; grid-template-columns: 280px 1fr; gap: 32px; align-items: start; }
.sidebar-col { position: sticky; top: 84px; }
.results-main { min-width: 0; }
.searchbar { display: flex; gap: 8px; margin-bottom: 18px; }
.searchbar .input { flex: 1; }
.filters-toggle { display: none; }
.results-head {
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    background: var(--color-card); border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    padding: 14px 18px; margin-bottom: 22px;
}
.results-title { margin: 0; }
.results-count { font-weight: 400; }
.results-tools { margin-left: auto; display: flex; gap: 12px; align-items: center; }
.sort-select { width: auto; }
.cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.cards-list { display: flex; flex-direction: column; gap: 14px; }
.empty { padding: 48px 32px; text-align: center; box-shadow: var(--shadow-sm); }

@media (max-width: 1100px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 860px) {
    .results-grid { grid-template-columns: 1fr; }
    .sidebar-col { position: static; display: none; }
    .sidebar-col.open { display: block; margin-bottom: 24px; }
    .filters-toggle { display: inline-flex; }
}
@media (max-width: 560px) {
    .cards-grid { grid-template-columns: 1fr; }
    /* sort + view toggle take their own row and shrink, instead of pushing the page wider */
    .results-tools { margin-left: 0; width: 100%; min-width: 0; }
    .sort-select { flex: 1; min-width: 0; }
    .results-tools .seg { flex: none; }
}
</style>
