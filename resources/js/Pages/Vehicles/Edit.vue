<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import VersionSelect from '@/Components/VersionSelect.vue';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    vehicle: Object,
    makesByCategory: Object,
    models: Array,
    categories: Array,
    features: Array,
    options: Object,
});

const r = props.vehicle.raw;
const form = useForm({
    category_id: props.vehicle.category_id,
    make_id: props.vehicle.make_id,
    car_model_id: props.vehicle.car_model_id,
    version: r.version ?? '',
    price: r.price ?? '',
    vat: r.vat ?? 'with_vat',
    year: r.year ?? '',
    mileage_km: r.mileage_km ?? '',
    fuel: r.fuel ?? 'diesel',
    transmission: r.transmission ?? 'manual',
    engine_cc: r.engine_cc ?? '',
    power_hp: r.power_hp ?? '',
    drivetrain: r.drivetrain ?? '',
    body_type: r.body_type ?? '',
    doors: r.doors ?? '',
    seats: r.seats ?? '',
    color: r.color ?? '',
    condition: r.condition ?? 'used',
    owners: r.owners ?? '',
    registered_until: r.registered_until ?? '',
    city: r.city ?? '',
    contact_phone: r.contact_phone ?? '',
    description: r.description ?? '',
    features: [...(props.vehicle.feature_ids ?? [])],
    images: [],
});

const models = ref(props.models ?? []);
const categorySlug = computed(() => props.categories.find((c) => c.id === form.category_id)?.slug ?? '');
const makes = computed(() => props.makesByCategory[form.category_id] ?? []);
const bodyTypes = computed(() => (categorySlug.value ? (props.options.body_types?.[categorySlug.value] ?? []) : []));
const featureGroups = computed(() => {
    const groups = {};
    for (const f of props.features) (groups[f.group] ??= []).push(f);
    return groups;
});
const existingImages = computed(() => props.vehicle.images ?? []);
const imageError = computed(() => Object.entries(form.errors).find(([k]) => k === 'images' || k.startsWith('images.'))?.[1] ?? '');

watch(() => form.make_id, async (id, old) => {
    if (id === old) return;
    form.car_model_id = '';
    models.value = [];
    if (id) {
        const { data } = await window.axios.get(route('api.models'), { params: { make_id: id } });
        models.value = data;
    }
});

const submit = () => form.put(route('vehicles.update', props.vehicle.slug), { forceFormData: true });
</script>

