<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import AdminNav from '@/Components/AdminNav.vue';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    translations: { type: Array, default: () => [] },
});

const form = useForm({
    translations: Object.fromEntries(props.translations.map((tr) => [tr.id, tr.mk ?? ''])),
});

const groups = computed(() => {
    const g = {};
    props.translations.forEach((tr) => {
        (g[tr.group] ||= []).push(tr);
    });
    return g;
});

const save = () => form.post(route('admin.translations.update'), { preserveScroll: true });
</script>

<template>
    <Head :title="t('adm.translations', 'Translations')" />
    <MarketplaceLayout>
        <section class="wrap admin">
            <div class="admin-grid">
                <aside class="admin-side">
                    <AdminNav current="admin.translations" />
                </aside>

                <div class="admin-main">
                    <div class="admin-head">
                        <div class="mono mono-accent">{{ t('adm.localisation', 'Localisation') }}</div>
                        <h2>{{ t('adm.translations', 'Translations') }}</h2>
                        <p class="text-muted admin-sub">{{ t('adm.translations_sub', 'Edit the Macedonian text shown across the site. Leave a field blank to fall back to the English source.') }}</p>
                    </div>

                    <div v-for="(rows, group) in groups" :key="group" class="tgroup panel">
                        <div class="tgroup-head mono">{{ group }}</div>
                        <div v-for="tr in rows" :key="tr.id" class="trow">
                            <div class="tkey">
                                <div class="mono tkey-name">{{ tr.key }}</div>
                                <div class="ten">{{ tr.en }}</div>
                            </div>
                            <input v-model="form.translations[tr.id]" class="input tmk" type="text" :placeholder="tr.en" />
                        </div>
                    </div>

                    <div class="save-bar">
                        <span v-if="form.recentlySuccessful" class="saved mono">{{ t('adm.saved', 'Saved ✓') }}</span>
                        <button class="btn btn-primary btn-lg" :disabled="form.processing" @click="save">{{ t('adm.save_changes', 'Save changes') }}</button>
                    </div>
                </div>
            </div>
        </section>
    </MarketplaceLayout>
</template>

<style scoped>
.admin { padding: 40px 32px 64px; }
.admin-grid { display: grid; grid-template-columns: 200px 1fr; gap: 32px; align-items: start; }
.admin-side { position: sticky; top: calc(var(--header-h) + 16px); }
.admin-head { margin-bottom: 22px; }
.admin-sub { font-size: 14px; margin: 8px 0 0; max-width: 60ch; }

.tgroup { padding: 8px 8px 12px; margin-bottom: 18px; }
.tgroup-head { padding: 12px 12px 10px; color: var(--color-accent); border-bottom: 1px solid var(--color-hairline); margin-bottom: 6px; }
.trow { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; align-items: center; padding: 9px 12px; border-radius: var(--radius-sm); }
.trow:hover { background: var(--color-surface); }
.tkey { min-width: 0; }
.tkey-name { color: var(--color-neutral-600); }
.ten { font-size: 13px; color: var(--color-neutral-800); margin-top: 3px; overflow: hidden; text-overflow: ellipsis; }
.tmk { min-height: 40px; }

.save-bar {
    position: sticky; bottom: 0; display: flex; align-items: center; justify-content: flex-end; gap: 16px;
    padding: 16px 0; margin-top: 8px;
    background: linear-gradient(180deg, transparent, var(--color-bg) 40%);
}
.saved { color: var(--color-success); }

@media (max-width: 820px) {
    .admin-grid { grid-template-columns: 1fr; }
    .admin-side { position: static; }
    .trow { grid-template-columns: 1fr; gap: 8px; }
}
</style>
