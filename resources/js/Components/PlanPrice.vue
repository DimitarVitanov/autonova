<script setup>
/**
 * A package / promotion price. During the free launch period it reads "Free"
 * with the regular price struck through beside it. Sizes are relative, so it
 * fits every price style it is dropped into.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { t } from '@/lib/i18n.js';

const props = defineProps({
    price: { type: String, required: true },
});

const page = usePage();
const isFree = computed(() => Boolean(page.props.launchOffer?.active));
// "€0" is already free — nothing to strike through.
const hasRegularPrice = computed(() => !/^\D*0+([.,]0+)?$/.test(props.price.trim()));
</script>

<template>
    <span v-if="isFree" class="plan-price-free">
        {{ t('launch.free', 'Free') }}<s v-if="hasRegularPrice" class="plan-price-was">{{ price }}</s>
    </span>
    <span v-else>{{ price }}</span>
</template>

<style scoped>
.plan-price-was {
    margin-left: 0.35em;
    font-size: 0.45em;
    font-weight: 700;
    letter-spacing: 0;
    vertical-align: 0.4em;
    opacity: 0.55;
}
</style>
