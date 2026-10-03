<script setup>
import { ref } from 'vue';
import { t } from '@/lib/i18n.js';

const model = defineModel({ type: Array, default: () => [] });
const props = defineProps({
    max: { type: Number, default: 24 },
    error: { type: String, default: '' },
});

const items = ref([]); // { file, url }
const dragging = ref(false);
const busy = ref(false);
const notices = ref([]); // photos skipped by the checks below
const input = ref(null);

// Mirrors App\Services\ImageService — the server enforces the same limits.
const TARGET_W = 1600, TARGET_H = 1200;
const MIN_W = 640, MIN_H = 480;

const sync = () => (model.value = items.value.map((i) => i.file));

const decode = (file) => new Promise((resolve, reject) => {
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
    img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('decode')); };
    img.src = url;
});

const toBlob = (canvas, type, quality) => new Promise((resolve) => canvas.toBlob(resolve, type, quality));

// Shrink to just cover the 1600×1200 canvas and re-encode as WebP in the
// browser, so a 10 MB phone photo uploads as a few hundred KB.
const prepare = async (file) => {
    const img = await decode(file);
    const w = img.naturalWidth, h = img.naturalHeight;
    if (w < MIN_W || h < MIN_H) throw new Error('small');

    const scale = Math.min(1, Math.max(TARGET_W / w, TARGET_H / h));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(w * scale);
    canvas.height = Math.round(h * scale);
    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);

    // Browsers that cannot encode WebP (Safari) silently return PNG — use JPEG there.
    let blob = await toBlob(canvas, 'image/webp', 0.9);
    if (!blob || blob.type !== 'image/webp') blob = await toBlob(canvas, 'image/jpeg', 0.9);
    if (!blob) throw new Error('decode');

    const ext = blob.type === 'image/webp' ? 'webp' : 'jpg';
    return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.' + ext, { type: blob.type });
};

const addFiles = async (fileList) => {
    const files = Array.from(fileList).filter((f) => f.type.startsWith('image/'));
    notices.value = [];
    busy.value = true;
    for (const original of files) {
        if (items.value.length >= props.max) {
            notices.value.push(`${t('upl.only', 'Only')} ${props.max} ${t('upl.max_skipped', 'photos are allowed — the rest were skipped.')}`);
            break;
        }
        try {
            const file = await prepare(original);
            items.value.push({ file, url: URL.createObjectURL(file) });
            sync();
        } catch (e) {
            notices.value.push(e.message === 'small'
                ? `${original.name} ${t('upl.too_small', 'is too small — photos must be at least')} ${MIN_W}×${MIN_H}.`
                : `${original.name} ${t('upl.unreadable', 'could not be read — please use a JPG, PNG or WEBP photo.')}`);
        }
    }
    busy.value = false;
};

const onInput = (e) => { addFiles(e.target.files); e.target.value = ''; };
const onDrop = (e) => { dragging.value = false; addFiles(e.dataTransfer.files); };
const remove = (i) => { URL.revokeObjectURL(items.value[i].url); items.value.splice(i, 1); sync(); };
const makeCover = (i) => { const [it] = items.value.splice(i, 1); items.value.unshift(it); sync(); };
</script>

<template>
    <div class="uploader">
        <div
            class="dropzone"
            :class="{ dragging }"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
            @click="input.click()"
        >
            <input ref="input" type="file" accept="image/*" multiple class="hidden-input" @change="onInput" />
            <div class="dz-inner">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 16V4M8 8l4-4 4 4" /><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3" />
                </svg>
                <div class="dz-text">{{ t('upl.drop', 'Drop photos here or') }} <span class="accent">{{ t('upl.browse', 'browse') }}</span></div>
                <div class="mono">JPG / PNG / WEBP · {{ t('upl.up_to', 'up to') }} {{ max }} {{ t('card.photos', 'photos') }} · {{ t('upl.min', 'min') }} {{ MIN_W }}×{{ MIN_H }} · {{ t('upl.auto_resized', 'auto-resized to') }} {{ TARGET_W }}×{{ TARGET_H }} WebP</div>
            </div>
        </div>

        <p v-if="busy" class="mono">{{ t('upl.optimising', 'Optimising photos…') }}</p>
        <p v-for="(n, i) in notices" :key="i" class="err">{{ n }}</p>
        <p v-if="error" class="err">{{ error }}</p>

        <div v-if="items.length" class="grid">
            <div v-for="(it, i) in items" :key="it.url" class="thumb frame frame-43">
                <img :src="it.url" :alt="t('upl.preview', 'preview')" />
                <span v-if="i === 0" class="cover-badge">{{ t('upl.cover', 'Cover') }}</span>
                <div class="thumb-actions">
                    <button v-if="i !== 0" type="button" class="mini" @click.stop="makeCover(i)" :title="t('upl.make_cover', 'Make cover')">★</button>
                    <button type="button" class="mini danger" @click.stop="remove(i)" :title="t('upl.remove', 'Remove')">✕</button>
                </div>
            </div>
        </div>
        <p v-else class="mono empty-note">{{ t('upl.empty', 'No photos added yet — the first photo becomes the cover.') }}</p>
    </div>
</template>

<style scoped>
.uploader { display: flex; flex-direction: column; gap: 14px; }
.dropzone {
    border: 1px dashed var(--color-divider);
    border-radius: var(--radius-lg);
    padding: 34px 30px; text-align: center; cursor: pointer;
    background: var(--color-surface);
    transition: border-color .18s var(--ease), background .18s var(--ease), box-shadow .18s var(--ease);
}
.dropzone:hover, .dragging {
    border-color: var(--color-accent);
    background: var(--color-accent-100);
    box-shadow: var(--shadow-sm);
}
.hidden-input { display: none; }
.dz-inner { display: flex; flex-direction: column; align-items: center; gap: 8px; color: var(--color-neutral-700); }
.dz-text { font: 700 15px var(--font-heading); color: var(--color-text); }
.accent { color: var(--color-accent); }
.err { color: var(--color-accent-700); font-size: 13px; margin: 0; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
.thumb {
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
.thumb-actions { position: absolute; right: 8px; top: 8px; z-index: 2; display: flex; gap: 6px; }
.mini {
    width: 28px; height: 28px; display: grid; place-items: center;
    background: color-mix(in srgb, var(--color-card) 88%, transparent);
    backdrop-filter: blur(4px);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-pill);
    cursor: pointer; font-size: 13px; color: var(--color-text);
    box-shadow: var(--shadow-sm);
    transition: border-color .15s var(--ease), color .15s var(--ease), background .15s var(--ease);
}
.mini:hover { border-color: var(--color-accent); color: var(--color-accent); background: var(--color-card); }
.mini.danger:hover { border-color: var(--color-accent-700); color: var(--color-accent-700); }
.empty-note { color: var(--color-neutral-600); }
</style>
