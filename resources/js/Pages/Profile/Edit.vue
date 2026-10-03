<script setup>
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head :title="t('nav.profile', 'Profile')" />

    <MarketplaceLayout>
        <div class="wrap-narrow profile">
            <div class="profile-head">
                <div class="mono mono-accent">{{ t('profile.kicker', 'Your account') }}</div>
                <h2 class="profile-title">{{ t('nav.profile', 'Profile') }}</h2>
                <p class="text-muted profile-sub">
                    {{ t('profile.sub', 'Manage your account details, password and account status.') }}
                </p>
            </div>

            <div class="panel profile-card">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <div class="panel profile-card">
                <UpdatePasswordForm />
            </div>

            <div class="panel profile-card">
                <DeleteUserForm />
            </div>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
.profile {
    padding: 48px 32px 72px;
    max-width: 760px;
}
.profile-head {
    margin-bottom: 28px;
}
.profile-title {
    font-size: clamp(30px, 3.4vw, 40px);
    letter-spacing: -0.02em;
    margin: 12px 0 8px;
}
.profile-sub {
    margin: 0;
    font-size: 15px;
}
.profile-card {
    padding: 28px;
}
.profile-card + .profile-card {
    margin-top: 24px;
}

@media (max-width: 700px) {
    .profile {
        padding: 32px 18px 56px;
    }
    .profile-card {
        padding: 22px 18px;
    }
}
</style>
