<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border-surface-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-surface-600 dark:bg-surface-900 dark:text-surface-100 dark:focus:border-primary-400 dark:focus:ring-primary-400 transition-colors"
        v-model="model"
        ref="input"
    />
</template>
