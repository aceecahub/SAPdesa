<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (v) => ['baru', 'diproses', 'selesai', 'ditolak'].includes(v),
    },
});

const config = computed(() => {
    const map = {
        baru: {
            label: 'Baru',
            class: 'bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/80',
            dot: 'bg-amber-500 animate-pulse',
        },
        diproses: {
            label: 'Diproses',
            class: 'bg-blue-50 text-blue-700 border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/80',
            dot: 'bg-blue-500 animate-pulse',
        },
        selesai: {
            label: 'Selesai',
            class: 'bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/80',
            dot: 'bg-emerald-500',
        },
        ditolak: {
            label: 'Ditolak',
            class: 'bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/80',
            dot: 'bg-rose-500',
        },
    };
    return map[props.status] || {
        label: props.status,
        class: 'bg-surface-100 text-surface-700 border-surface-200 dark:bg-surface-700 dark:text-surface-300 dark:border-surface-600',
        dot: 'bg-surface-400',
    };
});
</script>

<template>
    <span :class="['inline-flex items-center px-2.5 py-1 text-xs font-semibold border rounded-full shadow-xs tracking-wide', config.class]">
        <span :class="['w-1.5 h-1.5 rounded-full mr-1.5 shrink-0', config.dot]"></span>
        {{ config.label }}
    </span>
</template>
