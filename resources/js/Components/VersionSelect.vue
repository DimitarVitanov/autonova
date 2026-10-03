<script setup>
import { computed, ref, watch } from 'vue';
import { t } from '@/lib/i18n.js';

// Version / trim picker: a dropdown of the versions known for the chosen
// model, with "Other" (or no known versions at all) falling back to free text.
const model = defineModel({ type: String, default: '' });
const props = defineProps({
    carModelId: { type: [Number, String], default: null },
});

const OTHER = '__other__';
const options = ref([]);
const custom = ref(false);

watch(() => props.carModelId, async (id, old) => {
    if (old !== undefined) { model.value = ''; custom.value = false; }
    options.value = [];
    if (!id) return;
    const { data } = await window.axios.get(route('api.versions'), { params: { car_model_id: id } });
    if (id !== props.carModelId) return;
    options.value = data;
    custom.value = !!model.value && !data.includes(model.value);
}, { immediate: true });

const selected = computed({
    get: () => (custom.value ? OTHER : model.value),
    set: (v) => {
        custom.value = v === OTHER;
        model.value = v === OTHER ? '' : v;
    },
});
</script>

<template>
    <div class="version">
        <select v-if="options.length" v-model="selected" class="input">
            <option value="">{{ t('sell.select_version', 'Select version') }}</option>
            <option v-for="o in options" :key="o" :value="o">{{ o }}</option>
            <option :value="OTHER">{{ t('sell.version_other', 'Other — type it in') }}</option>
        </select>
        <input v-if="!options.length || custom" v-model="model" class="input" maxlength="120" :placeholder="t('sell.version_ph', 'e.g. 2.0 TDI Elegance')" />
    </div>
</template>

<style scoped>
.version { display: flex; flex-direction: column; gap: 8px; }
</style>
