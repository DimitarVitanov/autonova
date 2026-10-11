<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { loadCatalog } from '@/lib/catalog.js';
import { t } from '@/lib/i18n.js';

// Make and model in ONE dropdown that stays open while you drill down:
// make → its models → (with `grouped`) that model's versions. Every tap is
// applied straight away, so closing half-way keeps what was chosen so far.
// On phones the list opens as a centred sheet, like the system picker.
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
const open = ref(false);
const sheet = ref(false);
const step = ref('makes');
const root = ref(null);
const panel = ref(null);
const list = ref(null);

watch(() => props.category, async (slug) => {
    catalog.value = [];
    const data = await loadCatalog(slug);
    if (slug === props.category) catalog.value = data;
}, { immediate: true });

const selectedMake = computed(() => catalog.value.find((m) => m.id == makeId.value) ?? null);
const selectedModel = computed(() => selectedMake.value?.models.find((m) => m.id == modelId.value) ?? null);
const versionsOf = (model) => (props.grouped ? (model?.versions ?? []) : []);
const label = computed(() => [selectedMake.value?.name, selectedModel.value?.name, selectedModel.value && props.grouped ? version.value : ''].filter(Boolean).join(' '));

const title = computed(() => {
    if (step.value === 'versions') return `${selectedMake.value?.name} ${selectedModel.value?.name}`;
    if (step.value === 'models') return selectedMake.value?.name;
    return t('search.make_model', 'Make & model');
});

const rows = computed(() => {
    const make = selectedMake.value;
    const model = selectedModel.value;
    if (step.value === 'versions' && model) {
        return [
            { key: 'all', label: `${make.name} ${model.name} — ${t('flt.any_version', 'Any version')}`, active: !version.value, pick: () => setVersion('') },
            ...versionsOf(model).map((v) => ({ key: v, label: v, active: v === version.value, pick: () => setVersion(v) })),
        ];
    }
    if (step.value === 'models' && make) {
        return [
            { key: 'all', label: `${make.name} — ${props.modelPlaceholder}`, active: !model, pick: () => setModel(null) },
            ...make.models.map((m) => ({ key: m.id, label: m.name, active: m === model, more: versionsOf(m).length > 0, pick: () => setModel(m) })),
        ];
    }
    return [
        { key: 'any', label: props.placeholder, active: !make, pick: () => setMake(null) },
        ...catalog.value.map((m) => ({ key: m.id, label: m.name, active: m === make, more: true, pick: () => setMake(m) })),
    ];
});

const goTo = (name) => {
    step.value = name;
    nextTick(() => { if (list.value) list.value.scrollTop = 0; });
};

const setMake = (make) => {
    makeId.value = make?.id ?? '';
    modelId.value = '';
    version.value = '';
    make ? goTo('models') : close();
};
const setModel = (model) => {
    modelId.value = model?.id ?? '';
    version.value = '';
    versionsOf(model).length ? goTo('versions') : close();
};
const setVersion = (v) => {
    version.value = v;
    close();
};
const back = () => goTo(step.value === 'versions' ? 'models' : 'makes');

const onDocClick = (e) => {
    const path = e.composedPath();
    if (!path.includes(root.value) && !path.includes(panel.value)) close();
};
const onKey = (e) => { if (e.key === 'Escape') close(); };

function close() {
    if (!open.value) return;
    open.value = false;
    document.removeEventListener('click', onDocClick);
    document.removeEventListener('keydown', onKey);
    if (sheet.value) document.body.style.overflow = '';
}

const toggle = () => {
    if (open.value) return close();
    sheet.value = window.matchMedia('(max-width: 760px)').matches;
    step.value = selectedMake.value ? 'models' : 'makes';
    open.value = true;
    if (sheet.value) document.body.style.overflow = 'hidden';
    document.addEventListener('click', onDocClick);
    document.addEventListener('keydown', onKey);
    nextTick(() => list.value?.querySelector('.active')?.scrollIntoView({ block: 'center' }));
};

onBeforeUnmount(close);
</script>

