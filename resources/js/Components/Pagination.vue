<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: { type: Object, required: true },
});

const pages = computed(() => {
    if (!props.links?.links) return [];
    // Remove first (prev) and last (next)
    return props.links.links.slice(1, -1);
});

const showPagination = computed(() => {
    return props.links?.last_page > 1;
});
</script>

<template>
    <div v-if="showPagination" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-5 text-sm">
        <div class="text-xs font-medium text-surface-500 dark:text-surface-400">
            Menampilkan <span class="font-semibold text-surface-800 dark:text-surface-200">{{ links.from }}–{{ links.to }}</span> dari <span class="font-semibold text-surface-800 dark:text-surface-200">{{ links.total }}</span> data
        </div>
        <div class="flex items-center gap-1.5">
            <Link
                v-if="links.prev_page_url"
                :href="links.prev_page_url"
                class="px-3 py-1.5 border border-surface-200 dark:border-surface-700 rounded-xl text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700 transition-all font-semibold shadow-xs"
                preserve-scroll
            >
                &laquo;
            </Link>
            <span v-else class="px-3 py-1.5 border border-surface-200/50 dark:border-surface-800 rounded-xl text-surface-300 dark:text-surface-600 cursor-not-allowed text-xs">&laquo;</span>

            <template v-for="page in pages" :key="page.label">
                <Link
                    v-if="page.url"
                    :href="page.url"
                    :class="[
                        'px-3.5 py-1.5 text-xs font-semibold rounded-xl transition-all shadow-xs',
                        page.active
                            ? 'bg-primary-700 text-white border border-primary-700 dark:bg-primary-600 dark:border-primary-600 shadow-sm'
                            : 'border border-surface-200 dark:border-surface-700 text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-700',
                    ]"
                    preserve-scroll
                    v-html="page.label"
                />
                <span v-else class="px-3.5 py-1.5 text-xs text-surface-400 dark:text-surface-600" v-html="page.label" />
            </template>

            <Link
                v-if="links.next_page_url"
                :href="links.next_page_url"
                class="px-3 py-1.5 border border-surface-200 dark:border-surface-700 rounded-xl text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-700 transition-all font-semibold shadow-xs"
                preserve-scroll
            >
                &raquo;
            </Link>
            <span v-else class="px-3 py-1.5 border border-surface-200/50 dark:border-surface-800 rounded-xl text-surface-300 dark:text-surface-600 cursor-not-allowed text-xs">&raquo;</span>
        </div>
    </div>
</template>
