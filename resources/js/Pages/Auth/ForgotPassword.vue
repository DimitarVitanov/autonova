<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.forgot_title', 'Forgot Password')" />

        <div class="auth-intro">
            {{ t('auth.forgot_intro', 'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        <div
            v-if="status"
            class="auth-status"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="t('auth.email', 'Email')" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ t('auth.send_reset', 'Email Password Reset Link') }}
                </PrimaryButton>
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
</style>
