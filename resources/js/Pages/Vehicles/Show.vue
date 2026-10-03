<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { eur, mkd, num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    vehicle: Object,
    related: Object,
    favoriteIds: Array,
    canManage: Boolean,
});

const page = usePage();
const v = computed(() => props.vehicle);
const images = computed(() => v.value.images ?? []);
const activeIndex = ref(0);
const activeImage = computed(() => images.value[activeIndex.value]?.url ?? v.value.cover_url);
const showPhone = ref(false);
const isFav = computed(() => (props.favoriteIds ?? []).includes(v.value.id));

const specs = computed(() => {
    const s = v.value;
    return [
        ['Year', s.year],
        ['Mileage', s.mileage_km ? num(s.mileage_km) + ' ' + t('unit.km', 'km') : null],
        ['Fuel', s.fuel ? t('enum.' + s.fuel, s.fuel) : null],
        ['Transmission', s.transmission ? t('enum.' + s.transmission, s.transmission) : null],
        ['Engine', s.engine_cc ? num(s.engine_cc) + ' ' + t('unit.cc', 'cm³') : null],
        ['Power', s.power_hp ? s.power_hp + ' ' + t('unit.hp', 'hp') : null],
        ['Body type', s.body_type ? t('body.' + s.body_type, s.body_type) : null],
        ['Drivetrain', s.drivetrain ? t('enum.' + s.drivetrain, s.drivetrain) : null],
        ['Doors', s.doors],
        ['Seats', s.seats],
        ['Colour', s.color ? t('color.' + s.color, s.color) : null],
        ['Condition', s.condition ? t('enum.' + s.condition, s.condition) : null],
        ['Owners', s.owners],
        ['Registered until', s.registered_until],
    ].filter(([, val]) => val !== null && val !== undefined && val !== '');
});

const featureGroups = computed(() => v.value.features ?? {});
const relatedList = computed(() => props.related?.data ?? []);

const contact = useForm({ body: `${t('show.contact_prefix', 'Hi, is the')} ${v.value.title} ${t('show.contact_suffix', 'still available?')}` });
const sendMessage = () => {
    if (!page.props.auth.user) { router.visit(route('login')); return; }
    contact.post(route('conversations.start', v.value.slug), { preserveScroll: true });
};
</script>

