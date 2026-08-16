<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    dusun: Object,
});

const deleteModal = ref(null);
const deleteTarget = ref(null);

const confirmDelete = (item) => {
    deleteTarget.value = item;
    deleteModal.value.show();
};

const handleDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('master.dusun.destroy', deleteTarget.value.id));
    }
};
</script>

<template>
    <Head title="Data Dusun" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold tracking-tight text-surface-900 dark:text-surface-50">Data Dusun Sirnaputra</h1>
        </template>

        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0 mt-3 sm:mt-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                <p class="text-sm font-semibold text-surface-600 dark:text-surface-400">Kelola data dusun & kepala dusun Desa Sirnaputra</p>
                <Link :href="route('master.dusun.create')" class="btn-primary text-xs sm:text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Tambah Dusun</span>
                </Link>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Dusun</th>
                            <th>Jumlah Penduduk</th>
                            <th>Nama Kepala</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in dusun.data" :key="item.id">
                            <td class="text-surface-400">{{ (dusun.current_page - 1) * dusun.per_page + i + 1 }}</td>
                            <td class="font-medium text-surface-900">{{ item.nama }}</td>
                            <td>{{ item.jumlah_penduduk ?? '-' }}</td>
                            <td>{{ item.nama_kepala || '-' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('master.dusun.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5">
                                        Edit
                                    </Link>
                                    <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1.5 px-2.5 text-red-600 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!dusun.data?.length">
                            <td colspan="5" class="text-center text-surface-400 py-8">Belum ada data dusun</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700">
                <div v-if="!dusun.data?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm">
                    Belum ada data dusun
                </div>
                <div
                    v-for="(item, i) in dusun.data"
                    :key="item.id"
                    class="px-4 py-3.5"
                >
                    <!-- Nama + Actions -->
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-surface-400 dark:text-surface-500 w-5 text-right flex-shrink-0">{{ (dusun.current_page - 1) * dusun.per_page + i + 1 }}.</span>
                            <p class="font-semibold text-sm text-surface-900 dark:text-surface-100">{{ item.nama }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('master.dusun.edit', item.id)" class="btn-ghost text-xs py-1 px-2.5">
                                Edit
                            </Link>
                            <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1 px-2.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                Hapus
                            </button>
                        </div>
                    </div>
                    <!-- Info -->
                    <div class="pl-7 grid grid-cols-2 gap-1 text-xs text-surface-500 dark:text-surface-400">
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            <span>{{ item.nama_kepala || '-' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                            <span>{{ item.jumlah_penduduk ?? '-' }} penduduk</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3">
                <Pagination :links="dusun" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Dusun"
            :message="`Yakin ingin menghapus ${deleteTarget?.nama}? Data yang terkait juga akan terhapus.`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
