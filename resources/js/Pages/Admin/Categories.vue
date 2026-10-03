<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import AdminNav from '@/Components/AdminNav.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import { num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

// Local editable copies, keyed by category id.
const forms = reactive({});
props.categories.forEach((c) => {
    forms[c.id] = { name: c.name, name_plural: c.name_plural, hint: c.hint };
});

const save = (c) => {
    const f = forms[c.id];
    router.patch(
        route('admin.categories.update', c.slug),
        { name: f.name, name_plural: f.name_plural, hint: f.hint },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head :title="t('adm.categories', 'Categories')" />
    <MarketplaceLayout>
        <section class="wrap admin">
            <div class="admin-grid">
                <aside class="admin-side">
                    <AdminNav current="admin.categories" />
                </aside>

                <div class="admin-main">
                    <div class="admin-head">
                        <div class="mono mono-accent">{{ t('adm.taxonomy', 'Taxonomy') }}</div>
                        <h2>{{ t('adm.categories', 'Categories') }}</h2>
                    </div>

                    <div class="cats-grid">
                        <div v-for="c in categories" :key="c.id" class="panel cat-card">
                            <div class="cat-top">
                                <span class="cat-ic"><CategoryIcon :name="c.icon" :size="30" /></span>
                                <div class="cat-meta">
                                    <div class="cat-name">{{ c.name_plural }}</div>
                                    <div class="mono">
                                        {{ num(c.makes_count ?? 0) }} {{ t('adm.makes_word', 'makes') }} · {{ num(c.vehicles_count ?? 0) }} {{ t('adm.listings_word', 'listings') }} · {{ num(c.active_count ?? 0) }} {{ t('adm.active_word', 'active') }}
                                    </div>
                                </div>
                            </div>

                            <div class="cat-form">
                                <label class="field">
                                    <span class="label">{{ t('auth.name', 'Name') }}</span>
                                    <input v-model="forms[c.id].name" class="input" type="text" />
                                </label>
                                <label class="field">
                                    <span class="label">{{ t('adm.name_plural', 'Name (plural)') }}</span>
                                    <input v-model="forms[c.id].name_plural" class="input" type="text" />
                                </label>
                                <label class="field">
                                    <span class="label">{{ t('adm.hint', 'Hint') }}</span>
                                    <input v-model="forms[c.id].hint" class="input" type="text" />
                                </label>
                                <div class="cat-actions">
                                    <button class="btn btn-primary btn-sm" @click="save(c)">{{ t('adm.save', 'Save') }}</button>
                                </div>
                            </div>
                        </div>
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
.admin-head .mono { margin-bottom: 8px; }
.admin-head h2 { margin: 0; }

.cats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.cat-card { padding: 20px; display: flex; flex-direction: column; gap: 18px; }
.cat-top { display: flex; align-items: center; gap: 14px; padding-bottom: 16px; border-bottom: 1px solid var(--color-hairline); }
.cat-ic { color: var(--color-accent); flex: none; display: grid; place-items: center; width: 52px; height: 52px; background: var(--color-accent-100); border-radius: var(--radius-md); }
.cat-meta { min-width: 0; }
.cat-name { font: 800 18px/1.15 var(--font-heading); }
.cat-meta .mono { margin-top: 8px; }

.cat-form { display: flex; flex-direction: column; gap: 12px; }
.cat-actions { display: flex; justify-content: flex-end; margin-top: 2px; }

@media (max-width: 820px) {
    .admin { padding: 28px 18px 48px; }
    .admin-grid { grid-template-columns: 1fr; gap: 20px; }
    .admin-side { position: static; }
    .cats-grid { grid-template-columns: 1fr; }
}
</style>