<template>
    <div ref="root" class="mm" :class="{ 'mm-hero': hero }">
        <button type="button" class="mm-btn" :class="{ input: !hero }" :disabled="!category" aria-haspopup="listbox" :aria-expanded="open" @click="toggle">
            <span class="mm-value">{{ label || placeholder }}</span>
        </button>

        <Teleport to="body" :disabled="!sheet">
            <div v-if="open" class="mm-layer" :class="{ sheet, dark: hero && !sheet }" @click.self="close">
                <div ref="panel" class="mm-panel">
                    <div v-if="sheet || step !== 'makes'" class="mm-head">
                        <button v-if="step !== 'makes'" type="button" class="mm-icon" :aria-label="t('common.back', 'Back')" @click="back">
                            <svg width="9" height="15" viewBox="0 0 8 12"><path d="M7 1L2 6l5 5" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                        <span class="mm-title">{{ title }}</span>
                        <button v-if="sheet" type="button" class="mm-icon" :aria-label="t('common.close', 'Close')" @click="close">
                            <svg width="14" height="14" viewBox="0 0 14 14"><path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" /></svg>
                        </button>
                    </div>
                    <div ref="list" class="mm-list" role="listbox">
                        <button
                            v-for="row in rows" :key="`${step}-${row.key}`" type="button" role="option"
                            class="mm-opt" :class="{ active: row.active }" :aria-selected="row.active" @click="row.pick"
                        >
                            <span class="mm-opt-txt">{{ row.label }}</span>
                            <svg v-if="row.more" class="mm-chev" width="7" height="11" viewBox="0 0 8 12"><path d="M1 1l5 5-5 5" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.mm { position: relative; }

/* Trigger — looks like every other select */
.mm-btn { display: block; text-align: left; cursor: pointer; }
.mm-btn.input {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%231b1917' stroke-width='1.6' fill='none'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center; padding-right: 34px;
}
.mm-btn:disabled { cursor: not-allowed; opacity: .6; }
.mm-value { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mm-hero .mm-btn {
    width: 100%; padding: 0; border: 0; background: transparent; color: #fff;
    font: 800 15px var(--font-heading); letter-spacing: -0.01em;
}

/* Desktop: list drops down under the field */
.mm-layer {
    --mm-bg: var(--color-card); --mm-line: var(--color-divider); --mm-text: var(--color-text);
    --mm-muted: var(--color-neutral-500); --mm-hover: color-mix(in srgb, var(--color-text) 6%, transparent);
    position: absolute; top: calc(100% + 6px); left: 0; z-index: 60; width: max(100%, 260px);
}
.mm-layer.dark {
    --mm-bg: var(--color-ink-2); --mm-line: var(--color-ink-line-2); --mm-text: #fff;
    --mm-muted: var(--color-on-ink-muted); --mm-hover: rgba(255, 255, 255, 0.08);
    top: calc(100% + 14px);
}
.mm-panel {
    display: flex; flex-direction: column; max-height: 340px; overflow: hidden;
    background: var(--mm-bg); color: var(--mm-text); border: 1px solid var(--mm-line);
    border-radius: var(--radius-md); box-shadow: var(--shadow-lg);
}
.mm-head { display: flex; align-items: center; gap: 4px; padding: 6px; border-bottom: 1px solid var(--mm-line); flex: none; }
.mm-title { flex: 1; min-width: 0; padding: 0 8px; font: 800 14px var(--font-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mm-icon {
    flex: none; width: 36px; height: 36px; display: grid; place-items: center; cursor: pointer;
    background: transparent; border: 0; border-radius: var(--radius-sm); color: var(--mm-text);
}
.mm-icon:hover { background: var(--mm-hover); }
.mm-list { overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; padding: 6px; }
.mm-opt {
    display: flex; align-items: center; gap: 10px; width: 100%; min-height: 40px; padding: 8px 12px;
    background: transparent; border: 0; cursor: pointer; text-align: left;
    color: var(--mm-text); font: inherit; font-size: 14px; border-radius: var(--radius-sm);
}
.mm-opt:hover, .mm-opt:focus-visible { background: var(--mm-hover); outline: none; }
.mm-opt.active { font-weight: 700; color: var(--color-accent); }
.mm-opt-txt { flex: 1; min-width: 0; }
.mm-chev { flex: none; color: var(--mm-muted); }

/* Phones: centred sheet over a dimmed page, big tap targets */
.mm-layer.sheet {
    position: fixed; inset: 0; z-index: 1000; width: auto;
    display: flex; align-items: center; justify-content: center; padding: 16px;
    background: rgba(0, 0, 0, 0.55);
}
.sheet .mm-panel { width: 100%; max-width: 440px; max-height: min(78vh, 620px); border: 0; border-radius: var(--radius-lg); }
.sheet .mm-head { padding: 8px; }
.sheet .mm-title { font-size: 16px; padding-left: 10px; }
.sheet .mm-icon { width: 44px; height: 44px; }
.sheet .mm-opt { min-height: 50px; padding: 12px 14px; font-size: 16px; }
</style>
