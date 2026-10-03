<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    vehicles: { type: Object, default: () => ({ data: [] }) },
    savedSearches: { type: Array, default: () => [] },
    favoriteIds: { type: Array, default: () => [] },
});

const favSet = computed(() => new Set(props.favoriteIds ?? []));
const favorites = computed(() => props.vehicles?.data ?? []);

const removeSearch = (s) => {
    if (confirm(t('saved.confirm_delete', 'Delete saved search “:title”?').replace(':title', s.title))) {
        router.delete(route('saved-searches.destroy', s.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="t('nav.saved', 'Saved')" />
    <MarketplaceLayout>
        <div class="wrap saved">
            <div class="saved-head">
                <div class="mono mono-accent">{{ t('saved.kicker', 'Your shortlist') }}</div>
                <h2>{{ t('nav.saved', 'Saved') }}</h2>
            </div>

            <!-- Saved searches -->
            <section class="saved-section">
                <div class="section-bar">
                    <h4>{{ t('saved.searches', 'Saved searches') }}</h4>
                    <span class="mono">{{ savedSearches.length }} {{ t('saved.count', 'saved') }}</span>
                </div>

                <div v-if="savedSearches.length" class="search-grid">
                    <div v-for="s in savedSearches" :key="s.id" class="search-card panel">
                        <div class="search-top">
                            <div class="search-title">{{ s.title }}</div>
                            <button type="button" class="search-del" :aria-label="t('saved.delete_search', 'Delete saved search')" @click="removeSearch(s)">×</button>
                        </div>
                        <div class="search-summary text-muted">{{ s.summary === 'All vehicles' ? t('idx.all_vehicles', 'All vehicles') : s.summary }}</div>
                        <div class="search-foot">
                            <span v-if="s.new_count > 0" class="tag tag-accent">{{ s.new_count }} {{ t('saved.new', 'new') }}</span>
                            <span v-else class="mono">{{ s.alerts ? t('saved.alerts_on', 'Alerts on') : t('saved.no_new', 'No new matches') }}</span>
                            <Link :href="route('vehicles.index', s.filters)" class="btn btn-secondary btn-sm">{{ t('saved.open', 'Open') }} →</Link>
                        </div>
                    </div>
                </div>

                <div v-else class="empty panel-surface">
                    <div class="mono mono-accent">{{ t('saved.no_searches', 'No saved searches') }}</div>
                    <p class="text-muted">{{ t('saved.no_searches_text', 'Save a search from the results page and we’ll keep an eye out for new matches.') }}</p>
                    <Link :href="route('vehicles.index')" class="btn btn-secondary">{{ t('saved.browse', 'Browse vehicles') }} →</Link>
                </div>
            </section>

            <!-- Favorite vehicles -->
            <section class="saved-section">
                <div class="section-bar">
                    <h4>{{ t('saved.favorites', 'Favorite vehicles') }}</h4>
                    <span class="mono">{{ favorites.length }} {{ t('saved.count', 'saved') }}</span>
                </div>

                <div v-if="favorites.length" class="grid-cards">
                    <VehicleCard v-for="v in favorites" :key="v.id" :vehicle="v" :favorited="favSet.has(v.id)" />
                </div>

                <div v-else class="empty panel-surface">
                    <div class="mono mono-accent">{{ t('saved.no_favs', 'No favorites yet') }}</div>
                    <p class="text-muted">{{ t('saved.no_favs_text', 'Tap the heart on any listing to keep it here for later.') }}</p>
                    <Link :href="route('vehicles.index')" class="btn btn-primary">{{ t('saved.find', 'Find a vehicle') }} →</Link>
                </div>
            </section>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.saved { padding: 40px 32px; display: flex; flex-direction: column; gap: 44px; }
.saved-head h2 { margin: 6px 0 0; }

.saved-section { display: flex; flex-direction: column; gap: 22px; }
.section-bar { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--color-divider); padding-bottom: 12px; }
.section-bar h4 { margin: 0; }

/* Saved searches */
.search-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
.search-card { padding: 18px; display: flex; flex-direction: column; gap: 12px; }
.search-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.search-title { font: 800 17px/1.2 var(--font-heading); }
.search-del { background: transparent; border: 0; cursor: pointer; font: 700 22px/1 var(--font-heading); color: var(--color-neutral-500); padding: 0 2px; line-height: 1; }
.search-del:hover { color: var(--color-accent); }
.search-summary { font-size: 13px; line-height: 1.5; }
.search-foot { margin-top: auto; padding-top: 12px; border-top: 1px solid var(--color-hairline); display: flex; align-items: center; justify-content: space-between; gap: 12px; }

/* Favorites */
.grid-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }

/* Empty */
.empty { padding: 40px 28px; display: flex; flex-direction: column; align-items: flex-start; gap: 10px; }
.empty p { margin: 0; max-width: 48ch; }

@media (max-width: 1000px) {
    .grid-cards { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 620px) {
    .saved { padding: 28px 18px; }
    .grid-cards { grid-template-columns: 1fr 1fr; }
    .search-grid { grid-template-columns: 1fr; }
}
</style>
