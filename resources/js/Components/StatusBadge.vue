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
        baru: { label: 'Baru', class: 'bg-amber-50 text-amber-800 border-amber-200' },
        diproses: { label: 'Diproses', class: 'bg-blue-50 text-blue-800 border-blue-200' },
        selesai: { label: 'Selesai', class: 'bg-primary-50 text-primary-800 border-primary-200' },
        ditolak: { label: 'Ditolak', class: 'bg-red-50 text-red-800 border-red-200' },
    };
    return map[props.status] || { label: props.status, class: 'bg-surface-100 text-surface-700' };
});
</script>

<template>
    <span :class="['inline-flex items-center px-2 py-0.5 text-[11px] font-semibold border rounded-sm', config.class]">
        {{ config.label }}
    </span>
</template>
