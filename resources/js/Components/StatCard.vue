<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: null },
    color: { type: String, default: 'primary' },
    subtitle: { type: String, default: null },
});

const colorMap = {
    primary: 'text-primary-700 bg-primary-50/80 border border-primary-100 dark:text-primary-300 dark:bg-primary-950/50 dark:border-primary-900/60',
    amber: 'text-amber-700 bg-amber-50/80 border border-amber-100 dark:text-amber-300 dark:bg-amber-950/50 dark:border-amber-900/60',
    blue: 'text-blue-700 bg-blue-50/80 border border-blue-100 dark:text-blue-300 dark:bg-blue-950/50 dark:border-blue-900/60',
    green: 'text-emerald-700 bg-emerald-50/80 border border-emerald-100 dark:text-emerald-300 dark:bg-emerald-950/50 dark:border-emerald-900/60',
    red: 'text-rose-700 bg-rose-50/80 border border-rose-100 dark:text-rose-300 dark:bg-rose-950/50 dark:border-rose-900/60',
};
</script>

<template>
    <div class="card p-5 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider mb-1">{{ label }}</p>
                <p class="text-3xl font-extrabold text-surface-900 dark:text-surface-50 tracking-tight">{{ value }}</p>
            </div>
            <div v-if="icon" :class="['w-11 h-11 rounded-xl flex items-center justify-center shrink-0 shadow-xs', colorMap[color]]">
                <!-- Total -->
                <svg v-if="icon === 'total'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <!-- Baru -->
                <svg v-if="icon === 'baru'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <!-- Diproses -->
                <svg v-if="icon === 'diproses'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M21.015 4.356v4.992" />
                </svg>
                <!-- Selesai -->
                <svg v-if="icon === 'selesai'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <!-- Ditolak -->
                <svg v-if="icon === 'ditolak'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p v-if="subtitle" class="text-xs font-medium text-surface-400 dark:text-surface-500 mt-2">{{ subtitle }}</p>
    </div>
</template>
