<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: null },
    color: { type: String, default: 'primary' },
    subtitle: { type: String, default: null },
    trend: { type: [String, Number], default: null },
    trendType: { type: String, default: 'up' }, // 'up', 'down', 'neutral'
    badge: { type: String, default: null },
    badgeColor: { type: String, default: 'amber' },
});

const colorMap = {
    primary: 'text-primary-700 bg-primary-500/10 border-primary-200/60 dark:text-primary-300 dark:bg-primary-500/15 dark:border-primary-800/60 shadow-primary-500/10',
    amber: 'text-amber-700 bg-amber-500/10 border-amber-200/60 dark:text-amber-300 dark:bg-amber-500/15 dark:border-amber-800/60 shadow-amber-500/10',
    blue: 'text-blue-700 bg-blue-500/10 border-blue-200/60 dark:text-blue-300 dark:bg-blue-500/15 dark:border-blue-800/60 shadow-blue-500/10',
    green: 'text-emerald-700 bg-emerald-500/10 border-emerald-200/60 dark:text-emerald-300 dark:bg-emerald-500/15 dark:border-emerald-800/60 shadow-emerald-500/10',
    red: 'text-rose-700 bg-rose-500/10 border-rose-200/60 dark:text-rose-300 dark:bg-rose-500/15 dark:border-rose-800/60 shadow-rose-500/10',
    purple: 'text-purple-700 bg-purple-500/10 border-purple-200/60 dark:text-purple-300 dark:bg-purple-500/15 dark:border-purple-800/60 shadow-purple-500/10',
};

const badgeColorMap = {
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/80',
    emerald: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/80',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/80',
    rose: 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/80',
};
</script>

<template>
    <div class="group relative bg-white dark:bg-surface-800 border border-surface-200/80 dark:border-surface-700/80 rounded-2xl p-5 shadow-xs hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between overflow-hidden">
        <!-- Subtle Top Glow Line -->
        <div :class="[
            'absolute top-0 left-0 right-0 h-1 bg-gradient-to-r transition-opacity duration-300 opacity-60 group-hover:opacity-100',
            color === 'primary' ? 'from-primary-600 to-primary-400' : '',
            color === 'amber' ? 'from-amber-500 to-yellow-400' : '',
            color === 'blue' ? 'from-blue-600 to-cyan-400' : '',
            color === 'green' ? 'from-emerald-600 to-teal-400' : '',
            color === 'red' ? 'from-rose-600 to-red-400' : '',
            color === 'purple' ? 'from-purple-600 to-indigo-400' : '',
        ]"></div>

        <div class="flex items-start justify-between gap-3 mb-2">
            <div class="min-w-0">
                <span class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider block truncate">
                    {{ label }}
                </span>
                <div class="flex items-baseline gap-2 mt-1.5">
                    <span class="text-3xl font-extrabold text-surface-900 dark:text-surface-50 tracking-tight leading-none">
                        {{ value }}
                    </span>
                    
                    <!-- Badge (optional) -->
                    <span v-if="badge" :class="['inline-flex items-center px-2 py-0.5 text-[10px] font-bold border rounded-full shrink-0', badgeColorMap[badgeColor] || badgeColorMap.amber]">
                        <span v-if="badgeColor === 'amber'" class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1 animate-pulse"></span>
                        {{ badge }}
                    </span>
                </div>
            </div>

            <!-- Icon box -->
            <div v-if="icon" :class="['w-11 h-11 rounded-2xl border flex items-center justify-center shrink-0 shadow-sm transition-transform duration-300 group-hover:scale-110', colorMap[color] || colorMap.primary]">
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
                <!-- Rate / Percentage -->
                <svg v-if="icon === 'rate'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
        </div>

        <!-- Footer / Trend / Subtitle -->
        <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-surface-100 dark:border-surface-700/50 text-xs">
            <span v-if="subtitle" class="text-surface-500 dark:text-surface-400 font-medium truncate">
                {{ subtitle }}
            </span>
            
            <div v-if="trend !== null && trend !== undefined" class="flex items-center gap-1 font-semibold shrink-0 ml-auto" :class="[
                trendType === 'up' ? 'text-emerald-600 dark:text-emerald-400' : '',
                trendType === 'down' ? 'text-rose-600 dark:text-rose-400' : '',
                trendType === 'neutral' ? 'text-surface-500 dark:text-surface-400' : '',
            ]">
                <svg v-if="trendType === 'up'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 005.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.94" />
                </svg>
                <svg v-if="trendType === 'down'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.306-4.307a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.94" />
                </svg>
                <span>{{ trend }}</span>
            </div>
        </div>
    </div>
</template>

