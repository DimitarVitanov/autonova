<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import VersionSelect from '@/Components/VersionSelect.vue';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    categories: Array,
    makesByCategory: Object,
    features: Array,
    options: Object,
    promotions: Array,
    defaults: Object,
});

const step = ref(1);
const steps = computed(() => [
    { n: 1, label: t('search.category', 'Category') },
    { n: 2, label: t('sell.step_details', 'Details') },
    { n: 3, label: t('sell.photos', 'Photos') },
    { n: 4, label: t('flt.equipment', 'Equipment') },
    { n: 5, label: t('sell.promotion', 'Promotion') },
]);

const form = useForm({
    category_id: '', make_id: '', car_model_id: '', version: '',
    price: '', vat: 'with_vat', year: '', mileage_km: '', fuel: 'diesel',
    transmission: 'manual', engine_cc: '', power_hp: '', drivetrain: '',
    body_type: '', doors: '', seats: '', color: '', condition: 'used',
    owners: '', registered_until: '', city: props.defaults?.city ?? '',
    contact_phone: props.defaults?.contact_phone ?? '', description: '',
    features: [], promotion: 'none', images: [],
});

const models = ref([]);
const categorySlug = computed(() => props.categories.find((c) => c.id === form.category_id)?.slug ?? '');
const makes = computed(() => props.makesByCategory[form.category_id] ?? []);
const bodyTypes = computed(() => (categorySlug.value ? (props.options.body_types?.[categorySlug.value] ?? []) : []));
const featureGroups = computed(() => {
    const groups = {};
    for (const f of props.features) (groups[f.group] ??= []).push(f);
    return groups;
});

watch(() => form.make_id, async (id) => {
    form.car_model_id = '';
    models.value = [];
    if (id) {
        const { data } = await window.axios.get(route('api.models'), { params: { make_id: id } });
        models.value = data;
    }
});

const pickCategory = (id) => { form.category_id = id; form.make_id = ''; step.value = 2; };
const next = () => { if (step.value < 5) step.value++; };
const prev = () => { if (step.value > 1) step.value--; };
const goStep = (n) => (step.value = n);

const submit = () => {
    form.transform((d) => ({ ...d })).post(route('vehicles.store'), {
        forceFormData: true,
        onError: () => { /* keep user on current step; errors shown inline */ },
    });
};

const errorList = computed(() => Object.values(form.errors));
</script>

