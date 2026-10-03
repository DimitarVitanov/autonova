<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import PromoPopup from '@/Components/PromoPopup.vue';
import { t } from '@/lib/i18n.js';

const page = usePage();
const auth = computed(() => page.props.auth.user);
const locale = computed(() => page.props.i18n?.locale ?? 'mk');
const counts = computed(() => page.props.counts ?? { favorites: 0, unread_messages: 0 });
const categories = computed(() => page.props.nav?.categories ?? []);

// Flash toast
const toast = ref(null);
let timer = null;
watch(
    () => page.props.flash,
    (flash) => {
        const message = flash?.success || flash?.error;
        if (message) {
            toast.value = { type: flash.success ? 'success' : 'error', message };
            clearTimeout(timer);
            timer = setTimeout(() => (toast.value = null), 3500);
        }
    },
    { deep: true, immediate: true },
);

const logout = () => router.post(route('logout'));

// Mobile drawer state. Teleport the nav to <body> only on mobile so the
// full-height fixed drawer escapes the header's stacking/backdrop-filter context.
const menuOpen = ref(false);
const isMobile = ref(false);
let mq = null;
const applyMq = (e) => {
    isMobile.value = e.matches;
    if (!e.matches) menuOpen.value = false; // reset when resizing up to desktop
};
const onEsc = (e) => { if (e.key === 'Escape') menuOpen.value = false; };

onMounted(() => {
    mq = window.matchMedia('(max-width: 900px)');
    isMobile.value = mq.matches;
    mq.addEventListener('change', applyMq);
    document.addEventListener('keydown', onEsc);
});
onBeforeUnmount(() => {
    mq?.removeEventListener('change', applyMq);
    document.removeEventListener('keydown', onEsc);
    document.body.style.overflow = '';
});

// Lock background scroll while the drawer is open.
watch(menuOpen, (open) => {
    document.body.style.overflow = open && isMobile.value ? 'hidden' : '';
});
</script>

