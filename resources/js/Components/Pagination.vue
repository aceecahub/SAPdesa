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
    <div v-if="showPagination" class="flex items-center justify-between mt-4 text-sm">
        <div class="text-surface-500">
            Menampilkan {{ links.from }}–{{ links.to }} dari {{ links.total }} data
        </div>
        <div class="flex items-center gap-1">
            <Link
                v-if="links.prev_page_url"
                :href="links.prev_page_url"
                class="px-3 py-1.5 border border-surface-300 rounded-sm text-surface-600 hover:bg-surface-50 transition-colors"
                preserve-scroll
            >
                &laquo;
            </Link>
            <span v-else class="px-3 py-1.5 border border-surface-200 rounded-sm text-surface-300 cursor-not-allowed">&laquo;</span>

            <template v-for="page in pages" :key="page.label">
                <Link
                    v-if="page.url"
                    :href="page.url"
                    :class="[
                        'px-3 py-1.5 border rounded-sm transition-colors',
                        page.active
                            ? 'bg-primary-700 text-white border-primary-700'
                            : 'border-surface-300 text-surface-600 hover:bg-surface-50',
                    ]"
                    preserve-scroll
                    v-html="page.label"
                />
                <span v-else class="px-3 py-1.5 text-surface-400" v-html="page.label" />
            </template>

            <Link
                v-if="links.next_page_url"
                :href="links.next_page_url"
                class="px-3 py-1.5 border border-surface-300 rounded-sm text-surface-600 hover:bg-surface-50 transition-colors"
                preserve-scroll
            >
                &raquo;
            </Link>
            <span v-else class="px-3 py-1.5 border border-surface-200 rounded-sm text-surface-300 cursor-not-allowed">&raquo;</span>
        </div>
    </div>
</template>
