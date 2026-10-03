<script setup>
defineProps({ message: { type: String } });
</script>

<template>
    <div v-show="message">
        <p class="input-error">{{ message }}</p>
    </div>
</template>

<style scoped>
.input-error {
    margin: 4px 0 0;
    font-size: 12px;
    color: var(--color-accent-700);
}
</style>
