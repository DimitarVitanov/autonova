<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link
        :href="href"
        class="navlink"
        :class="{ 'navlink-active': active }"
    >
        <slot />
    </Link>
</template>

<style scoped>
.navlink {
    display: inline-flex;
    align-items: center;
    font-family: var(--font-heading);
    font-weight: 600;
    font-size: 14px;
    line-height: 1.2;
    color: color-mix(in srgb, var(--color-text) 55%, transparent);
    padding: 8px 14px;
    border-radius: var(--radius-pill);
    transition: background .15s var(--ease), color .15s var(--ease);
}
.navlink:hover {
    color: var(--color-text);
    background: color-mix(in srgb, var(--color-text) 5%, transparent);
}
.navlink-active {
    color: var(--color-accent);
    background: var(--color-accent-100);
}
</style>
