<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.confirm_title', 'Confirm Password')" />

        <div class="auth-intro">
            {{ t('auth.confirm_intro', 'This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" :value="t('auth.password', 'Password')" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-end">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ t('auth.confirm', 'Confirm') }}
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
</style>
