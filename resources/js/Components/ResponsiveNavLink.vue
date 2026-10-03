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
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link
        :href="href"
        class="rnavlink"
        :class="{ 'rnavlink-active': active }"
    >
        <slot />
    </Link>
</template>

<style scoped>
.rnavlink {
    display: block;
    width: 100%;
    text-align: start;
    padding: 10px 16px;
    border: 0;
    border-radius: var(--radius-md);
    background: transparent;
    font-family: var(--font-heading);
    font-weight: 600;
    font-size: 15px;
    color: color-mix(in srgb, var(--color-text) 60%, transparent);
    transition: background .15s var(--ease), color .15s var(--ease);
}
.rnavlink:hover {
    color: var(--color-text);
    background: var(--color-surface);
}
.rnavlink-active {
    color: var(--color-accent);
    background: var(--color-accent-100);
}
</style>
