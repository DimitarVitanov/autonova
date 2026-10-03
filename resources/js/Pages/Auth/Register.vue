<script setup>
import MarketplaceLayout from '@/Layouts/MarketplaceLayout.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const cities = ['Skopje', 'Bitola', 'Kumanovo', 'Prilep', 'Tetovo', 'Veles', 'Ohrid', 'Gostivar'];

const form = useForm({
    name: '',
    email: '',
    account_type: 'private',
    dealer_name: '',
    phone: '',
    city: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="t('auth.create_title', 'Create your account')" />
    <MarketplaceLayout>
        <div class="auth-page">
        <section class="auth">
            <!-- Left: form -->
            <div class="auth-form-col">
                <div class="auth-form">
                    <div class="mono mono-accent">{{ t('auth.reg_kicker', 'Register') }}</div>
                    <h2 class="auth-title">{{ t('auth.create_title', 'Create your account') }}</h2>
                    <p class="text-muted auth-sub">{{ t('auth.reg_sub', 'Sell privately or run your dealership — one place for everything.') }}</p>

                    <form @submit.prevent="submit" class="auth-fields">
                        <div class="field">
                            <label class="label">{{ t('auth.account_type', 'Account type') }}</label>
                            <div class="seg">
                                <label class="seg-opt">
                                    <input type="radio" value="private" v-model="form.account_type" />
                                    {{ t('common.private_seller', 'Private seller') }}
                                </label>
                                <label class="seg-opt">
                                    <input type="radio" value="dealer" v-model="form.account_type" />
                                    {{ t('common.dealer', 'Dealer') }}
                                </label>
                            </div>
                            <InputError :message="form.errors.account_type" />
                        </div>

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

                        <div v-if="form.account_type === 'dealer'" class="field">
                            <label class="label" for="dealer_name">{{ t('auth.dealership_name', 'Dealership name') }}</label>
                            <TextInput
                                id="dealer_name"
                                type="text"
                                v-model="form.dealer_name"
                                autocomplete="organization"
                            />
                            <InputError :message="form.errors.dealer_name" />
                        </div>

                        <div class="auth-two">
                            <div class="field">
                                <label class="label" for="phone">{{ t('auth.phone', 'Phone') }}</label>
                                <TextInput
                                    id="phone"
                                    type="tel"
                                    v-model="form.phone"
                                    autocomplete="tel"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="field">
                                <label class="label" for="city">{{ t('search.city', 'City') }}</label>
                                <select id="city" class="input" v-model="form.city">
                                    <option value="">{{ t('auth.select_city', 'Select city') }}</option>
                                    <option v-for="c in cities" :key="c" :value="c">{{ t('city.' + c, c) }}</option>
                                </select>
                                <InputError :message="form.errors.city" />
                            </div>
                        </div>

                        <div class="auth-two">
                            <div class="field">
                                <label class="label" for="password">{{ t('auth.password', 'Password') }}</label>
                                <TextInput
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="field">
                                <label class="label" for="password_confirmation">{{ t('auth.confirm_password', 'Confirm password') }}</label>
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-block btn-lg"
                            :disabled="form.processing"
                        >
                            {{ t('auth.create_account', 'Create account') }}
                        </button>
                    </form>

                    <p class="auth-alt">
                        {{ t('auth.have_account', 'Already have an account?') }}
                        <Link :href="route('login')" class="auth-link">{{ t('nav.signin', 'Sign in') }}</Link>
                    </p>
                </div>
            </div>

            <!-- Right: promo -->
            <aside class="auth-promo">
                <div class="mono promo-kicker">{{ t('prc.kicker', 'For dealers') }}</div>
                <h3 class="promo-title">{{ t('auth.reg_promo_title', 'Dealers get a profile, inventory and per-listing analytics.') }}</h3>
                <p class="promo-text">{{ t('auth.reg_promo_text', 'Import your stock, keep every listing in one dashboard, and see exactly how each vehicle performs. Private sellers keep it simple — list a car in minutes.') }}</p>
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
.auth-fields {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.auth-two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.seg {
    display: flex;
    width: 100%;
}
.seg-opt {
    flex: 1;
    justify-content: center;
}
.auth-link {
    font: 700 13px var(--font-heading);
}
.auth-alt {
    margin: 18px 0 0;
    font-size: 14px;
    color: color-mix(in srgb, var(--color-text) 70%, transparent);
}

/* Right promo */
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
    .auth-two {
        grid-template-columns: 1fr;
    }
}
</style>
