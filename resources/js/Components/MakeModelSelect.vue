<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { loadCatalog } from '@/lib/catalog.js';
import { t } from '@/lib/i18n.js';

// Make and model in one picker: choose a make and its models open in the same
// list — or type "bmw 1" to jump straight to a model.
const makeId = defineModel('makeId', { type: [Number, String], default: '' });
const modelId = defineModel('modelId', { type: [Number, String], default: '' });
const props = defineProps({
    category: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    anyModelLabel: { type: String, default: '' },
    hero: { type: Boolean, default: false },
});

const catalog = ref([]);
const open = ref(false);
const query = ref('');
const browsing = ref(null);
const root = ref(null);
const searchInput = ref(null);

watch(() => props.category, async (slug) => {
    catalog.value = [];
    const data = await loadCatalog(slug);
    if (slug === props.category) catalog.value = data;
}, { immediate: true });

const selectedMake = computed(() => catalog.value.find((m) => m.id == makeId.value) ?? null);
const selectedModel = computed(() => selectedMake.value?.models.find((m) => m.id == modelId.value) ?? null);
const label = computed(() => [selectedMake.value?.name, selectedModel.value?.name].filter(Boolean).join(' '));

const rows = computed(() => {
    const tokens = query.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
    if (tokens.length) {
        const out = [];
        for (const make of catalog.value) {
            const makeName = make.name.toLowerCase();
            if (tokens.every((tk) => makeName.includes(tk))) out.push({ key: `m${make.id}`, make, label: make.name, drill: true });
            for (const model of make.models) {
                const name = `${makeName} ${model.name.toLowerCase()}`;
                if (tokens.every((tk) => name.includes(tk))) out.push({ key: `c${model.id}`, make, model, label: `${make.name} ${model.name}` });
            }
            if (out.length >= 80) break;
        }
        return out;
    }
    if (browsing.value) {
        const make = browsing.value;
        return [
            { key: 'all', make, label: `${make.name} — ${props.anyModelLabel || t('flt.any_model', 'Any model')}` },
            ...make.models.map((model) => ({ key: `c${model.id}`, make, model, label: model.name })),
        ];
    }
    return [
        { key: 'any', label: props.placeholder },
        ...catalog.value.map((make) => ({ key: `m${make.id}`, make, label: make.name, drill: true })),
    ];
});

const isActive = (row) => {
    if (row.model) return row.model.id == modelId.value;
    if (row.make) return row.make.id == makeId.value && (row.drill || !modelId.value);
    return !makeId.value;
};

const focusSearch = () => nextTick(() => searchInput.value?.focus());
const close = () => { open.value = false; query.value = ''; };
const toggle = () => {
    if (open.value) return close();
    browsing.value = selectedMake.value;
    open.value = true;
    focusSearch();
};
const back = () => { browsing.value = null; focusSearch(); };

const pick = (row) => {
    query.value = '';
    makeId.value = row.make?.id ?? '';
    modelId.value = row.model?.id ?? '';
    if (row.drill) {
        browsing.value = row.make;
        focusSearch();
        return;
    }
    close();
};

const onDocClick = (e) => { if (root.value && !root.value.contains(e.target)) close(); };
onMounted(() => document.addEventListener('click', onDocClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocClick));
</script>