<template>
    <Head :title="`${vehicle.title}${vehicle.version ? ' ' + vehicle.version : ''}`" />
    <MarketplaceLayout>
        <div class="detail wrap-narrow">
            <div class="crumb text-muted">
                <Link :href="route('home')">{{ t('common.home', 'Home') }}</Link> /
                <Link :href="route('vehicles.index', { category: vehicle.category.slug })">{{ t('cat.' + vehicle.category.slug, vehicle.category.name_plural) }}</Link> /
                {{ vehicle.title }}
            </div>

            <div v-if="canManage" class="manage-bar panel-surface">
                <span>{{ t('show.manage', 'You manage this listing') }} · <strong style="text-transform:capitalize">{{ t('status.' + vehicle.status, vehicle.status) }}</strong></span>
                <Link :href="route('vehicles.edit', vehicle.slug)" class="btn btn-secondary btn-sm">{{ t('show.edit', 'Edit listing') }}</Link>
            </div>

            <div class="detail-grid">
                <!-- Main column -->
                <div class="detail-main">
                    <div class="gallery">
                        <div class="frame frame-169 gallery-main">
                            <img v-if="activeImage" :src="activeImage" :alt="vehicle.title" />
                            <span class="frame-count">{{ activeIndex + 1 }} / {{ images.length }}</span>
                        </div>
                        <div v-if="images.length > 1" class="thumbs">
                            <button
                                v-for="(img, i) in images" :key="img.id"
                                class="thumb frame frame-43" :class="{ active: i === activeIndex }"
                                @click="activeIndex = i"
                            >
                                <img :src="img.thumb_url" :alt="`${vehicle.title} ${t('show.photo', 'photo')} ${i + 1}`" />
                            </button>
                        </div>
                    </div>

                    <h3 class="block-title">{{ t('show.specs', 'Specifications') }}</h3>
                    <div class="specs panel-block">
                        <div v-for="[k, val] in specs" :key="k" class="spec-row">
                            <span class="text-muted">{{ t('spec.' + k, k) }}</span>
                            <span class="spec-val" style="text-transform:capitalize">{{ val }}</span>
                        </div>
                    </div>

                    <template v-if="Object.keys(featureGroups).length">
                        <h3 class="block-title">{{ t('show.equipment', 'Equipment') }}</h3>
                        <div class="panel-block equip-panel">
                            <div v-for="(items, group) in featureGroups" :key="group" class="equip-group">
                                <div class="mono equip-label">{{ t('featgroup.' + group, group) }}</div>
                                <div class="equip-tags">
                                    <span v-for="item in items" :key="item" class="tag tag-neutral">{{ t('feat.' + item, item) }}</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-if="vehicle.description">
                        <h3 class="block-title">{{ t('show.description', 'Description') }}</h3>
                        <div class="panel-block">
                            <p class="desc">{{ vehicle.description }}</p>
                        </div>
                    </template>
                </div>

                <!-- Sidebar -->
                <aside class="detail-side">
                    <div class="price-card panel">
                        <h6 v-if="vehicle.is_featured" class="promo-kicker">{{ t('show.featured_listing', 'Featured listing') }}</h6>
                        <h4 class="price-title">{{ vehicle.title }} <span v-if="vehicle.version">{{ vehicle.version }}</span></h4>
                        <div class="price">{{ eur(vehicle.price) }}</div>
                        <div class="price-sub text-muted">{{ mkd(vehicle.price_mkd) }} · {{ t('enum.' + vehicle.vat, vehicle.vat) }}</div>
                        <div class="hr"></div>
                        <div class="price-actions">
                            <button v-if="!showPhone" class="btn btn-primary btn-block" @click="showPhone = true">{{ t('show.show_phone', 'Show phone number') }}</button>
                            <a v-else :href="`tel:${vehicle.contact_phone}`" class="btn btn-primary btn-block phone">{{ vehicle.contact_phone || t('show.no_phone', 'No phone provided') }}</a>
                            <FavoriteButton :slug="vehicle.slug" :favorited="isFav" variant="button" class="btn-block" />
                        </div>
                        <form class="contact" @submit.prevent="sendMessage">
                            <label class="label">{{ t('show.message_seller', 'Message the seller') }}</label>
                            <textarea v-model="contact.body" class="input" rows="3"></textarea>
                            <div v-if="contact.errors.body" class="text-muted" style="color:var(--color-accent-700);font-size:12px">{{ contact.errors.body }}</div>
                            <button class="btn btn-dark btn-block" :disabled="contact.processing" type="submit">
                                {{ page.props.auth.user ? t('show.send_message', 'Send message') : t('show.signin_message', 'Sign in to message') }}
                            </button>
                        </form>
                    </div>

                    <component
                        :is="vehicle.dealer ? 'a' : 'div'"
                        class="seller-card panel"
                    >
                        <h6 class="seller-h">{{ t('show.seller', 'Seller') }}</h6>
                        <Link v-if="vehicle.dealer" :href="route('dealers.show', vehicle.dealer.slug)" class="seller-row link-quiet">
                            <span class="seller-logo">{{ vehicle.dealer.name.charAt(0) }}</span>
                            <span>
                                <span class="seller-name">{{ vehicle.dealer.name }}</span>
                                <span class="mono seller-meta">{{ (vehicle.dealer.city ? t('city.' + vehicle.dealer.city, vehicle.dealer.city) : '') }} · {{ num(vehicle.dealer.vehicles_count) }} {{ t('common.vehicles_word', 'vehicles') }}</span>
                            </span>
                        </Link>
                        <div v-else class="seller-row">
                            <span class="seller-logo">{{ vehicle.seller.name.charAt(0) }}</span>
                            <span>
                                <span class="seller-name">{{ vehicle.seller.name }}</span>
                                <span class="mono seller-meta">{{ t('common.private_seller', 'Private seller') }} · {{ (vehicle.seller.city ? t('city.' + vehicle.seller.city, vehicle.seller.city) : '') }}</span>
                            </span>
                        </div>
                        <div class="seller-tags">
                            <span v-if="vehicle.dealer?.verified" class="tag tag-accent">{{ t('show.verified_dealer', 'Verified dealer') }}</span>
                            <span class="tag tag-neutral">{{ num(vehicle.views) }} {{ t('show.views', 'views') }}</span>
                            <span class="tag tag-neutral">{{ t('show.listed', 'Listed') }} {{ vehicle.published_at }}</span>
                        </div>
                    </component>
                </aside>
            </div>

            <!-- Related -->
            <section v-if="relatedList.length" class="related">
                <div class="section-head"><h3>{{ t('show.similar', 'Similar vehicles') }}</h3></div>
                <div class="related-grid">
                    <VehicleCard v-for="rv in relatedList" :key="rv.id" :vehicle="rv" :favorited="(favoriteIds || []).includes(rv.id)" />
                </div>
            </section>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.detail { padding: 24px 24px 64px; }
