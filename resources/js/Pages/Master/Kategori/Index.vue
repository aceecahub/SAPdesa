<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kategori: Object,
});

const deleteModal = ref(null);
const deleteTarget = ref(null);

const confirmDelete = (item) => {
    deleteTarget.value = item;
    deleteModal.value.show();
};

const handleDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('master.kategori.destroy', deleteTarget.value.id));
    }
};
</script>

<template>
    <Head title="Data Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold tracking-tight text-surface-900 dark:text-surface-50">Data Kategori Laporan</h1>
        </template>

        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0 mt-3 sm:mt-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                <p class="text-sm font-semibold text-surface-600 dark:text-surface-400">Kelola daftar kategori laporan</p>
                <div class="flex items-center gap-2">
                    <Link :href="route('master.subkategori.index')" class="btn-secondary text-xs sm:text-sm">
                        <span>Kelola Subkategori</span>
                    </Link>
                    <Link :href="route('master.kategori.create')" class="btn-primary text-xs sm:text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>Tambah Kategori</span>
                    </Link>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in kategori.data" :key="item.id">
                            <td class="text-surface-400">{{ (kategori.current_page - 1) * kategori.per_page + i + 1 }}</td>
                            <td class="font-medium text-surface-900">{{ item.nama }}</td>
                            <td class="text-surface-500"><code class="text-xs bg-surface-100 px-1.5 py-0.5 rounded">{{ item.slug }}</code></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('master.kategori.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5">
                                        Edit
                                    </Link>
                                    <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1.5 px-2.5 text-red-600 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!kategori.data?.length">
                            <td colspan="4" class="text-center text-surface-400 py-8">Belum ada data kategori</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700">
                <div v-if="!kategori.data?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm">
                    Belum ada data kategori
                </div>
                <div
                    v-for="(item, i) in kategori.data"
                    :key="item.id"
                    class="px-4 py-3.5"
                >
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-surface-400 dark:text-surface-500 w-5 text-right">{{ (kategori.current_page - 1) * kategori.per_page + i + 1 }}.</span>
                            <p class="font-semibold text-sm text-surface-900 dark:text-surface-100">{{ item.nama }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('master.kategori.edit', item.id)" class="btn-ghost text-xs py-1 px-2.5">
                                Edit
                            </Link>
                            <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1 px-2.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                Hapus
                            </button>
                        </div>
                    </div>
                    <div class="pl-7">
                        <code class="text-xs bg-surface-100 dark:bg-surface-700 text-surface-500 dark:text-surface-400 px-1.5 py-0.5 rounded">{{ item.slug }}</code>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3">
                <Pagination :links="kategori" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Kategori"
            :message="`Yakin ingin menghapus kategori '${deleteTarget?.nama}'?`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
