<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);
const props = defineProps({
    checked: { type: [Array, Boolean], required: true },
    value: { default: null },
});

const proxyChecked = computed({
    get: () => props.checked,
    set: (val) => emit('update:checked', val),
});
</script>

<template>
    <input type="checkbox" :value="value" v-model="proxyChecked" class="native-check" />
</template>

<style scoped>
.native-check {
    width: 17px;
    height: 17px;
    accent-color: var(--color-accent);
    cursor: pointer;
}
</style>