.crumb { font-size: 12px; margin-bottom: 18px; }
.crumb a { color: var(--color-neutral-600); }
.crumb a:hover { color: var(--color-accent); }

.manage-bar {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 14px 18px; margin-bottom: 20px; font-size: 13px;
    box-shadow: var(--shadow-xs);
}
.detail-grid { display: grid; grid-template-columns: 1fr 360px; gap: 36px; align-items: start; }
.detail-main { min-width: 0; }

/* Gallery — dark showroom stage */
.gallery { background: var(--color-ink); border-radius: var(--radius-xl); padding: 14px; box-shadow: var(--shadow-ink); }
.gallery-main {
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-ink-line);
    box-shadow: none;
}
.thumbs { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-top: 12px; }
.thumb {
    padding: 0; cursor: pointer; background: var(--color-ink-2);
    border: 1px solid var(--color-ink-line);
    border-radius: var(--radius-md);
    transition: border-color .16s var(--ease), box-shadow .16s var(--ease), transform .16s var(--ease);
}
.thumb:hover { border-color: var(--color-ink-line-2); transform: translateY(-2px); }
.thumb.active { border-color: var(--color-accent); box-shadow: 0 0 0 2px var(--color-accent); }

/* Info panels */
.block-title { margin: 32px 0 14px; padding-bottom: 10px; border-bottom: 1px solid var(--color-divider); }
.panel-block {
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    padding: 22px 24px;
}

.specs { display: grid; grid-template-columns: 1fr 1fr; gap: 0 40px; padding-top: 6px; padding-bottom: 6px; }
.spec-val { font-weight: 700; }
.specs .spec-row:last-child { border-bottom: none; }

.equip-group { margin-bottom: 18px; }
.equip-panel .equip-group:last-child { margin-bottom: 0; }
.equip-label { margin-bottom: 10px; }
.equip-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.desc { max-width: 66ch; line-height: 1.75; margin: 0; }

/* Sidebar */
.detail-side { position: sticky; top: 88px; display: flex; flex-direction: column; gap: 18px; }
.price-card { padding: 22px; box-shadow: var(--shadow-card); }
.promo-kicker { color: var(--color-accent); margin: 0 0 10px; }
.price-title { margin: 0 0 12px; }
.price { font: 800 36px/1 var(--font-heading); color: var(--color-accent-700); letter-spacing: -0.02em; }
.price-sub { font-size: 12px; margin-top: 6px; }
.price-actions { display: flex; flex-direction: column; gap: 10px; }
.phone { letter-spacing: 0.02em; }
.contact { margin-top: 16px; display: flex; flex-direction: column; gap: 8px; }

.seller-card { padding: 22px; display: block; }
.seller-h { margin: 0 0 14px; }
.seller-row { display: flex; gap: 12px; align-items: center; }
.seller-logo {
    width: 48px; height: 48px; flex: none; display: grid; place-items: center;
    background: var(--color-text); color: var(--color-bg);
    font: 800 19px var(--font-heading); border-radius: var(--radius-md);
}
.seller-name { display: block; font: 800 15px var(--font-heading); }
.seller-meta { display: block; margin-top: 4px; }
.seller-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px; }

.related { margin-top: 48px; }
.section-head { border-bottom: 1px solid var(--color-divider); padding-bottom: 14px; margin-bottom: 24px; }
.related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }

@media (max-width: 900px) {
    .detail-grid { grid-template-columns: 1fr; }
    .detail-side { position: static; }
    .related-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
    .specs { grid-template-columns: 1fr; }
    .related-grid { grid-template-columns: 1fr; }
    .thumbs { grid-template-columns: repeat(4, 1fr); }
}
</style>
