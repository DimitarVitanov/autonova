<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { t } from '@/lib/i18n.js';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeConfirm(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeConfirm = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <header class="section-header">
            <h4 class="section-title">{{ t('profile.del_title', 'Delete account') }}</h4>
            <p class="text-muted section-desc">
                {{ t('profile.del_desc', 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>
        </header>

        <button
            v-if="!confirmingUserDeletion"
            type="button"
            class="btn btn-danger"
            @click="confirmUserDeletion"
        >
            {{ t('profile.del_title', 'Delete account') }}
        </button>

        <div v-else class="confirm panel-surface">
            <h5 class="confirm-title">{{ t('profile.del_confirm_title', 'Are you sure you want to delete your account?') }}</h5>
            <p class="text-muted confirm-text">
                {{ t('profile.del_confirm_text', 'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="field confirm-field">
                <label class="label" for="delete_password">{{ t('auth.password', 'Password') }}</label>
                <TextInput
                    id="delete_password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    :placeholder="t('auth.password', 'Password')"
                    @keyup.enter="deleteUser"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="confirm-actions">
                <button type="button" class="btn btn-secondary" @click="closeConfirm">
                    {{ t('profile.cancel', 'Cancel') }}
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    :disabled="form.processing"
                    @click="deleteUser"
                >
                    {{ t('profile.del_title', 'Delete account') }}
                </button>
            </div>
        </div>
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
    max-width: 60ch;
}
.confirm {
    padding: 22px;
    max-width: 480px;
}
.confirm-title {
    margin: 0 0 8px;
}
.confirm-text {
    margin: 0 0 16px;
    font-size: 13px;
}
.confirm-field {
    max-width: 320px;
    margin-bottom: 18px;
}
.confirm-actions {
    display: flex;
    gap: 12px;
}
</style>
