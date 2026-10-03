<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    filters: { type: Object, required: true },
    options: { type: Object, required: true },
});
const emit = defineEmits(['apply', 'save', 'clear']);

// Local editable copy of the filters
const f = reactive({
    category: props.filters.category ?? '',
    make_id: props.filters.make_id ?? '',
    car_model_id: props.filters.car_model_id ?? '',
    price_min: props.filters.price_min ?? '',
    price_max: props.filters.price_max ?? '',
    year_min: props.filters.year_min ?? '',
    year_max: props.filters.year_max ?? '',
    mileage_max: props.filters.mileage_max ?? '',
    power_min: props.filters.power_min ?? '',
    power_max: props.filters.power_max ?? '',
    fuel: Array.isArray(props.filters.fuel) ? [...props.filters.fuel] : (props.filters.fuel ? [props.filters.fuel] : []),
    transmission: props.filters.transmission ?? '',
    drivetrain: props.filters.drivetrain ?? '',
    body_type: props.filters.body_type ?? '',
    condition: props.filters.condition ?? '',
    seller_type: props.filters.seller_type ?? 'all',
    city: props.filters.city ?? '',
    features: Array.isArray(props.filters.features) ? [...props.filters.features] : [],
});

const makes = ref(props.options.makes ?? []);
const models = ref(props.options.models ?? []);
const showAllFeatures = ref(false);

const bodyTypes = computed(() => (f.category ? (props.options.body_types?.[f.category] ?? []) : []));
const visibleFeatures = computed(() => (showAllFeatures.value ? props.options.features : (props.options.features ?? []).slice(0, 10)));

watch(() => f.category, async (slug) => {
    f.make_id = '';
    f.car_model_id = '';
    f.body_type = '';
    models.value = [];
    if (!slug) { makes.value = []; return; }
    const { data } = await window.axios.get(route('api.makes'), { params: { category: slug } });
    makes.value = data;
});

watch(() => f.make_id, async (id) => {
    f.car_model_id = '';
    if (!id) { models.value = []; return; }
    const { data } = await window.axios.get(route('api.models'), { params: { make_id: id } });
    models.value = data;
});

const params = () => {
    const out = {};
    for (const [k, v] of Object.entries(f)) {
        if (v === '' || v === null || (Array.isArray(v) && v.length === 0)) continue;
        if (k === 'seller_type' && v === 'all') continue;
        out[k] = v;
    }
    // Preserve sort/view from the current query
    if (props.filters.sort) out.sort = props.filters.sort;
    if (props.filters.view) out.view = props.filters.view;
    return out;
};

const apply = () => emit('apply', params());
const save = () => emit('save', params());
const clear = () => {
    Object.assign(f, {
        category: f.category, make_id: '', car_model_id: '', price_min: '', price_max: '',
        year_min: '', year_max: '', mileage_max: '', power_min: '', power_max: '',
        fuel: [], transmission: '', drivetrain: '', body_type: '', condition: '',
        seller_type: 'all', city: '', features: [],
    });
    emit('clear');
};
</script>

