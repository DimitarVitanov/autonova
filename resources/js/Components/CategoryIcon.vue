<script setup>
defineProps({
    name: { type: String, default: 'car' },
    size: { type: Number, default: 24 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <!-- car -->
        <template v-if="name === 'car'">
            <path d="M3 13l1.6-4.2A2 2 0 0 1 6.5 7.5h11a2 2 0 0 1 1.9 1.3L21 13" />
            <path d="M3 13h18v4a1 1 0 0 1-1 1h-1.5" />
            <path d="M5.5 18H4a1 1 0 0 1-1-1v-4" />
            <circle cx="7" cy="18" r="1.8" />
            <circle cx="17" cy="18" r="1.8" />
        </template>
        <!-- motorcycle -->
        <template v-else-if="name === 'motorcycle'">
            <circle cx="5" cy="16" r="3.2" />
            <circle cx="19" cy="16" r="3.2" />
            <path d="M5 16l3-5h5l2 2.5" />
            <path d="M8 11l-1-2H5" />
            <path d="M15.5 13.5L18 9h2" />
        </template>
        <!-- van -->
        <template v-else-if="name === 'van'">
            <path d="M2 16V8a1 1 0 0 1 1-1h10l4 3.5V16" />
            <path d="M2 16h2M9 16h6M20 16h2" />
            <path d="M13 7v4h6" />
            <circle cx="6.5" cy="16.5" r="1.8" />
            <circle cx="17.5" cy="16.5" r="1.8" />
        </template>
        <!-- truck -->
        <template v-else-if="name === 'truck'">
            <path d="M2 15V6h11v9" />
            <path d="M13 9h4l3 3v3h-2" />
            <path d="M2 15h3M11 15h3" />
            <circle cx="7" cy="16" r="1.8" />
            <circle cx="17.5" cy="16" r="1.8" />
        </template>
        <!-- tractor -->
        <template v-else-if="name === 'tractor'">
            <circle cx="7" cy="16" r="3.4" />
            <circle cx="18" cy="17" r="2.2" />
            <path d="M4 13V9h4l1.5 3.5" />
            <path d="M10 13h6l1 2" />
            <path d="M8 6h3v3" />
        </template>
        <!-- trailer -->
        <template v-else-if="name === 'trailer'">
            <path d="M3 15V8a1 1 0 0 1 1-1h13a1 1 0 0 1 1 1v7" />
            <path d="M3 15h5M12 15h9" />
            <path d="M19 15h3" />
            <circle cx="10" cy="16" r="1.8" />
        </template>
        <!-- fallback -->
        <template v-else>
            <rect x="3" y="6" width="18" height="12" rx="1" />
        </template>
    </svg>
</template>
