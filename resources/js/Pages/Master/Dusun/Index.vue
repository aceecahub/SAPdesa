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
            <h1 class="text-lg font-semibold text-surface-900">Data Dusun</h1>
        </template>

        <div class="card">
            <div class="px-5 py-4 border-b border-surface-200 flex items-center justify-between">
                <p class="text-sm text-surface-500">Kelola data dusun Desa Sirnaputra</p>
                <Link :href="route('master.dusun.create')" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah
                </Link>
            </div>

            <div class="table-container">
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
