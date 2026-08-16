<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-surface-300 text-primary-700 shadow-sm focus:ring-primary-600 dark:border-surface-600 dark:bg-surface-800 dark:text-primary-500 dark:focus:ring-primary-500 dark:focus:ring-offset-surface-900 transition-colors cursor-pointer"
    />
</template>
