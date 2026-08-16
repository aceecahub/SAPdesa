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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Data Kategori Laporan
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Pengelompokan bidang pengaduan dan aspirasi masyarakat
                    </p>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <Link :href="route('master.subkategori.index')" class="btn-secondary text-xs font-bold">
                        📁 Kelola Subkategori
                    </Link>
                    <Link :href="route('master.kategori.create')" class="btn-primary text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>Tambah Kategori</span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0 mt-3 sm:mt-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                <p class="text-xs sm:text-sm font-medium text-surface-500 dark:text-surface-400">
                    Total <span class="font-bold text-surface-900 dark:text-surface-100">{{ kategori.total }}</span> kategori terdaftar
                </p>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">No</th>
                            <th>Nama Kategori</th>
                            <th>Kode Slug</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in kategori.data" :key="item.id">
                            <td class="text-center font-bold text-xs text-surface-400">
                                {{ (kategori.current_page - 1) * kategori.per_page + i + 1 }}
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 font-bold text-surface-900 dark:text-surface-100 text-sm">
                                    🏷️ {{ item.nama }}
                                </span>
                            </td>
                            <td>
                                <code class="text-xs bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300 font-mono px-2 py-0.5 rounded-md border border-surface-200/60 dark:border-surface-600/60">
                                    {{ item.slug }}
                                </code>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('master.kategori.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40">
                                        Edit
                                    </Link>
                                    <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!kategori.data?.length">
                            <td colspan="4" class="text-center text-surface-400 dark:text-surface-500 py-12 font-medium">
                                Belum ada data kategori laporan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700/60">
                <div v-if="!kategori.data?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm font-medium">
                    Belum ada data kategori laporan
                </div>
                <div
                    v-for="(item, i) in kategori.data"
                    :key="item.id"
                    class="px-4 py-3.5 space-y-1.5"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold text-sm text-surface-900 dark:text-surface-100">
                            🏷️ {{ item.nama }}
                        </span>
                        <div class="flex items-center gap-1">
                            <Link :href="route('master.kategori.edit', item.id)" class="btn-ghost text-xs py-1 px-2 font-bold text-amber-600">
                                Edit
                            </Link>
                            <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1 px-2 font-bold text-rose-600">
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div>
                        <code class="text-xs bg-surface-100 dark:bg-surface-700 text-surface-500 dark:text-surface-400 px-2 py-0.5 rounded font-mono">
                            {{ item.slug }}
                        </code>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-surface-200/80 dark:border-surface-700/80">
                <Pagination :links="kategori" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Kategori"
            :message="`Apakah Anda yakin ingin menghapus kategori '${deleteTarget?.nama}'? Data laporan terkait kategori ini dapat terpengaruh.`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>