<template>
    <div class="app">
        <!-- Header -->
        <header class="header">
            <div class="header-inner wrap">
                <Link :href="route('home')" class="brand"><Logo :size="24" inverse /></Link>

                <Teleport to="body" :disabled="!isMobile">
                    <Transition name="drawer-fade">
                        <div v-if="isMobile && menuOpen" class="nav-backdrop" @click="menuOpen = false" />
                    </Transition>

                    <nav class="nav-links" :class="{ open: menuOpen }" @click="menuOpen = false">
                    <div class="drawer-head">
                        <Link :href="route('home')" class="brand"><Logo :size="22" inverse /></Link>
                        <button class="drawer-close" type="button" :aria-label="t('nav.close_menu', 'Close menu')" @click.stop="menuOpen = false">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
                        </button>
                    </div>
                    <Link :href="route('vehicles.index')" class="nav-link">{{ t('nav.vehicles', 'Vehicles') }}</Link>
                    <Link :href="route('dealers.index')" class="nav-link nav-spot">{{ t('nav.dealers', 'Dealers') }}</Link>
                    <Link :href="route('pricing')" class="nav-link">{{ t('nav.packages', 'Packages') }}</Link>

                    <!-- Actions inside the mobile burger menu -->
                    <div class="nav-mobile-actions">
                        <template v-if="auth">
                            <Link :href="route('dashboard')" class="nav-link">{{ t('nav.dashboard', 'Dashboard') }}</Link>
                            <Link :href="route('saved')" class="nav-link">{{ t('nav.saved', 'Saved') }}</Link>
                            <Link :href="route('inbox')" class="nav-link">{{ t('nav.inbox', 'Inbox') }}</Link>
                            <Link :href="route('profile.edit')" class="nav-link">{{ t('nav.profile', 'Profile') }}</Link>
                            <Link :href="route('vehicles.create')" class="nav-link accent">{{ t('nav.sell', 'Sell your car') }}</Link>
                            <button class="nav-link" @click="logout">{{ t('nav.logout', 'Log out') }}</button>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="nav-link">{{ t('nav.signin', 'Sign in') }}</Link>
                            <Link :href="route('vehicles.create')" class="nav-link accent">{{ t('nav.sell', 'Sell your car') }}</Link>
                        </template>
                    </div>
                    </nav>
                </Teleport>

                <div class="header-actions">
                    <div class="lang-switch">
                        <Link :href="route('locale.switch', 'mk')" class="lang-opt" :class="{ active: locale === 'mk' }">МК</Link>
                        <Link :href="route('locale.switch', 'en')" class="lang-opt" :class="{ active: locale === 'en' }">EN</Link>
                    </div>
                    <template v-if="auth">
                        <Link :href="route('saved')" class="chip">
                            {{ t('nav.saved', 'Saved') }}<span v-if="counts.favorites" class="badge-dot">{{ counts.favorites }}</span>
                        </Link>
                        <Link :href="route('inbox')" class="chip">
                            {{ t('nav.inbox', 'Inbox') }}<span v-if="counts.unread_messages" class="badge-dot">{{ counts.unread_messages }}</span>
                        </Link>

                        <details class="menu">
                            <summary class="user-btn">
                                <span class="avatar">{{ auth.name.charAt(0) }}</span>
                                <span class="user-name">{{ auth.name.split(' ')[0] }}</span>
                            </summary>
                            <div class="menu-panel">
                                <div class="menu-head">
                                    <div class="menu-name">{{ auth.name }}</div>
                                    <div class="menu-role">{{ auth.is_dealer ? t('nav.role_dealer', 'Dealer') : t('nav.role_private', 'Private') }} · {{ t('role.' + auth.role, auth.role) }}</div>
                                </div>
                                <Link :href="route('dashboard')" class="menu-item">{{ t('nav.dashboard', 'Dashboard') }}</Link>
                                <Link :href="route('saved')" class="menu-item">{{ t('nav.saved_vehicles', 'Saved vehicles') }}</Link>
                                <Link :href="route('inbox')" class="menu-item">{{ t('nav.messages', 'Messages') }}</Link>
                                <Link :href="route('profile.edit')" class="menu-item">{{ t('nav.profile', 'Profile') }}</Link>
                                <Link v-if="auth.is_staff" :href="route('admin.dashboard')" class="menu-item accent">{{ t('nav.admin', 'Admin panel') }}</Link>
                                <button class="menu-item" @click="logout">{{ t('nav.logout', 'Log out') }}</button>
                            </div>
                        </details>
                        <Link :href="route('vehicles.create')" class="btn btn-primary">{{ t('nav.sell', 'Sell your car') }}</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn-secondary">{{ t('nav.signin', 'Sign in') }}</Link>
                        <Link :href="route('vehicles.create')" class="btn btn-primary">{{ t('nav.sell', 'Sell your car') }}</Link>
                    </template>

                    <button class="burger" @click="menuOpen = !menuOpen" :aria-label="t('nav.menu', 'Menu')">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Marketing promo popup (appears a few seconds after load, once per visitor) -->
        <PromoPopup />

        <!-- Flash toast -->
        <Transition name="toast">
            <div v-if="toast" class="toast" :class="`toast-${toast.type}`">{{ toast.message }}</div>
        </Transition>

        <main class="main">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="wrap footer-inner">
                <div class="footer-brand">
                    <Logo :size="20" inverse />
                    <p class="footer-tag">{{ t('footer.tag', 'Macedonia’s marketplace for vehicles. Verified dealers, real photos, no duplicate listings.') }}</p>
                </div>
                <div class="footer-col">
                    <span class="mono">{{ t('footer.categories', 'Categories') }}</span>
                    <Link v-for="c in categories" :key="c.id" :href="route('vehicles.index', { category: c.slug })" class="link-quiet">{{ t('cat.' + c.slug, c.name_plural) }}</Link>
                </div>
                <div class="footer-col">
                    <span class="mono">{{ t('footer.for_dealers', 'For dealers') }}</span>
                    <Link :href="route('pricing')" class="link-quiet">{{ t('footer.packages', 'Packages') }}</Link>
                    <Link :href="route('dashboard')" class="link-quiet">{{ t('footer.inventory', 'Inventory') }}</Link>
                    <Link :href="route('dealers.index')" class="link-quiet">{{ t('footer.dealer_directory', 'Dealer directory') }}</Link>
                </div>
                <div class="footer-col">
                    <span class="mono">{{ t('footer.company', 'Company') }}</span>
                    <span class="link-quiet">{{ t('footer.about', 'About AutoNova') }}</span>
                    <span class="link-quiet">{{ t('footer.terms', 'Terms & privacy') }}</span>
                    <span class="text-muted footer-lang">Македонски · Shqip · English</span>
                </div>
            </div>
            <div class="wrap footer-bottom">
                <span class="text-muted">© {{ new Date().getFullYear() }} AutoNova</span>
                <span class="text-muted">{{ t('footer.built', 'Built for the Balkans') }}</span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.app { min-height: 100vh; display: flex; flex-direction: column; }

