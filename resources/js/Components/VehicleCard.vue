<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { eur, num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    vehicle: { type: Object, required: true },
    favorited: { type: Boolean, default: false },
    layout: { type: String, default: 'grid' }, // 'grid' | 'list'
});

const href = computed(() => route('vehicles.show', props.vehicle.slug));
const badge = computed(() => {
    if (props.vehicle.promotion === 'homepage') return t('card.top', 'Top');
    if (props.vehicle.is_featured) return t('card.featured', 'Featured');
    return null;
});
const specs = computed(() => {
    const v = props.vehicle;
    return [
        v.year,
        v.mileage_km ? num(v.mileage_km) + ' ' + t('unit.km', 'km') : null,
        v.fuel ? t('enum.' + v.fuel, v.fuel) : null,
    ].filter(Boolean);
});
const sellerLabel = computed(() => props.vehicle.dealer_name || (props.vehicle.seller_type === 'dealer' ? t('common.dealer', 'Dealer') : t('common.private_seller', 'Private seller')));
</script>

<template>
    <Link :href="href" :class="['vcard', layout === 'list' ? 'vcard-list' : 'vcard-grid']">
        <div class="vcard-media">
            <div class="frame frame-43">
                <img v-if="vehicle.cover_url" :src="vehicle.cover_url" :alt="vehicle.title" loading="lazy" />
                <div v-else class="img-placeholder">{{ t('common.no_photo', 'No photo') }}</div>
            </div>
            <span v-if="badge" class="frame-badge">{{ badge }}</span>
            <span v-if="vehicle.images_count" class="frame-count">{{ vehicle.images_count }} {{ t('card.photos', 'photos') }}</span>
            <FavoriteButton :slug="vehicle.slug" :favorited="favorited" />
        </div>

        <div class="vcard-body">
            <div class="vcard-title">
                {{ vehicle.title }}<span v-if="vehicle.version" class="vcard-version"> · {{ vehicle.version }}</span>
            </div>
            <div class="vcard-price">{{ eur(vehicle.price) }}</div>
            <div class="vcard-specs">
                <span v-for="(s, i) in specs" :key="i" class="vcard-spec">{{ s }}</span>
            </div>
            <div class="vcard-foot">
                <span class="vcard-seller">{{ sellerLabel }}</span>
                <span class="vcard-city">{{ (vehicle.city ? t('city.' + vehicle.city, vehicle.city) : '') }}</span>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.vcard {
    display: flex; flex-direction: column;
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden; color: var(--color-text);
    transition: border-color .22s var(--ease), box-shadow .22s var(--ease), transform .22s var(--ease);
}
.vcard:hover {
    border-color: color-mix(in srgb, var(--color-text) 8%, transparent);
    box-shadow: var(--shadow-card-hover);
    transform: translateY(-4px);
    color: var(--color-text);
}
.vcard-media { position: relative; }
.vcard-media .frame { border-radius: 0; }
.vcard-media .frame > img { transition: transform .5s var(--ease); }
.vcard:hover .vcard-media .frame > img { transform: scale(1.05); }

.vcard-body { padding: 16px 16px 15px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.vcard-title { font: 700 15px/1.3 var(--font-heading); letter-spacing: -0.01em; }
.vcard-version { color: var(--color-neutral-600); font-weight: 500; }
.vcard-price { font: 800 24px/1 var(--font-heading); color: var(--color-text); letter-spacing: -0.02em; }

.vcard-specs { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 1px; }
.vcard-spec {
    font: 600 11px/1 var(--font-heading);
    color: var(--color-neutral-700);
    background: var(--color-surface-2);
    padding: 6px 9px; border-radius: var(--radius-pill);
    white-space: nowrap;
}

.vcard-foot {
    margin-top: auto; padding-top: 12px; margin-top: 12px;
    border-top: 1px solid var(--color-hairline);
    display: flex; justify-content: space-between; gap: 10px;
    font-size: 12px; color: var(--color-neutral-600);
}
.vcard-seller { font-weight: 600; color: var(--color-neutral-700); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.vcard-city { flex: none; }

/* List layout */
.vcard-list { flex-direction: row; }
.vcard-list .vcard-media { width: 260px; flex: none; }
.vcard-list .vcard-body { padding: 18px 20px; }
.vcard-list .vcard-price { font-size: 27px; }
@media (max-width: 560px) {
    .vcard-list { flex-direction: column; }
    .vcard-list .vcard-media { width: 100%; }
}
</style>
