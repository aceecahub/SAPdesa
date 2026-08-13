<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subkategori: Object,
});

const deleteModal = ref(null);
const deleteTarget = ref(null);

const confirmDelete = (item) => {
    deleteTarget.value = item;
    deleteModal.value.show();
};

const handleDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('master.subkategori.destroy', deleteTarget.value.id));
    }
};
</script>

<template>
    <Head title="Data Subkategori" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('master.kategori.index')" class="text-surface-400 hover:text-surface-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-lg font-semibold text-surface-900">Data Subkategori</h1>
            </div>
        </template>

        <div class="card">
            <div class="px-5 py-4 border-b border-surface-200 flex items-center justify-between">
                <p class="text-sm text-surface-500">Kelola subkategori untuk setiap kategori laporan</p>
                <Link :href="route('master.subkategori.create')" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah
                </Link>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori Induk</th>
                            <th>Nama Subkategori</th>
                            <th>Slug</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in subkategori.data" :key="item.id">
                            <td class="text-surface-400">{{ (subkategori.current_page - 1) * subkategori.per_page + i + 1 }}</td>
                            <td class="font-medium text-surface-900">{{ item.kategori?.nama || '-' }}</td>
                            <td class="font-medium text-surface-900">{{ item.nama }}</td>
                            <td class="text-surface-500"><code class="text-xs bg-surface-100 px-1.5 py-0.5 rounded">{{ item.slug }}</code></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('master.subkategori.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5">
                                        Edit
                                    </Link>
                                    <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1.5 px-2.5 text-red-600 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!subkategori.data?.length">
                            <td colspan="5" class="text-center text-surface-400 py-8">Belum ada data subkategori</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3">
                <Pagination :links="subkategori" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Subkategori"
            :message="`Yakin ingin menghapus subkategori '${deleteTarget?.nama}'?`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
