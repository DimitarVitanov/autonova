<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { t } from '@/lib/i18n.js';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header class="section-header">
            <h4 class="section-title">{{ t('profile.pw_title', 'Update password') }}</h4>
            <p class="text-muted section-desc">
                {{ t('profile.pw_desc', 'Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="form">
            <div class="field">
                <label class="label" for="current_password">{{ t('profile.pw_current', 'Current password') }}</label>
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.current_password" />
            </div>

            <div class="field">
                <label class="label" for="password">{{ t('profile.pw_new', 'New password') }}</label>
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="field">
                <label class="label" for="password_confirmation">{{ t('auth.confirm_password', 'Confirm password') }}</label>
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" />
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