<template>
    <div ref="root" class="mm" :class="{ 'mm-hero': hero }" @keydown.esc="close">
        <button type="button" class="mm-btn" :class="{ input: !hero }" :disabled="!category" aria-haspopup="listbox" :aria-expanded="open" @click="toggle">
            <span class="mm-value" :class="{ empty: !label }">{{ label || placeholder }}</span>
            <svg class="mm-caret" width="12" height="8" viewBox="0 0 12 8"><path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </button>

        <div v-if="open" class="mm-panel">
            <input
                ref="searchInput" v-model="query" class="mm-search" type="text" autocomplete="off"
                :placeholder="t('mm.search_ph', 'Search make or model…')"
                @keydown.enter.prevent="rows.length && pick(rows[0])"
            />
            <button v-if="browsing && !query.trim()" type="button" class="mm-back" @click="back">
                <svg width="7" height="11" viewBox="0 0 8 12"><path d="M7 1L2 6l5 5" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
                {{ t('mm.all_makes', 'All makes') }}
            </button>
            <div class="mm-list" role="listbox">
                <button
                    v-for="row in rows" :key="row.key" type="button" role="option"
                    class="mm-opt" :class="{ active: isActive(row) }" :aria-selected="isActive(row)"
                    @click="pick(row)"
                >
                    <span class="mm-opt-txt">{{ row.label }}</span>
                    <svg v-if="row.drill" class="mm-chev" width="7" height="11" viewBox="0 0 8 12"><path d="M1 1l5 5-5 5" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </button>
                <div v-if="!rows.length" class="mm-empty">{{ t('mm.no_match', 'No matches') }}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.mm {
    position: relative;
    --mm-bg: var(--color-card); --mm-line: var(--color-divider); --mm-text: var(--color-text);
    --mm-muted: var(--color-neutral-500); --mm-hover: color-mix(in srgb, var(--color-text) 6%, transparent);
    --mm-shadow: var(--shadow-card);
}
.mm-btn { display: flex; align-items: center; gap: 10px; text-align: left; cursor: pointer; }
.mm-btn:disabled { cursor: not-allowed; opacity: .6; }
.mm-value { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mm-caret { flex: none; }

.mm-panel {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 60; min-width: 250px;
    background: var(--mm-bg); border: 1px solid var(--mm-line); border-radius: var(--radius-md);
    box-shadow: var(--mm-shadow); padding: 6px; display: flex; flex-direction: column; gap: 4px;
}
.mm-search {
    width: 100%; min-height: 38px; padding: 8px 12px; font: inherit; font-size: 14px;
    color: var(--mm-text); caret-color: var(--color-accent); background: transparent;
    border: 1px solid var(--mm-line); border-radius: var(--radius-sm);
}
.mm-search:focus-visible { outline: none; border-color: var(--color-accent); }
.mm-search::placeholder { color: var(--mm-muted); }
.mm-back {
    display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: transparent; border: 0;
    cursor: pointer; color: var(--color-accent); font: 600 12px var(--font-heading);
}
.mm-list { max-height: 280px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
.mm-opt {
    display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 10px; flex: none;
    background: transparent; border: 0; cursor: pointer; text-align: left;
    color: var(--mm-text); font: inherit; font-size: 14px; border-radius: var(--radius-sm);
    transition: background .14s var(--ease);
}
.mm-opt:hover, .mm-opt:focus-visible { background: var(--mm-hover); outline: none; }
.mm-opt.active { font-weight: 700; background: color-mix(in srgb, var(--color-accent) 12%, transparent); }
.mm-opt-txt { flex: 1; min-width: 0; }
.mm-chev { flex: none; color: var(--mm-muted); }
.mm-empty { padding: 10px; font-size: 13px; color: var(--mm-muted); }

/* Dark glass search bar on the home hero */
.mm-hero {
    --mm-bg: var(--color-ink-2); --mm-line: var(--color-ink-line-2); --mm-text: #fff;
    --mm-muted: var(--color-on-ink-muted); --mm-hover: rgba(255, 255, 255, 0.07);
    --mm-shadow: var(--shadow-ink);
}
.mm-hero .mm-btn {
    width: 100%; padding: 0; background: transparent; border: 0;
    color: #fff; font: 800 15px var(--font-heading); letter-spacing: -0.01em;
}
.mm-hero .mm-caret { color: var(--color-on-ink-muted); }
.mm-hero .mm-panel { top: calc(100% + 14px); right: auto; width: max(100%, 280px); max-width: calc(100vw - 40px); }
.mm-hero .mm-opt { font: 700 14px var(--font-heading); color: var(--color-on-ink-soft); }
.mm-hero .mm-opt:hover, .mm-hero .mm-opt.active { color: #fff; }
</style>
