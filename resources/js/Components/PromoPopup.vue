<script setup>
/**
 * PromoPopup — site-wide marketing popup that fades in a few seconds after the
 * page loads. On brand with the "dark luxury showroom" system (ink gradient,
 * accent glow, Archivo, pill buttons). Promotes the PRO dealer package.
 *
 * Shows once per visitor (localStorage guard + cooldown) so it never nags, and
 * is skipped on the Pricing page itself (where it would be redundant).
 */
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    // How long after mount (ms) before the popup appears.
    delay: { type: Number, default: 5000 },
    // Don't show again for this many hours after it's been seen/dismissed.
    remindHours: { type: Number, default: 24 },
    // localStorage key used to remember that it has been seen.
    storageKey: { type: String, default: 'an_promo_pro_seen_at' },
    // Optional hero image (falls back to the shipped showroom photo).
    image: { type: String, default: '/images/showroom.jpg' },
});

const page = usePage();
const show = ref(false);
const closeBtn = ref(null);
let timer = null;

const feats = computed(() => [
    t('promo.feat_1', 'Homepage placement & priority ranking'),
    t('promo.feat_2', 'Bulk XML / CSV inventory import'),
    t('promo.feat_3', '10 promotion credits every month'),
    t('promo.feat_4', 'Branded profile with logo & banner'),
]);

function recentlySeen() {
    try {
        const raw = window.localStorage.getItem(props.storageKey);
        if (!raw) return false;
        const seenAt = Number(raw);
        if (!seenAt) return false;
        return Date.now() - seenAt < props.remindHours * 3600 * 1000;
    } catch {
        return false;
    }
}

function remember() {
    try {
        window.localStorage.setItem(props.storageKey, String(Date.now()));
    } catch {
        /* storage disabled / private mode — just skip the guard */
    }
}

function open() {
    show.value = true;
    document.body.style.overflow = 'hidden';
    remember();
    requestAnimationFrame(() => closeBtn.value?.focus());
}

function close() {
    show.value = false;
    document.body.style.overflow = '';
}

function onKeydown(e) {
    if (e.key === 'Escape' && show.value) {
        e.preventDefault();
        close();
    }
}

onMounted(() => {
    // Skip on the pricing page (the popup's destination) and if seen recently.
    const onPricing = (page.url || '').startsWith('/pricing');
    if (onPricing || recentlySeen()) return;

    document.addEventListener('keydown', onKeydown);
    timer = window.setTimeout(open, props.delay);
});

