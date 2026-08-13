<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: null },
    color: { type: String, default: 'primary' },
    subtitle: { type: String, default: null },
});

const colorMap = {
    primary: 'text-primary-600 bg-primary-50',
    amber: 'text-amber-600 bg-amber-50',
    blue: 'text-blue-600 bg-blue-50',
    green: 'text-green-600 bg-green-50',
    red: 'text-red-600 bg-red-50',
};
</script>

<template>
    <div class="card p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-surface-500 mb-1">{{ label }}</p>
                <p class="text-2xl font-bold text-surface-900">{{ value }}</p>
                <p v-if="subtitle" class="text-xs text-surface-400 mt-1">{{ subtitle }}</p>
            </div>
            <div v-if="icon" :class="['w-10 h-10 rounded-sm flex items-center justify-center shrink-0', colorMap[color]]">
                <!-- Total -->
                <svg v-if="icon === 'total'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <!-- Baru -->
                <svg v-if="icon === 'baru'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <!-- Diproses -->
                <svg v-if="icon === 'diproses'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M21.015 4.356v4.992" />
                </svg>
                <!-- Selesai -->
                <svg v-if="icon === 'selesai'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <!-- Ditolak -->
                <svg v-if="icon === 'ditolak'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</template>