/* Header — dark showroom bar; same ink + dark-red glow as the hero so it blends */
.header {
    position: sticky; top: 0; z-index: 40;
    background:
        radial-gradient(110% 320% at 84% 130%, color-mix(in srgb, var(--color-accent) 32%, transparent), transparent 60%),
        radial-gradient(60% 240% at 5% 0%, rgba(255, 255, 255, 0.05), transparent 60%),
        color-mix(in srgb, var(--color-ink) 90%, transparent);
    backdrop-filter: saturate(1.4) blur(16px);
    border-bottom: 1px solid transparent;
}
.header-inner { display: flex; align-items: center; gap: 24px; padding: 14px 32px; }
.brand { display: inline-flex; }
.nav-links { display: flex; align-items: center; gap: 4px; margin-right: auto; margin-left: 14px; }
.nav-link { font-family: var(--font-heading); font-weight: 600; font-size: 14px; color: var(--color-on-ink-soft); padding: 8px 14px; border-radius: var(--radius-pill); transition: background .15s var(--ease), color .15s var(--ease);
    /* reset UA button styling so <button class="nav-link"> (Log out) isn't a white box on the dark drawer */
    appearance: none; -webkit-appearance: none; background: transparent; border: 0; cursor: pointer; text-align: left; }
.nav-link:hover { color: #fff; background: rgba(255, 255, 255, 0.07); }

/* Spotlighted nav item (Dealers): gold, pulsing ring, arrows nudging in from both sides */
.nav-spot {
    position: relative; margin: 0 22px; padding: 8px 18px;
    color: #ffc247; font-weight: 800;
    background: rgba(255, 194, 71, 0.12);
    border: 1px solid rgba(255, 194, 71, 0.55);
    animation: nav-spot-pulse 1.8s ease-out infinite;
}
.nav-spot:hover { color: #14161b; background: #ffc247; }
.nav-spot::before, .nav-spot::after {
    position: absolute; top: 50%; margin-top: -11px;
    font-size: 18px; line-height: 22px; font-weight: 800; color: #ffc247;
    pointer-events: none;
}
.nav-spot::before { content: '»'; right: 100%; margin-right: 5px; animation: nav-spot-left 0.9s ease-in-out infinite; }
.nav-spot::after { content: '«'; left: 100%; margin-left: 5px; animation: nav-spot-right 0.9s ease-in-out infinite; }
@keyframes nav-spot-pulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 194, 71, 0.6); }
    70% { box-shadow: 0 0 0 12px rgba(255, 194, 71, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 194, 71, 0); }
}
@keyframes nav-spot-left { 0%, 100% { transform: translateX(-5px); opacity: .45; } 50% { transform: translateX(0); opacity: 1; } }
@keyframes nav-spot-right { 0%, 100% { transform: translateX(5px); opacity: .45; } 50% { transform: translateX(0); opacity: 1; } }
@media (prefers-reduced-motion: reduce) {
    .nav-spot, .nav-spot::before, .nav-spot::after { animation: none; }
}
.header-actions { display: flex; align-items: center; gap: 8px; }
.lang-switch { display: inline-flex; align-items: center; gap: 2px; padding: 3px; border: 1px solid var(--color-ink-line-2); border-radius: var(--radius-pill); background: rgba(255, 255, 255, 0.04); }
.lang-opt { font: 800 11px var(--font-heading); color: var(--color-on-ink-muted); padding: 5px 9px; border-radius: var(--radius-pill); letter-spacing: 0.04em; line-height: 1; }
.lang-opt:hover { color: #fff; }
.lang-opt.active { background: var(--color-accent); color: #fff; }
.chip {
    display: inline-flex; align-items: center; gap: 6px; font-family: var(--font-heading);
    font-weight: 600; font-size: 13px; color: var(--color-on-ink-soft); padding: 9px 13px;
    border-radius: var(--radius-pill); transition: background .15s var(--ease), color .15s var(--ease);
}
.chip:hover { color: #fff; background: rgba(255, 255, 255, 0.07); }

/* User menu (details/summary) */
.menu { position: relative; }
.menu summary { list-style: none; cursor: pointer; }
.menu summary::-webkit-details-marker { display: none; }
.user-btn { display: inline-flex; align-items: center; gap: 8px; padding: 5px 12px 5px 5px; border: 1px solid var(--color-ink-line-2); border-radius: var(--radius-pill); background: rgba(255, 255, 255, 0.06); transition: background .15s var(--ease), border-color .15s var(--ease); }
.user-btn:hover { background: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.28); }
.avatar { width: 28px; height: 28px; display: grid; place-items: center; background: var(--color-accent); color: #fff; font: 800 13px var(--font-heading); border-radius: var(--radius-pill); }
.user-name { font: 700 13px var(--font-heading); color: var(--color-on-ink); }
.menu-panel { position: absolute; right: 0; top: calc(100% + 10px); width: 232px; background: var(--color-card); border: 1px solid var(--color-hairline); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); display: flex; flex-direction: column; overflow: hidden; z-index: 50; }
.menu-head { padding: 14px 16px; border-bottom: 1px solid var(--color-hairline); }
.menu-name { font: 800 14px var(--font-heading); }
.menu-role { font-size: 11px; text-transform: capitalize; color: var(--color-neutral-600); }
.menu-item { text-align: left; padding: 11px 16px; font: 600 13px var(--font-heading); color: var(--color-text); background: transparent; border: 0; border-bottom: 1px solid var(--color-hairline); cursor: pointer; transition: background .13s var(--ease), color .13s var(--ease); }
.menu-item:last-child { border-bottom: 0; }
.menu-item:hover { background: var(--color-surface); color: var(--color-accent); }
.menu-item.accent { color: var(--color-accent); }

.burger { display: none; flex-direction: column; gap: 4px; background: rgba(255, 255, 255, 0.06); border: 1px solid var(--color-ink-line-2); border-radius: var(--radius-md); padding: 10px 9px; cursor: pointer; }
.burger span { width: 18px; height: 2px; background: var(--color-on-ink); border-radius: 2px; }

/* Toast */
.toast { position: fixed; top: 82px; right: 24px; z-index: 60; padding: 14px 20px; font: 700 13px var(--font-heading); color: #fff; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); max-width: 340px; }
.toast-success { background: var(--color-success); }
.toast-error { background: var(--color-accent-700); }
.toast-enter-active, .toast-leave-active { transition: all .25s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(-8px); }

.main { flex: 1; }

/* Footer — dark showroom bookend */
.footer { margin-top: auto; background: var(--color-ink); color: var(--color-on-ink); }
.footer-inner { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 32px; padding: 56px 32px 32px; }
.footer-brand { max-width: 320px; }
.footer-tag { font-size: 13px; margin-top: 14px; color: var(--color-on-ink-muted); line-height: 1.6; }
.footer-col { display: flex; flex-direction: column; gap: 11px; font-size: 13px; }
.footer-col .mono { margin-bottom: 5px; color: var(--color-on-ink-muted); }
.footer-col .link-quiet { color: var(--color-on-ink-soft); }
.footer-col .link-quiet:hover { color: #fff; }
.footer-lang { font-size: 11px; margin-top: 4px; color: var(--color-on-ink-muted); }
.footer-bottom { display: flex; justify-content: space-between; padding: 20px 32px; border-top: 1px solid var(--color-ink-line); font-size: 12px; }
.footer-bottom .text-muted { color: var(--color-on-ink-muted) !important; }

/* Mobile-only bits are hidden on desktop; the nav is a plain inline row there. */
.nav-mobile-actions { display: none; }
.drawer-head { display: none; }

/* Dimmed backdrop behind the drawer (teleported to <body> on mobile) */
.nav-backdrop {
    position: fixed; inset: 0; z-index: 60;
    background: rgba(6, 7, 9, 0.58);
    backdrop-filter: blur(2px);
}
.drawer-fade-enter-active, .drawer-fade-leave-active { transition: opacity .3s var(--ease); }
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }

@media (max-width: 900px) {
    .header-inner { padding: 12px 18px; }
    .brand { margin-right: auto; }

    /* Full-height drawer that slides in from the left */
    .nav-links {
        position: fixed; top: 0; left: 0; bottom: 0; /* top+bottom => full viewport height */
        width: min(86vw, 340px);
        margin: 0; z-index: 61;
        flex-direction: column; align-items: stretch; gap: 0;
        background: var(--color-ink-2);
        border-right: 1px solid var(--color-ink-line);
        box-shadow: var(--shadow-ink);
        overflow-y: auto; -webkit-overflow-scrolling: touch;
        transform: translateX(-100%);
        transition: transform .32s var(--ease);
        padding-bottom: max(24px, env(safe-area-inset-bottom));
    }
    .nav-links.open { transform: translateX(0); }

    .drawer-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 18px; margin-bottom: 4px;
        border-bottom: 1px solid var(--color-ink-line);
    }
    .drawer-close {
        display: grid; place-items: center; width: 38px; height: 38px;
        background: rgba(255, 255, 255, 0.06); border: 1px solid var(--color-ink-line-2);
        border-radius: var(--radius-pill); color: var(--color-on-ink); cursor: pointer;
        transition: background .15s var(--ease), transform .15s var(--ease);
    }
    .drawer-close:hover { background: rgba(255, 255, 255, 0.14); transform: rotate(90deg); }

    .nav-links .nav-link { width: 100%; padding: 15px 22px; border-bottom: 1px solid var(--color-ink-line); border-radius: 0; }
    .nav-links .nav-spot { margin: 0; border-width: 0 0 1px; animation: none; background: rgba(255, 194, 71, 0.12); }
    .nav-links .nav-spot::before { position: static; display: inline-block; margin: 0 8px 0 0; }
    .nav-links .nav-spot::after { position: static; display: inline-block; margin: 0 0 0 8px; }
    .nav-mobile-actions { display: flex; flex-direction: column; width: 100%; border-top: 3px solid var(--color-ink); margin-top: 4px; }
    .nav-mobile-actions .nav-link.accent { color: var(--color-accent); }
    .burger { display: flex; }
    /* fold the desktop actions into the drawer */
    .header-actions > .chip, .header-actions > .menu, .header-actions > .btn { display: none; }
    .footer-inner { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 560px) {
    .footer-inner { grid-template-columns: 1fr; }
    .footer-bottom { flex-direction: column; gap: 6px; text-align: center; }
}
</style>