onBeforeUnmount(() => {
    clearTimeout(timer);
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="promo">
            <div
                v-if="show"
                class="promo-overlay"
                role="dialog"
                aria-modal="true"
                aria-labelledby="promo-title"
                @click.self="close"
            >
                <div class="promo-card">
                    <button ref="closeBtn" class="promo-close" type="button" :aria-label="t('promo.close', 'Close')" @click="close">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>

                    <!-- Visual panel -->
                    <div class="promo-visual" :style="{ backgroundImage: `url(${image})` }">
                        <div class="promo-visual-scrim" />
                        <span class="promo-ribbon">{{ t('promo.ribbon', 'Launch week') }}</span>
                        <div class="promo-visual-copy">
                            <span class="promo-eyebrow mono">{{ t('promo.eyebrow', 'For dealers') }}</span>
                            <span class="promo-visual-headline">{{ t('promo.visual_headline', 'Sell faster.\nStand out.') }}</span>
                        </div>
                    </div>

                    <!-- Content panel -->
                    <div class="promo-body">
                        <div class="promo-plan">
                            <span class="promo-plan-name">PRO</span>
                            <span class="promo-plan-tag">{{ t('promo.most_popular', 'Most popular') }}</span>
                        </div>

                        <h3 id="promo-title" class="promo-title">
                            {{ t('promo.title', 'Put your inventory in front of every buyer') }}
                        </h3>
                        <p class="promo-sub">
                            {{ t('promo.sub', 'Upgrade to PRO and get homepage placement, unlimited reach and the tools serious dealers use to close deals.') }}
                        </p>

                        <ul class="promo-feats">
                            <li v-for="(f, i) in feats" :key="i">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6L9 17l-5-5" />
                                </svg>
                                <span>{{ f }}</span>
                            </li>
                        </ul>

                        <div class="promo-price">
                            <span class="promo-price-amount">€79</span>
                            <span class="promo-price-per">{{ t('promo.per', '/ month · up to 60 listings') }}</span>
                        </div>

                        <div class="promo-actions">
                            <Link :href="route('pricing')" class="btn btn-primary btn-lg btn-block" @click="close">
                                {{ t('promo.cta', 'See packages') }}
                            </Link>
                            <button type="button" class="promo-dismiss" @click="close">
                                {{ t('promo.dismiss', 'Maybe later') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.promo-overlay {
    position: fixed;
    inset: 0;
    z-index: 90;
    display: grid;
    place-items: center;
    padding: 20px;
    background: color-mix(in srgb, #0b0c0f 62%, transparent);
    backdrop-filter: blur(6px);
}

.promo-card {
    position: relative;
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    width: min(760px, 100%);
    max-height: calc(100vh - 40px);
    overflow: hidden;
    border-radius: var(--radius-xl);
    border: 1px solid var(--color-ink-line-2);
    background:
        radial-gradient(120% 160% at 100% 0%, color-mix(in srgb, var(--color-accent) 26%, transparent), transparent 55%),
        radial-gradient(80% 120% at 0% 100%, rgba(255, 255, 255, 0.05), transparent 60%),
        var(--color-ink-2);
    box-shadow: var(--shadow-ink);
    color: var(--color-on-ink);
}

/* Close button */
.promo-close {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 3;
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--color-ink-line-2);
    background: rgba(11, 12, 15, 0.45);
    color: var(--color-on-ink);
    cursor: pointer;
    backdrop-filter: blur(6px);
    transition: background .15s var(--ease), border-color .15s var(--ease), transform .15s var(--ease);
}
.promo-close:hover { background: rgba(255, 255, 255, 0.16); border-color: rgba(255, 255, 255, 0.3); transform: rotate(90deg); }

/* Visual panel */
.promo-visual {
    position: relative;
    min-height: 100%;
    background-size: cover;
    background-position: center;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.promo-visual-scrim {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(11, 12, 15, 0.15) 0%, rgba(11, 12, 15, 0.55) 55%, rgba(18, 19, 25, 0.92) 100%);
}
.promo-ribbon {
    position: absolute;
    top: 18px;
    left: 18px;
    z-index: 2;
    font: 800 10px var(--font-heading);
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #fff;
    background: var(--color-accent);
    padding: 6px 11px;
    border-radius: var(--radius-pill);
    box-shadow: var(--shadow-accent);
}
.promo-visual-copy { position: relative; z-index: 2; display: flex; flex-direction: column; gap: 8px; }
.promo-eyebrow { color: rgba(255, 255, 255, 0.66); }
.promo-visual-headline {
    font: 900 26px/1.05 var(--font-heading);
    letter-spacing: -0.02em;
    color: #fff;
    white-space: pre-line;
}

/* Content panel */
.promo-body { padding: 30px 30px 26px; display: flex; flex-direction: column; }
.promo-plan { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.promo-plan-name {
    font: 900 13px var(--font-heading);
    letter-spacing: 0.18em;
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid var(--color-ink-line-2);
    padding: 5px 12px;
    border-radius: var(--radius-pill);
}
.promo-plan-tag {
    font: 700 11px var(--font-heading);
    letter-spacing: 0.04em;
    color: var(--color-accent-300);
}
.promo-title { font: 900 24px/1.12 var(--font-heading); letter-spacing: -0.02em; color: #fff; margin: 0 0 8px; }
.promo-sub { font-size: 13.5px; line-height: 1.6; color: var(--color-on-ink-soft); margin: 0 0 18px; }

.promo-feats { list-style: none; margin: 0 0 20px; padding: 0; display: grid; gap: 10px; }
.promo-feats li { display: flex; align-items: flex-start; gap: 10px; font-size: 13.5px; color: var(--color-on-ink); }
.promo-feats svg {
    flex: none;
    margin-top: 1px;
    color: var(--color-accent-400);
    background: color-mix(in srgb, var(--color-accent) 22%, transparent);
    border-radius: var(--radius-pill);
    padding: 3px;
    box-sizing: content-box;
}

.promo-price { display: flex; align-items: baseline; gap: 8px; margin-bottom: 18px; }
.promo-price-amount { font: 900 30px var(--font-heading); letter-spacing: -0.03em; color: #fff; }
.promo-price-per { font-size: 12.5px; color: var(--color-on-ink-muted); }

.promo-actions { margin-top: auto; display: flex; flex-direction: column; gap: 10px; }
.promo-dismiss {
    background: transparent;
    border: 0;
    cursor: pointer;
    font: 600 12.5px var(--font-heading);
    color: var(--color-on-ink-muted);
    padding: 4px;
    transition: color .15s var(--ease);
}
.promo-dismiss:hover { color: var(--color-on-ink); }

/* Entrance / exit */
.promo-enter-active { transition: opacity .3s var(--ease); }
.promo-leave-active { transition: opacity .25s var(--ease); }
.promo-enter-from, .promo-leave-to { opacity: 0; }
.promo-enter-active .promo-card { transition: transform .38s var(--ease), opacity .38s var(--ease); }
.promo-enter-from .promo-card { transform: translateY(18px) scale(0.96); opacity: 0; }

@media (max-width: 640px) {
    .promo-card { grid-template-columns: 1fr; }
    .promo-visual { min-height: 168px; padding: 20px; }
    .promo-visual-headline { font-size: 22px; }
    .promo-body { padding: 22px 22px 20px; }
    .promo-title { font-size: 21px; }
}

@media (prefers-reduced-motion: reduce) {
    .promo-enter-active, .promo-leave-active,
    .promo-enter-active .promo-card { transition: opacity .2s linear; }
    .promo-enter-from .promo-card { transform: none; }
    .promo-close:hover { transform: none; }
}
</style>