<template>
    <aside class="filters">
        <div class="filters-head">
            <h5>{{ t('idx.filters', 'Filters') }}</h5>
            <button class="link-clear" @click="clear">{{ t('flt.clear_all', 'Clear all') }}</button>
        </div>

        <div class="filter-stack">
            <div class="field">
                <label>{{ t('search.category', 'Category') }}</label>
                <select v-model="f.category" class="input">
                    <option value="">{{ t('flt.all_categories', 'All categories') }}</option>
                    <option v-for="c in options.categories" :key="c.id" :value="c.slug">{{ t('cat.' + c.slug, c.name_plural) }}</option>
                </select>
            </div>

            <div class="field">
                <label>{{ t('search.make', 'Make') }}</label>
                <select v-model="f.make_id" class="input" :disabled="!f.category">
                    <option value="">{{ f.category ? t('common.any_make', 'Any make') : t('flt.select_cat_first', 'Select category first') }}</option>
                    <option v-for="m in makes" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
            </div>

            <div class="field" v-if="f.make_id">
                <label>{{ t('flt.model', 'Model') }}</label>
                <select v-model="f.car_model_id" class="input">
                    <option value="">{{ t('flt.any_model', 'Any model') }}</option>
                    <option v-for="m in models" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
            </div>

            <div class="field">
                <label>{{ t('flt.price', 'Price (EUR)') }}</label>
                <div class="input-group">
                    <input v-model="f.price_min" class="input" inputmode="numeric" :placeholder="t('flt.from', 'from')" />
                    <input v-model="f.price_max" class="input" inputmode="numeric" :placeholder="t('flt.to', 'to')" />
                </div>
            </div>

            <div class="field">
                <label>{{ t('flt.year', 'Year') }}</label>
                <div class="input-group">
                    <input v-model="f.year_min" class="input" inputmode="numeric" :placeholder="t('flt.from', 'from')" />
                    <input v-model="f.year_max" class="input" inputmode="numeric" :placeholder="t('flt.to', 'to')" />
                </div>
            </div>

            <div class="field">
                <label>{{ t('flt.mileage_max', 'Mileage up to (km)') }}</label>
                <input v-model="f.mileage_max" class="input" inputmode="numeric" :placeholder="t('flt.mileage_ph', 'e.g. 200000')" />
            </div>

            <div>
                <label class="label">{{ t('flt.fuel', 'Fuel') }}</label>
                <div class="check-list">
                    <label v-for="opt in options.fuels" :key="opt.value" class="check">
                        <input type="checkbox" :value="opt.value" v-model="f.fuel" />
                        <span class="box"></span>{{ t('enum.' + opt.label, opt.label) }}
                    </label>
                </div>
            </div>

            <div class="field">
                <label>{{ t('flt.transmission', 'Transmission') }}</label>
                <select v-model="f.transmission" class="input">
                    <option value="">{{ t('flt.any', 'Any') }}</option>
                    <option v-for="opt in options.transmissions" :key="opt.value" :value="opt.value">{{ t('enum.' + opt.label, opt.label) }}</option>
                </select>
            </div>

            <div class="field" v-if="bodyTypes.length">
                <label>{{ t('flt.body_type', 'Body type') }}</label>
                <select v-model="f.body_type" class="input">
                    <option value="">{{ t('flt.any', 'Any') }}</option>
                    <option v-for="b in bodyTypes" :key="b" :value="b">{{ t('body.' + b, b) }}</option>
                </select>
            </div>

            <div class="field">
                <label>{{ t('flt.drivetrain', 'Drivetrain') }}</label>
                <select v-model="f.drivetrain" class="input">
                    <option value="">{{ t('flt.any', 'Any') }}</option>
                    <option v-for="opt in options.drivetrains" :key="opt.value" :value="opt.value">{{ t('enum.' + opt.label, opt.label) }}</option>
                </select>
            </div>

            <div class="field">
                <label>{{ t('flt.power', 'Power (hp)') }}</label>
                <div class="input-group">
                    <input v-model="f.power_min" class="input" inputmode="numeric" :placeholder="t('flt.from', 'from')" />
                    <input v-model="f.power_max" class="input" inputmode="numeric" :placeholder="t('flt.to', 'to')" />
                </div>
            </div>

            <div>
                <label class="label">{{ t('flt.seller', 'Seller') }}</label>
                <div class="seg seg-full">
                    <label class="seg-opt"><input type="radio" value="all" v-model="f.seller_type" />{{ t('flt.any', 'Any') }}</label>
                    <label class="seg-opt"><input type="radio" value="dealer" v-model="f.seller_type" />{{ t('flt.dealer', 'Dealer') }}</label>
                    <label class="seg-opt"><input type="radio" value="private" v-model="f.seller_type" />{{ t('flt.private', 'Private') }}</label>
                </div>
            </div>

            <div class="field">
                <label>{{ t('search.city', 'City') }}</label>
                <select v-model="f.city" class="input">
                    <option value="">{{ t('common.all_macedonia', 'All Macedonia') }}</option>
                    <option v-for="c in options.cities" :key="c" :value="c">{{ t('city.' + c, c) }}</option>
                </select>
            </div>

            <div v-if="options.features?.length">
                <label class="label">{{ t('flt.equipment', 'Equipment') }}</label>
                <div class="check-list">
                    <label v-for="ft in visibleFeatures" :key="ft.id" class="check">
                        <input type="checkbox" :value="ft.slug" v-model="f.features" />
                        <span class="box"></span>{{ t('feat.' + ft.name, ft.name) }}
                    </label>
                </div>
                <button v-if="options.features.length > 10" class="link-more" @click="showAllFeatures = !showAllFeatures">
                    {{ showAllFeatures ? t('flt.show_less', 'Show less') : `${t('flt.show_all', 'Show all')} ${options.features.length}` }}
                </button>
            </div>

            <button class="btn btn-primary btn-block" @click="apply">{{ t('flt.apply', 'Apply filters') }}</button>

            <div class="save-box panel-surface">
                <h6>{{ t('flt.save_title', 'Save this search') }}</h6>
                <p class="text-muted save-text">{{ t('flt.save_text', 'Get notified about new listings that match.') }}</p>
                <button class="btn btn-secondary btn-block" @click="save">{{ t('flt.save_btn', 'Save search') }}</button>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.filters {
    display: flex; flex-direction: column;
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    padding: 22px 20px;
}
.filters-head { display: flex; align-items: baseline; justify-content: space-between; border-bottom: 1px solid var(--color-hairline); padding-bottom: 14px; margin-bottom: 18px; }
.link-clear, .link-more { background: transparent; border: 0; color: var(--color-accent); font: 600 12px var(--font-heading); cursor: pointer; padding: 0; }
.link-more { margin-top: 8px; }
.filter-stack { display: flex; flex-direction: column; gap: 16px; }
.check-list { display: flex; flex-direction: column; gap: 9px; }
.seg-full { display: flex; width: 100%; }
.seg-full .seg-opt { flex: 1; justify-content: center; }
.save-box { padding: 14px; }
.save-box h6 { margin: 0 0 6px; }
.save-text { font-size: 12px; margin: 0 0 10px; }
</style>
