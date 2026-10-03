<script setup>
import Logo from '@/Components/Logo.vue';
import { Link } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';
</script>

<template>
    <div class="guest">
        <Link :href="route('home')" class="guest-brand">
            <Logo :size="26" inverse />
        </Link>

        <div class="guest-card">
            <slot />
        </div>

        <div class="mono guest-foot">AutoNova · {{ t('auth.guest_foot', 'Vehicle marketplace') }}</div>
    </div>
</template>

<style scoped>
.guest {
    min-height: 100vh;
    background:
        radial-gradient(90% 90% at 85% -10%, color-mix(in srgb, var(--color-accent) 22%, transparent), transparent 52%),
        linear-gradient(180deg, rgba(11, 12, 15, 0.88), rgba(11, 12, 15, 0.97)),
        url('/images/showroom.jpg') center / cover no-repeat,
        var(--color-ink);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 22px;
    padding: 40px 20px;
}
.guest-brand {
    display: inline-flex;
}
.guest-card {
    width: 100%;
    max-width: 460px;
    padding: 36px;
    background: var(--color-card);
    border: 1px solid var(--color-hairline);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
}
.guest-foot {
    color: var(--color-on-ink-muted);
}
</style>
