<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import AdminNav from '@/Components/AdminNav.vue';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    launchOfferSettings: { type: Object, required: true },
    suggestedUntil: { type: String, required: true },
});

const page = usePage();

const form = useForm({
    launch_offer_enabled: props.launchOfferSettings.enabled,
    launch_offer_until: props.launchOfferSettings.until ?? props.suggestedUntil,
});

// Status of what is saved (not of unsaved edits in the form).
const status = computed(() => {
    if (page.props.launchOffer?.active) return { cls: 'tag-success', label: t('adm.launch_live', 'Live — all plans show as free') };
    if (props.launchOfferSettings.enabled) return { cls: 'tag-warning', label: t('adm.launch_ended', 'Ended — the end date has passed') };
    return { cls: 'tag-neutral', label: t('adm.launch_off', 'Off — regular prices') };
});

const save = () => form.patch(route('admin.settings.update'), { preserveScroll: true });
</script>

<template>
    <Head :title="t('adm.settings', 'Settings')" />
    <MarketplaceLayout>
        <section class="wrap admin">
            <div class="admin-grid">
                <aside class="admin-side">
                    <AdminNav current="admin.settings" />
                </aside>

                <div class="admin-main">
                    <div class="admin-head">
                        <div class="mono mono-accent">{{ t('adm.site', 'Site') }}</div>
                        <h2>{{ t('adm.settings', 'Settings') }}</h2>
                    </div>

                    <form class="panel setting-card" @submit.prevent="save">
                        <div class="setting-top">
                            <div>
                                <h4 class="setting-title">{{ t('adm.launch_title', 'Free launch period') }}</h4>
                                <p class="text-muted setting-desc">
                                    {{ t('adm.launch_desc', 'While on, every dealer package and listing promotion is shown as free, with the regular price struck through. It switches itself off after the end date.') }}
                                </p>
                            </div>
                            <span class="tag" :class="status.cls">{{ status.label }}</span>
                        </div>

                        <label class="switch-row">
                            <input v-model="form.launch_offer_enabled" type="checkbox" class="switch-input" />
                            <span class="switch" aria-hidden="true"></span>
                            <span class="switch-label">{{ t('adm.launch_toggle', 'Everything is free') }}</span>
                        </label>

                        <label class="field until-field">
                            <span class="label">{{ t('adm.launch_until', 'Free until (inclusive)') }}</span>
                            <input v-model="form.launch_offer_until" type="date" class="input" :disabled="!form.launch_offer_enabled" />
                            <span v-if="form.errors.launch_offer_until" class="field-error">{{ form.errors.launch_offer_until }}</span>
                        </label>

                        <div class="setting-actions">
                            <button class="btn btn-primary btn-sm" type="submit" :disabled="form.processing || !form.isDirty">
                                {{ t('adm.save', 'Save') }}
                            </button>
                        </div>
                    </form>
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
.admin-head .mono { margin-bottom: 8px; }
.admin-head h2 { margin: 0; }

.setting-card { padding: 24px; max-width: 640px; display: flex; flex-direction: column; gap: 20px; }
.setting-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding-bottom: 18px; border-bottom: 1px solid var(--color-hairline); }
.setting-title { margin: 0 0 6px; }
.setting-desc { margin: 0; font-size: 14px; max-width: 46ch; }
.setting-top .tag { flex: none; }

.switch-row { display: inline-flex; align-items: center; gap: 12px; cursor: pointer; align-self: flex-start; }
.switch-input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.switch {
    position: relative; flex: none; width: 46px; height: 26px; border-radius: var(--radius-pill);
    background: var(--color-neutral-300, #d4d4d8); transition: background .18s var(--ease);
}
.switch::after {
    content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%;
    background: #fff; box-shadow: var(--shadow-sm); transition: transform .18s var(--ease);
}
.switch-input:checked + .switch { background: var(--color-accent); }
.switch-input:checked + .switch::after { transform: translateX(20px); }
.switch-input:focus-visible + .switch { box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-accent) 25%, transparent); }
.switch-label { font: 700 15px/1.2 var(--font-heading); }

.until-field { max-width: 240px; gap: 6px; }
.until-field .input:disabled { opacity: .5; }
.field-error { color: var(--color-accent-700); font-size: 12px; }
.setting-actions { display: flex; justify-content: flex-end; }

@media (max-width: 820px) {
    .admin { padding: 28px 18px 48px; }
    .admin-grid { grid-template-columns: 1fr; gap: 20px; }
    .admin-side { position: static; }
    .setting-top { flex-direction: column; }
}
</style>