<template>
    <Head :title="t('sell.title', 'Sell your vehicle')" />
    <MarketplaceLayout>
        <div class="sell wrap-narrow">
            <h2 class="sell-title">{{ t('sell.title', 'Sell your vehicle') }}</h2>
            <p class="text-muted">{{ t('sell.step', 'Step') }} {{ step }} {{ t('sell.of', 'of') }} 5 · {{ t('sell.free_30', 'Free listing for 30 days') }}</p>

            <!-- Step tabs -->
            <div class="steps">
                <button v-for="s in steps" :key="s.n" class="step" :class="{ active: s.n === step, done: s.n < step }" @click="goStep(s.n)">
                    <span class="mono">0{{ s.n }}</span>
                    <span class="step-label">{{ s.label }}</span>
                </button>
            </div>

            <div v-if="errorList.length" class="err-banner">
                <strong>{{ t('sell.fix_errors', 'Please fix the following:') }}</strong>
                <ul><li v-for="(e, i) in errorList" :key="i">{{ e }}</li></ul>
            </div>

            <!-- Step 1: Category -->
            <div v-show="step === 1" class="panel-pad">
                <h4>{{ t('sell.what_selling', 'What are you selling?') }}</h4>
                <div class="cat-choices">
                    <button v-for="c in categories" :key="c.id" class="cat-choice" :class="{ chosen: form.category_id === c.id }" @click="pickCategory(c.id)">
                        <CategoryIcon :name="c.icon" :size="28" />
                        <span class="cc-name">{{ t('cat.' + c.slug, c.name_plural) }}</span>
                        <span class="mono">{{ t('cathint.' + c.slug, c.hint) }}</span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Details -->
            <div v-show="step === 2" class="panel-pad">
                <h4>{{ t('sell.basic_details', 'Basic details') }}</h4>
                <div class="form-grid">
                    <div class="field"><label>{{ t('search.make', 'Make') }}</label>
                        <select v-model="form.make_id" class="input"><option value="">{{ t('sell.select_make', 'Select make') }}</option><option v-for="m in makes" :key="m.id" :value="m.id">{{ m.name }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('flt.model', 'Model') }}</label>
                        <select v-model="form.car_model_id" class="input" :disabled="!form.make_id"><option value="">{{ t('sell.select_model', 'Select model') }}</option><option v-for="m in models" :key="m.id" :value="m.id">{{ m.name }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.version_trim', 'Version / trim') }}</label><VersionSelect v-model="form.version" :car-model-id="form.car_model_id" /></div>
                    <div class="field"><label>{{ t('flt.year', 'Year') }}</label>
                        <select v-model="form.year" class="input"><option value="">{{ t('flt.year', 'Year') }}</option><option v-for="y in options.years" :key="y" :value="y">{{ y }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.mileage_km', 'Mileage (km)') }}</label><input v-model="form.mileage_km" class="input" inputmode="numeric" placeholder="147000" /></div>
                    <div class="field"><label>{{ t('flt.fuel', 'Fuel') }}</label>
                        <select v-model="form.fuel" class="input"><option v-for="o in options.fuels" :key="o.value" :value="o.value">{{ t('enum.' + o.label, o.label) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('flt.transmission', 'Transmission') }}</label>
                        <select v-model="form.transmission" class="input"><option v-for="o in options.transmissions" :key="o.value" :value="o.value">{{ t('enum.' + o.label, o.label) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.engine_cc', 'Engine (cm³)') }}</label><input v-model="form.engine_cc" class="input" inputmode="numeric" placeholder="1968" /></div>
                    <div class="field"><label>{{ t('flt.power', 'Power (hp)') }}</label><input v-model="form.power_hp" class="input" inputmode="numeric" placeholder="150" /></div>
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
                    <div class="field"><label>{{ t('spec.Registered until', 'Registered until') }}</label><input v-model="form.registered_until" class="input" placeholder="04 / 2027" /></div>
                    <div class="field"><label>{{ t('search.city', 'City') }}</label>
                        <select v-model="form.city" class="input"><option value="">{{ t('auth.select_city', 'Select city') }}</option><option v-for="c in options.cities" :key="c" :value="c">{{ t('city.' + c, c) }}</option></select>
                    </div>
                    <div class="field"><label>{{ t('sell.contact_phone', 'Contact phone') }}</label><input v-model="form.contact_phone" class="input" placeholder="+389 …" /></div>
                    <div class="field"><label>{{ t('flt.price', 'Price (EUR)') }}</label><input v-model="form.price" class="input" inputmode="numeric" placeholder="13900" /></div>
                </div>
                <div class="hr"></div>
                <div class="vat-row">
                    <label v-for="o in options.vat" :key="o.value" class="radio"><input type="radio" :value="o.value" v-model="form.vat" /><span class="dot"></span>{{ t('enum.' + o.label, o.label) }}</label>
                </div>
            </div>

            <!-- Step 3: Photos -->
            <div v-show="step === 3" class="panel-pad">
                <h4>{{ t('sell.photos', 'Photos') }}</h4>
                <p class="text-muted photo-hint">{{ t('sell.photos_hint', 'The first photo is the cover. Every photo is automatically resized to an identical 1600×1200 WebP so your listing looks clean. Minimum 1 real photo.') }}</p>
                <ImageUploader v-model="form.images" :error="form.errors.images" />
            </div>

            <!-- Step 4: Equipment -->
            <div v-show="step === 4" class="panel-pad">
                <h4>{{ t('sell.equip_desc', 'Equipment & description') }}</h4>
                <div v-for="(items, group) in featureGroups" :key="group" class="equip-group">
                    <div class="mono equip-label">{{ t('featgroup.' + group, group) }}</div>
                    <div class="equip-choices">
                        <label v-for="f in items" :key="f.id" class="chip-check" :class="{ on: form.features.includes(f.id) }">
                            <input type="checkbox" :value="f.id" v-model="form.features" />{{ t('feat.' + f.name, f.name) }}
                        </label>
                    </div>
                </div>
                <div class="field desc-field"><label>{{ t('show.description', 'Description') }}</label>
                    <textarea v-model="form.description" class="input" rows="6" :placeholder="t('sell.desc_ph', 'Describe the condition, service history, extras…')"></textarea>
                </div>
            </div>

            <!-- Step 5: Promotion -->
            <div v-show="step === 5" class="panel-pad">
                <h4>{{ t('sell.promotion', 'Promotion') }}</h4>
                <div class="promo-grid">
                    <label v-for="p in promotions" :key="p.key" class="promo-card" :class="{ chosen: form.promotion === p.key }">
                        <input type="radio" :value="p.key" v-model="form.promotion" class="promo-radio" />
                        <div class="mono mono-accent">{{ t('promo.' + p.key + '.kicker', p.kicker) }}</div>
                        <div class="promo-price">{{ p.price }}</div>
                        <div class="promo-name">{{ t('promo.' + p.key + '.name', p.name) }}</div>
                        <p class="text-muted promo-desc">{{ t('promo.' + p.key + '.desc', p.desc) }}</p>
                    </label>
                </div>
            </div>

            <!-- Nav -->
            <div class="wizard-nav">
                <button v-if="step > 1" class="btn btn-secondary" @click="prev">{{ t('sell.back', 'Back') }}</button>
                <button v-if="step < 5" class="btn btn-primary" @click="next" :disabled="step === 1 && !form.category_id">{{ t('sell.continue', 'Continue') }}</button>
                <button v-else class="btn btn-primary" @click="submit" :disabled="form.processing">
                    {{ form.processing ? t('sell.publishing', 'Publishing…') : t('sell.publish', 'Publish listing') }}
                </button>
            </div>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.sell { padding: 32px 24px 64px; max-width: 1000px; }
.sell-title { margin: 0 0 6px; }

.steps { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin: 26px 0 24px; }
.step {
    text-align: left; padding: 12px 14px;
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-md);
    background: var(--color-card);
    box-shadow: var(--shadow-xs);
    cursor: pointer; display: flex; flex-direction: column; gap: 6px;
    color: var(--color-text);
    transition: border-color .18s var(--ease), background .18s var(--ease), box-shadow .18s var(--ease), transform .18s var(--ease);
}
.step:hover { border-color: color-mix(in srgb, var(--color-text) 22%, transparent); box-shadow: var(--shadow-sm); transform: translateY(-1px); }
.step.active { border-color: var(--color-accent); background: var(--color-accent-100); box-shadow: var(--shadow-sm); }
.step.done { border-color: var(--color-accent-300); }
.step .mono { color: var(--color-neutral-600); }
.step.active .mono { color: var(--color-accent); }
.step-label { font: 800 13px var(--font-heading); }

.err-banner {
    border: 1px solid var(--color-accent);
    background: var(--color-accent-100);
    color: var(--color-accent-800);
    border-radius: var(--radius-md);
    padding: 14px 18px; margin-bottom: 20px; font-size: 13px;
}
.err-banner ul { margin: 6px 0 0; padding-left: 18px; }

.panel-pad {
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    padding: 24px;
}
.panel-pad h4 { margin: 0 0 18px; }

.cat-choices { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.cat-choice {
    display: flex; flex-direction: column; gap: 8px; align-items: flex-start;
    padding: 18px 16px;
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg);
    background: var(--color-surface);
    box-shadow: var(--shadow-xs);
    cursor: pointer; color: var(--color-text);
    transition: border-color .18s var(--ease), background .18s var(--ease), box-shadow .18s var(--ease), transform .18s var(--ease);
}
.cat-choice:hover { border-color: var(--color-accent); background: var(--color-accent-100); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
.cat-choice.chosen { border-color: var(--color-accent); background: var(--color-accent-100); box-shadow: var(--shadow-sm); }
.cat-choice svg { color: var(--color-accent); }
.cc-name { font: 800 15px var(--font-heading); }

.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.vat-row { display: flex; gap: 24px; flex-wrap: wrap; font-size: 13px; }

.photo-hint { max-width: 60ch; }
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
.desc-field { margin-top: 8px; }

.promo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.promo-card {
    display: block; padding: 20px;
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg);
    background: var(--color-surface);
    box-shadow: var(--shadow-xs);
    cursor: pointer;
    transition: border-color .18s var(--ease), background .18s var(--ease), box-shadow .18s var(--ease), transform .18s var(--ease);
}
.promo-card:hover { border-color: color-mix(in srgb, var(--color-text) 20%, transparent); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
.promo-card.chosen { border-color: var(--color-accent); background: var(--color-accent-100); box-shadow: var(--shadow-sm); }
.promo-radio { display: none; }
.promo-price { font: 800 26px/1 var(--font-heading); margin: 8px 0 4px; }
.promo-name { font: 700 14px var(--font-heading); }
.promo-desc { font-size: 13px; margin: 10px 0 0; }

.wizard-nav { display: flex; gap: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--color-hairline); }

@media (max-width: 800px) {
    .form-grid, .cat-choices, .promo-grid { grid-template-columns: 1fr 1fr; }
    .step-label { font-size: 11px; }
}
@media (max-width: 520px) {
    .form-grid, .cat-choices, .promo-grid { grid-template-columns: 1fr; }
    .sell { padding-left: 18px; padding-right: 18px; }
    /* phones: only the current step shows its label, the rest collapse to their number */
    .steps { display: flex; gap: 6px; }
    .step { flex: none; min-width: 0; padding: 10px 12px; }
    .step.active { flex: 1; }
    .step:not(.active) .step-label { display: none; }
    .step-label { font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
}
</style>
