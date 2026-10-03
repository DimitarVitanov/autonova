<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="section-header">
            <h4 class="section-title">{{ t('profile.info_title', 'Profile information') }}</h4>
            <p class="text-muted section-desc">
                {{ t('profile.info_desc', 'Update your account’s profile information and email address.') }}
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="form">
            <div class="field">
                <label class="label" for="name">{{ t('auth.name', 'Name') }}</label>
                <TextInput
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="field">
                <label class="label" for="email">{{ t('auth.email', 'Email') }}</label>
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="verify">
                <p class="verify-text">
                    {{ t('profile.unverified', 'Your email address is unverified.') }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="verify-link"
                    >
                        {{ t('profile.resend', 'Click here to re-send the verification email.') }}
                    </Link>
                </p>
                <div v-show="status === 'verification-link-sent'" class="verify-sent">
                    {{ t('profile.link_sent', 'A new verification link has been sent to your email address.') }}
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    {{ t('profile.save', 'Save') }}
                </button>
                <Transition name="fade">
                    <span v-if="form.recentlySuccessful" class="saved">{{ t('profile.saved', 'Saved.') }}</span>
                </Transition>
            </div>
        </form>
    </section>
</template>

<style scoped>
.section-header {
    margin-bottom: 22px;
}
.section-title {
    margin: 0 0 6px;
}
.section-desc {
    margin: 0;
    font-size: 14px;
}
.form {
    display: flex;
    flex-direction: column;
    gap: 18px;
    max-width: 460px;
}
.verify {
    background: var(--color-surface);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-md);
    padding: 14px 16px;
}
.verify-text {
    margin: 0;
    font-size: 13px;
}
.verify-link {
    background: transparent;
    border: 0;
    padding: 0;
    cursor: pointer;
    color: var(--color-accent);
    font: 700 13px var(--font-heading);
    text-decoration: underline;
    text-underline-offset: 3px;
}
.verify-sent {
    margin-top: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-success);
}
.form-actions {
    display: flex;
    align-items: center;
    gap: 14px;
}
.saved {
    font-size: 13px;
    color: color-mix(in srgb, var(--color-text) 60%, transparent);
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