<template>
    <Head :title="`${t('sell.edit_prefix', 'Edit')} — ${vehicle.title}`" />
    <MarketplaceLayout>
        <div class="edit wrap-narrow">
            <div class="edit-head">
                <div>
                    <h2>{{ t('show.edit', 'Edit listing') }}</h2>
                    <p class="text-muted">{{ vehicle.title }} · <span style="text-transform:capitalize">{{ t('status.' + vehicle.status, vehicle.status) }}</span></p>
                </div>
                <Link :href="route('vehicles.show', vehicle.slug)" class="btn btn-secondary">{{ t('sell.view_listing', 'View listing') }}</Link>
            </div>

            <div v-if="Object.keys(form.errors).length" class="err-banner">
                <strong>{{ t('sell.fix_errors', 'Please fix the following:') }}</strong>
                <ul><li v-for="(e, k) in form.errors" :key="k">{{ e }}</li></ul>
            </div>

            <section class="card-block panel">
                <h4>{{ t('sell.step_details', 'Details') }}</h4>
                <div class="form-grid">
                    <div class="field"><label>{{ t('search.make', 'Make') }}</label>
                        <select v-model="form.make_id" class="input"><option value="">{{ t('sell.select_make', 'Select make') }}</option><option v-for="m in makes" :key="m.id" :value="m.id">{{ m.name }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('flt.model', 'Model') }}</label>
                        <select v-model="form.car_model_id" class="input"><option value="">{{ t('sell.select_model', 'Select model') }}</option><option v-for="m in models" :key="m.id" :value="m.id">{{ m.name }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.version', 'Version') }}</label><VersionSelect v-model="form.version" :car-model-id="form.car_model_id" /></div>
                    <div class="field"><label>{{ t('flt.year', 'Year') }}</label>
                        <select v-model="form.year" class="input"><option v-for="y in options.years" :key="y" :value="y">{{ y }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.mileage_km', 'Mileage (km)') }}</label><input v-model="form.mileage_km" class="input" inputmode="numeric" /></div>
                    <div class="field"><label>{{ t('flt.fuel', 'Fuel') }}</label>
                        <select v-model="form.fuel" class="input"><option v-for="o in options.fuels" :key="o.value" :value="o.value">{{ t('enum.' + o.label, o.label) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('flt.transmission', 'Transmission') }}</label>
                        <select v-model="form.transmission" class="input"><option v-for="o in options.transmissions" :key="o.value" :value="o.value">{{ t('enum.' + o.label, o.label) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.engine_cc', 'Engine (cm³)') }}</label><input v-model="form.engine_cc" class="input" inputmode="numeric" /></div>
                    <div class="field"><label>{{ t('flt.power', 'Power (hp)') }}</label><input v-model="form.power_hp" class="input" inputmode="numeric" /></div>
                    <div class="field"><label>{{ t('flt.drivetrain', 'Drivetrain') }}</label>
                        <select v-model="form.drivetrain" class="input"><option value="">—</option><option v-for="o in options.drivetrains" :key="o.value" :value="o.value">{{ t('enum.' + o.label, o.label) }}</option></select>
                    </div>
                    <div class="field" v-if="bodyTypes.length"><label>{{ t('flt.body_type', 'Body type') }}</label>
                        <select v-model="form.body_type" class="input"><option value="">—</option><option v-for="b in bodyTypes" :key="b" :value="b">{{ t('body.' + b, b) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('spec.Colour', 'Colour') }}</label>
                        <select v-model="form.color" class="input"><option value="">—</option><option v-for="c in options.colors" :key="c" :value="c">{{ t('color.' + c, c) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('spec.Condition', 'Condition') }}</label>
                        <select v-model="form.condition" class="input"><option v-for="o in options.conditions" :key="o.value" :value="o.value">{{ t('enum.' + o.label, o.label) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('spec.Registered until', 'Registered until') }}</label><input v-model="form.registered_until" class="input" /></div>
                    <div class="field"><label>{{ t('search.city', 'City') }}</label>
                        <select v-model="form.city" class="input"><option v-for="c in options.cities" :key="c" :value="c">{{ t('city.' + c, c) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.contact_phone', 'Contact phone') }}</label><input v-model="form.contact_phone" class="input" /></div>
                    <div class="field"><label>{{ t('flt.price', 'Price (EUR)') }}</label><input v-model="form.price" class="input" inputmode="numeric" /></div>
                </div>
                <div class="hr"></div>
                <div class="vat-row">
                    <label v-for="o in options.vat" :key="o.value" class="radio"><input type="radio" :value="o.value" v-model="form.vat" /><span class="dot"></span>{{ t('enum.' + o.label, o.label) }}</label>
                </div>
            </section>

            <section class="card-block panel">
                <h4>{{ t('sell.photos', 'Photos') }}</h4>
                <div v-if="existingImages.length" class="existing">
                    <div v-for="img in existingImages" :key="img.id" class="frame frame-43 ex-thumb">
                        <img :src="img.thumb_url" alt="" />
                        <span v-if="img.is_cover" class="cover-badge">{{ t('upl.cover', 'Cover') }}</span>
                    </div>
                </div>
                <p class="text-muted add-note">{{ t('sell.add_more', 'Add more photos (auto-resized to 1600×1200 WebP):') }}</p>
                <ImageUploader v-model="form.images" :error="imageError" />
            </section>

            <section class="card-block panel">
                <h4>{{ t('sell.equip_desc', 'Equipment & description') }}</h4>
                <div v-for="(items, group) in featureGroups" :key="group" class="equip-group">
                    <div class="mono equip-label">{{ t('featgroup.' + group, group) }}</div>
                    <div class="equip-choices">
                        <label v-for="f in items" :key="f.id" class="chip-check" :class="{ on: form.features.includes(f.id) }">
                            <input type="checkbox" :value="f.id" v-model="form.features" />{{ t('feat.' + f.name, f.name) }}
                        </label>
                    </div>
                </div>
                <div class="field"><label>{{ t('show.description', 'Description') }}</label><textarea v-model="form.description" class="input" rows="6"></textarea></div>
            </section>

            <div class="actions">
                <Link :href="route('dashboard')" class="btn btn-secondary">{{ t('sell.cancel', 'Cancel') }}</Link>
                <button class="btn btn-primary" @click="submit" :disabled="form.processing">{{ form.processing ? t('sell.saving', 'Saving…') : t('sell.save_changes', 'Save changes') }}</button>
            </div>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.edit { padding: 32px 24px 64px; max-width: 1000px; }
.edit-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
.err-banner {
    border: 1px solid var(--color-accent);
    background: var(--color-accent-100);
    color: var(--color-accent-800);
    border-radius: var(--radius-md);
    padding: 14px 18px; margin-bottom: 20px; font-size: 13px;
}
.err-banner ul { margin: 6px 0 0; padding-left: 18px; }
.card-block { padding: 24px; margin-bottom: 20px; }
.card-block h4 { margin: 0 0 18px; }
.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.vat-row { display: flex; gap: 24px; flex-wrap: wrap; font-size: 13px; }
.existing { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; margin-bottom: 16px; }
.ex-thumb {
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-xs);
}
.cover-badge {
    position: absolute; left: 8px; top: 8px; z-index: 2;
    background: var(--color-accent); color: #fff;
    font: 800 9px/1 var(--font-heading); letter-spacing: .08em; text-transform: uppercase;
    padding: 5px 9px; border-radius: var(--radius-pill);
    box-shadow: var(--shadow-sm);
}
.add-note { font-size: 13px; }
.equip-group { margin-bottom: 18px; }
.equip-label { margin-bottom: 8px; }
.equip-choices { display: flex; flex-wrap: wrap; gap: 8px; }
.chip-check {
    display: inline-flex; align-items: center; padding: 8px 14px;
    border: 1px solid var(--color-divider);
    border-radius: var(--radius-pill);
    background: var(--color-card);
    font-size: 13px; cursor: pointer; color: var(--color-text);
    transition: border-color .15s var(--ease), background .15s var(--ease), color .15s var(--ease);
}
.chip-check:hover { border-color: color-mix(in srgb, var(--color-text) 24%, transparent); }
.chip-check input { display: none; }
.chip-check.on { border-color: var(--color-accent); background: var(--color-accent-100); color: var(--color-accent-800); }
.actions { display: flex; gap: 12px; }
@media (max-width: 800px) { .form-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 520px) { .form-grid { grid-template-columns: 1fr; } }
</style>
