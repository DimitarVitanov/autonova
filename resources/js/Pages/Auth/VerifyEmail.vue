<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.verify_title', 'Email Verification')" />

        <div class="auth-intro">
            {{ t('auth.verify_intro', 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn’t receive the email, we will gladly send you another.') }}
        </div>

        <div
            class="auth-status"
            v-if="verificationLinkSent"
        >
            {{ t('auth.verify_sent', 'A new verification link has been sent to the email address you provided during registration.') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ t('auth.resend', 'Resend Verification Email') }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="auth-logout"
                    >{{ t('nav.logout', 'Log out') }}</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.auth-intro {
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 1.55;
    color: color-mix(in srgb, var(--color-text) 58%, transparent);
}
.auth-status {
    margin-bottom: 18px;
    padding: 11px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-success);
    background: var(--color-success-bg);
    border: 1px solid color-mix(in srgb, var(--color-success) 30%, transparent);
    border-radius: var(--radius-md);
}
.auth-logout {
    background: transparent;
    border: 0;
    padding: 0;
    cursor: pointer;
    font: 700 13px var(--font-heading);
    color: color-mix(in srgb, var(--color-text) 60%, transparent);
    transition: color .14s var(--ease);
}
.auth-logout:hover {
    color: var(--color-accent);
}
</style>
