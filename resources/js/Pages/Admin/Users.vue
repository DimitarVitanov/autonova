<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import AdminNav from '@/Components/AdminNav.vue';
import Pagination from '@/Components/Pagination.vue';
import { num } from '@/lib/format.js';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    users: { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({}) },
});

const roleOptions = ['admin', 'moderator', 'client'];

const q = ref(props.filters?.q ?? '');
const roleFilter = ref(props.filters?.role ?? '');

const reload = () => {
    router.get(
        route('admin.users'),
        { q: q.value || undefined, role: roleFilter.value || undefined },
        { preserveState: true, preserveScroll: true },
    );
};

const changeRole = (user, role) => {
    router.patch(route('admin.users.role', user.id), { role }, { preserveScroll: true });
};

const toggle = (user) => {
    router.post(route('admin.users.toggle', user.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head :title="t('adm.users', 'Users')" />
    <MarketplaceLayout>
        <section class="wrap admin">
            <div class="admin-grid">
                <aside class="admin-side">
                    <AdminNav current="admin.users" />
                </aside>

                <div class="admin-main">
                    <div class="admin-head">
                        <div class="mono mono-accent">{{ t('adm.people', 'People') }}</div>
                        <h2>{{ t('adm.users', 'Users') }}</h2>
                    </div>

                    <form class="filters" @submit.prevent="reload">
                        <input v-model="q" class="input" type="search" :placeholder="t('adm.users_search_ph', 'Search by name or email…')" />
                        <select v-model="roleFilter" class="input role-filter" @change="reload">
                            <option value="">{{ t('adm.all_roles', 'All roles') }}</option>
                            <option v-for="r in roleOptions" :key="r" :value="r">{{ t('role.' + r, r) }}</option>
                        </select>
                        <button type="submit" class="btn btn-secondary">{{ t('common.search', 'Search') }}</button>
                    </form>

                    <div v-if="users.data.length" class="table-scroll panel">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ t('adm.user', 'User') }}</th>
                                    <th>{{ t('adm.type', 'Type') }}</th>
                                    <th>{{ t('search.city', 'City') }}</th>
                                    <th>{{ t('adm.listings', 'Listings') }}</th>
                                    <th>{{ t('adm.joined', 'Joined') }}</th>
                                    <th>{{ t('adm.role', 'Role') }}</th>
                                    <th class="col-actions">{{ t('adm.status', 'Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id">
                                    <td>
                                        <div class="user-name">{{ user.name }}</div>
                                        <div class="text-muted user-email">{{ user.email }}</div>
                                    </td>
                                    <td class="cap">{{ (user.account_type ? t('acctype.' + user.account_type, user.account_type) : '') }}</td>
                                    <td>{{ user.city ? t('city.' + user.city, user.city) : '—' }}</td>
                                    <td>{{ num(user.vehicles_count ?? 0) }}</td>
                                    <td class="nowrap text-muted">{{ user.joined }}</td>
                                    <td>
                                        <select class="input role-select" :value="user.role" @change="changeRole(user, $event.target.value)">
                                            <option v-for="r in roleOptions" :key="r" :value="r">{{ t('role.' + r, r) }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="status-cell">
                                            <span class="tag" :class="user.is_active ? 'tag-success' : 'tag-neutral'">
                                                {{ user.is_active ? t('adm.active', 'Active') : t('adm.suspended', 'Suspended') }}
                                            </span>
                                            <button class="btn btn-secondary btn-sm" @click="toggle(user)">
                                                {{ user.is_active ? t('adm.suspend', 'Suspend') : t('adm.activate', 'Activate') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="empty panel-surface">
                        <div class="mono mono-accent">{{ t('adm.no_users', 'No users') }}</div>
                        <p class="text-muted">{{ t('adm.no_users_text', 'No accounts match this filter.') }}</p>
                    </div>

                    <Pagination :links="users.links" />
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

.filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px; }
.filters .input[type="search"] { flex: 1; min-width: 200px; }
.role-filter { width: auto; min-width: 150px; text-transform: capitalize; }

.table-scroll { overflow-x: auto; }
.table-scroll .table tbody tr:last-child td { border-bottom: 0; }
.user-name { font: 800 14px/1.25 var(--font-heading); }
.user-email { font-size: 12px; margin-top: 3px; }
.cap { text-transform: capitalize; }
.nowrap { white-space: nowrap; }
.role-select { min-width: 130px; text-transform: capitalize; }
.col-actions { text-align: right; }
.status-cell { display: flex; align-items: center; gap: 8px; justify-content: flex-end; }

.empty { padding: 40px 24px; text-align: center; }
.empty p { margin: 10px 0 0; }

@media (max-width: 820px) {
    .admin { padding: 28px 18px 48px; }
    .admin-grid { grid-template-columns: 1fr; gap: 20px; }
    .admin-side { position: static; }
}
</style>
