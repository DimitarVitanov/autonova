<script setup>
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { t } from '@/lib/i18n.js';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const demoAccounts = computed(() => [
    { email: 'admin@autonova.test', role: t('role.admin', 'Admin') },
    { email: 'moderator@autonova.test', role: t('role.moderator', 'Moderator') },
    { email: 'vardar@autonova.test', role: t('nav.role_dealer', 'Dealer') },
    { email: 'marko@autonova.test', role: t('nav.role_private', 'Private') },
]);

const fillDemo = (email) => {
    form.email = email;
    form.password = 'password';
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="t('nav.signin', 'Sign in')" />
    <MarketplaceLayout>
        <div class="auth-page">
        <section class="auth">
            <!-- Left: form -->
            <div class="auth-form-col">
                <div class="auth-form">
                    <div class="mono mono-accent">{{ t('auth.account', 'Account') }}</div>
                    <h2 class="auth-title">{{ t('nav.signin', 'Sign in') }}</h2>
                    <p class="text-muted auth-sub">{{ t('auth.signin_sub', 'One account for private sellers and dealers.') }}</p>

                    <div v-if="status" class="auth-status">{{ status }}</div>

                    <form @submit.prevent="submit" class="auth-fields">
                        <div class="field">
                            <label class="label" for="email">{{ t('auth.email', 'Email') }}</label>
                            <TextInput
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="field">
                            <label class="label" for="password">{{ t('auth.password', 'Password') }}</label>
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="auth-row">
                            <label class="check">
                                <input type="checkbox" v-model="form.remember" />
                                <span class="box"></span>
                                {{ t('auth.remember', 'Remember me') }}
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="auth-link"
                            >
                                {{ t('auth.forgot', 'Forgot password?') }}
                            </Link>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-block btn-lg"
                            :disabled="form.processing"
                        >
                            {{ t('nav.signin', 'Sign in') }}
                        </button>
                    </form>

                    <p class="auth-alt">
                        {{ t('auth.new_here', 'New here?') }}
                        <Link :href="route('register')" class="auth-link">{{ t('auth.register', 'Register') }}</Link>
                    </p>

                    <div class="panel-surface demo">
                        <div class="mono demo-head">{{ t('auth.demo', 'Demo accounts · password: password') }}</div>
                        <div class="demo-grid">
                            <button
                                v-for="acc in demoAccounts"
                                :key="acc.email"
                                type="button"
                                class="demo-btn"
                                @click="fillDemo(acc.email)"
                            >
                                <span class="demo-email">{{ acc.email }}</span>
                                <span class="mono demo-role">{{ acc.role }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: promo -->
            <aside class="auth-promo">
                <div class="mono promo-kicker">{{ t('auth.promo_kicker', 'Why an account') }}</div>
                <h3 class="promo-title">{{ t('auth.promo_title', 'Saved searches, alerts and messages in one place.') }}</h3>
                <p class="promo-text">{{ t('auth.promo_text', 'Follow the vehicles you care about, get notified when the price drops, and message sellers directly — all from a single AutoNova account.') }}</p>
            </aside>
        </section>
        </div>
    </MarketplaceLayout>
</template>

<style scoped>
/* Gradient canvas behind the floating auth card */
.auth-page {
    display: flex;
    justify-content: center;
    padding: 48px 24px;
    background:
        radial-gradient(120% 130% at 88% -10%, color-mix(in srgb, var(--color-accent) 9%, transparent), transparent 52%),
        radial-gradient(90% 90% at 0% 0%, color-mix(in srgb, var(--color-accent) 4%, transparent), transparent 45%),
        var(--color-bg);
}
.auth {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 66vh;
    width: 100%;
    max-width: 1120px;
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

/* Left column */
.auth-form-col {
    display: flex;
    justify-content: center;
    padding: 56px 40px;
    border-right: 1px solid var(--color-hairline);
}
.auth-form {
    width: 100%;
    max-width: 460px;
}
.auth-title {
    font-size: clamp(30px, 3.4vw, 40px);
    letter-spacing: -0.02em;
    margin: 14px 0 8px;
}
.auth-sub {
    font-size: 15px;
    margin: 0 0 24px;
}
.auth-status {
    background: var(--color-success-bg);
    color: var(--color-success);
    font-weight: 600;
    font-size: 13px;
    padding: 11px 14px;
    margin-bottom: 18px;
    border: 1px solid color-mix(in srgb, var(--color-success) 30%, transparent);
    border-radius: var(--radius-md);
}
.auth-fields {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.auth-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.auth-link {
    font: 700 13px var(--font-heading);
}
.auth-alt {
    margin: 18px 0 0;
    font-size: 14px;
    color: color-mix(in srgb, var(--color-text) 70%, transparent);
}

/* Demo accounts */
.demo {
    margin-top: 24px;
    padding: 16px;
}
.demo-head {
    color: var(--color-neutral-700);
    margin-bottom: 12px;
}
.demo-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.demo-btn {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    text-align: left;
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-sm);
    padding: 10px 12px;
    cursor: pointer;
    box-shadow: var(--shadow-xs);
    transition: border-color .14s var(--ease), background .14s var(--ease), box-shadow .14s var(--ease), transform .14s var(--ease);
}
.demo-btn:hover {
    border-color: color-mix(in srgb, var(--color-accent) 45%, transparent);
    background: var(--color-accent-100);
    box-shadow: var(--shadow-sm);
    transform: translateY(-1px);
}
.demo-email {
    font: 600 12px var(--font-heading);
    color: var(--color-text);
    word-break: break-all;
}
.demo-role {
    color: var(--color-accent);
}

/* Right promo — dark showroom */
.auth-promo {
    position: relative;
    background:
        linear-gradient(180deg, rgba(11, 12, 15, 0.34) 0%, rgba(11, 12, 15, 0.86) 76%),
        url('/images/showroom.jpg') center 35% / cover no-repeat,
        var(--color-ink);
    color: var(--color-on-ink);
    padding: 48px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.promo-kicker {
    color: var(--color-accent-300);
    margin-bottom: 18px;
}
.promo-title {
    color: #fff;
    font-size: clamp(26px, 3vw, 38px);
    letter-spacing: -0.02em;
    max-width: 16ch;
    margin: 0 0 16px;
}
.promo-text {
    margin: 0;
    max-width: 42ch;
    color: var(--color-on-ink-soft);
    font-size: 15px;
}

@media (max-width: 800px) {
    .auth {
        grid-template-columns: 1fr;
    }
    .auth-form-col {
        border-right: 0;
        padding: 40px 24px;
    }
    .auth-promo {
        padding: 40px 24px;
        min-height: 300px;
        border-top: 1px solid var(--color-hairline);
    }
}
@media (max-width: 480px) {
    .demo-grid {
        grid-template-columns: 1fr;
    }
}
</style>
