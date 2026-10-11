<script setup>
import { computed, ref, watch } from 'vue';
import { loadCatalog } from '@/lib/catalog.js';
import { t } from '@/lib/i18n.js';

// Make and model in ONE native dropdown. It lists the makes; once a make is
// chosen the same dropdown lists that make's models (the other makes stay
// below them). With `grouped`, the chosen model's versions are listed right
// under it (BMW 1 Series → BMW 1 Series 116i, 118d …) and set the version too.
const makeId = defineModel('makeId', { type: [Number, String], default: '' });
const modelId = defineModel('modelId', { type: [Number, String], default: '' });
const version = defineModel('version', { type: String, default: '' });
const props = defineProps({
    category: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    modelPlaceholder: { type: String, default: '' },
    grouped: { type: Boolean, default: false },
    hero: { type: Boolean, default: false },
});

const catalog = ref([]);
const select = ref(null);

watch(() => props.category, async (slug) => {
    catalog.value = [];
    const data = await loadCatalog(slug);
    if (slug === props.category) catalog.value = data;
}, { immediate: true });

const selectedMake = computed(() => catalog.value.find((m) => m.id == makeId.value) ?? null);
const selectedModel = computed(() => selectedMake.value?.models.find((m) => m.id == modelId.value) ?? null);
const otherMakes = computed(() => catalog.value.filter((m) => m !== selectedMake.value));
const versionsOf = (model) => (props.grouped ? (model.versions ?? []) : []);

// Option keys: "<make>", "<make>:<model>" or "<make>:<model>:<n>" (n-th version).
const choice = computed(() => {
    const make = selectedMake.value;
    const model = selectedModel.value;
    if (!make) return '';
    if (!model) return `${make.id}`;
    const n = version.value ? versionsOf(model).indexOf(version.value) : -1;
    return n >= 0 ? `${make.id}:${model.id}:${n}` : `${make.id}:${model.id}`;
});

const choose = (key) => {
    const [make, model, n] = key.split(':');
    const found = catalog.value.find((m) => m.id == make)?.models.find((m) => m.id == model);
    makeId.value = make ? Number(make) : '';
    modelId.value = found?.id ?? '';
    version.value = found && n !== undefined ? versionsOf(found)[n] : '';
};

// The option list is rebuilt after every pick, which makes the browser drop
// its selection — put it back once the new options are in the DOM.
watch([choice, catalog], () => {
    if (select.value) select.value.value = choice.value;
}, { flush: 'post' });
</script>

<template>
    <select ref="select" :value="choice" class="mm-select" :class="hero ? 'mm-hero' : 'input'" :disabled="!category" @change="choose($event.target.value)">
        <option value="">{{ placeholder }}</option>
        <template v-if="selectedMake">
            <optgroup :label="selectedMake.name">
                <option :value="`${selectedMake.id}`">{{ selectedMake.name }} — {{ modelPlaceholder }}</option>
                <template v-for="m in selectedMake.models" :key="m.id">
                    <option :value="`${selectedMake.id}:${m.id}`">{{ selectedMake.name }} {{ m.name }}</option>
                    <template v-if="m === selectedModel">
                        <option v-for="(v, n) in versionsOf(m)" :key="v" :value="`${selectedMake.id}:${m.id}:${n}`">{{ selectedMake.name }} {{ m.name }} {{ v }}</option>
                    </template>
                </template>
            </optgroup>
            <optgroup :label="t('mm.other_makes', 'Other makes')">
                <option v-for="m in otherMakes" :key="m.id" :value="`${m.id}`">{{ m.name }}</option>
            </optgroup>
        </template>
        <template v-else>
            <option v-for="m in catalog" :key="m.id" :value="`${m.id}`">{{ m.name }}</option>
        </template>
    </select>
</template>

<style scoped>
.mm-select { min-width: 0; text-overflow: ellipsis; cursor: pointer; }
.mm-select:disabled { cursor: not-allowed; opacity: .6; }

/* Dark glass search bar on the home hero */
.mm-hero {
    width: 100%; padding: 0; border: 0; background: transparent; color: #fff;
    font: 800 15px var(--font-heading); letter-spacing: -0.01em; appearance: none;
}
.mm-hero:focus-visible { outline: none; }
.mm-hero option, .mm-hero optgroup { color: var(--color-text); }
</style>
