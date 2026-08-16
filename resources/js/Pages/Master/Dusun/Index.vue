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
            <h1 class="text-base sm:text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50 truncate">
                Data Dusun
            </h1>
        </template>

        <div class="space-y-4">
            <!-- Page Header Container inside Body for 100% Mobile Cleanliness -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mx-4 sm:mx-0 mt-2 sm:mt-0">
                <div>
                    <h2 class="text-lg sm:text-2xl font-black text-surface-900 dark:text-surface-50">
                        Data Dusun Desa Sirnaputra
                    </h2>
                    <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 font-medium">
                        Kelola data wilayah dusun, foto Kepala Dusun, dan statistik penduduk
                    </p>
                </div>
                <Link :href="route('master.dusun.create')" class="btn-primary text-xs sm:text-sm shrink-0 self-start sm:self-auto shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Tambah Dusun Baru</span>
                </Link>
            </div>

            <!-- Main Card Container -->
            <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0">
                <div class="px-5 py-3.5 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                    <p class="text-xs sm:text-sm font-medium text-surface-500 dark:text-surface-400">
                        Total <span class="font-bold text-surface-900 dark:text-surface-100">{{ dusun.total }}</span> wilayah dusun terdaftar
                    </p>
                </div>

                <!-- Desktop Table -->
                <div class="hidden sm:block table-container border-0 rounded-none">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th>Nama Dusun</th>
                                <th>Kepala Dusun</th>
                                <th>Jumlah Penduduk</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, i) in dusun.data" :key="item.id">
                                <td class="text-center font-bold text-xs text-surface-400">
                                    {{ (dusun.current_page - 1) * dusun.per_page + i + 1 }}
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 font-bold text-surface-900 dark:text-surface-100 text-sm">
                                        📍 Dusun {{ item.nama }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <!-- Avatar Foto Kepala Dusun -->
                                        <div class="w-9 h-9 rounded-full overflow-hidden bg-primary-100 dark:bg-primary-950 border border-primary-200 dark:border-primary-800 shrink-0 flex items-center justify-center font-bold text-xs text-primary-700 shadow-xs">
                                            <img
                                                v-if="item.foto_kepala"
                                                :src="'/storage/' + item.foto_kepala"
                                                :alt="item.nama_kepala"
                                                class="w-full h-full object-cover"
                                            />
                                            <span v-else>👤</span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-surface-900 dark:text-surface-100 text-xs block">
                                                {{ item.nama_kepala || 'Belum diatur' }}
                                            </span>
                                            <span class="text-[11px] text-surface-400 font-medium">Kepala Dusun</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-xs font-semibold text-surface-600 dark:text-surface-400">
                                    👥 {{ item.jumlah_penduduk ? item.jumlah_penduduk.toLocaleString('id-ID') + ' Jiwa' : '-' }}
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('master.dusun.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40">
                                            Edit
                                        </Link>
                                        <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!dusun.data?.length">
                                <td colspan="5" class="text-center text-surface-400 dark:text-surface-500 py-12 font-medium">
                                    Belum ada data wilayah dusun
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List (Optimized for Android / Mobile Devices) -->
                <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700/60">
                    <div v-if="!dusun.data?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm font-medium">
                        Belum ada data wilayah dusun
                    </div>
                    <div
                        v-for="(item, i) in dusun.data"
                        :key="item.id"
                        class="p-4 space-y-3"
                    >
                        <!-- Top Header Row: Name & Buttons -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-sm text-surface-900 dark:text-surface-100 flex items-center gap-1.5">
                                <span class="text-xs text-surface-400 font-semibold">{{ (dusun.current_page - 1) * dusun.per_page + i + 1 }}.</span>
                                📍 Dusun {{ item.nama }}
                            </span>
                            <div class="flex items-center gap-1">
                                <Link :href="route('master.dusun.edit', item.id)" class="btn-ghost text-xs py-1 px-2 font-bold text-amber-600 dark:text-amber-400">
                                    Edit
                                </Link>
                                <button @click="confirmDelete(item)" class="btn-ghost text-xs py-1 px-2 font-bold text-rose-600 dark:text-rose-400">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <!-- Detail Row: Foto Kepala Dusun + Info -->
                        <div class="flex items-center gap-3 bg-surface-50 dark:bg-surface-800/60 p-2.5 rounded-xl border border-surface-200/60 dark:border-surface-700/60">
                            <!-- Avatar Foto -->
                            <div class="w-11 h-11 rounded-full overflow-hidden bg-primary-100 dark:bg-primary-950 border border-primary-200 dark:border-primary-800 shrink-0 flex items-center justify-center font-bold text-sm text-primary-700 shadow-xs">
                                <img
                                    v-if="item.foto_kepala"
                                    :src="'/storage/' + item.foto_kepala"
                                    :alt="item.nama_kepala"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>👤</span>
                            </div>

                            <!-- Info Kepala Dusun & Penduduk -->
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-extrabold text-surface-900 dark:text-surface-100 truncate">
                                    {{ item.nama_kepala || 'Belum ada Kepala Dusun' }}
                                </p>
                                <p class="text-[11px] font-semibold text-surface-500 dark:text-surface-400 mt-0.5">
                                    👥 {{ item.jumlah_penduduk ? item.jumlah_penduduk.toLocaleString('id-ID') + ' Penduduk' : '0 Penduduk' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-surface-200/80 dark:border-surface-700/80">
                    <Pagination :links="dusun" />
                </div>
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Data Dusun"
            :message="`Apakah Anda yakin ingin menghapus '${deleteTarget?.nama}'? Data laporan terkait dusun ini juga dapat terpengaruh.`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>


