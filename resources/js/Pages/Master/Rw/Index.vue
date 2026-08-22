<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    rwList: Object,
    dusunList: Array,
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isKepalaDusun = computed(() => user.value?.role === 'kepala_dusun');

const selectedDusun = ref(props.filters?.dusun_id || '');

const filterByDusun = () => {
    router.get(route('master.rw.index'), {
        dusun_id: selectedDusun.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch(selectedDusun, filterByDusun);

const deleteModal = ref(null);
const deleteTarget = ref(null);

const confirmDelete = (item) => {
    deleteTarget.value = item;
    deleteModal.value.show();
};

const handleDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('master.rw.destroy', deleteTarget.value.id));
    }
};

const formatRwNumber = (num) => {
    return 'RW ' + String(num).padStart(2, '0');
};
</script>

<template>
    <Head title="Data RW" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-base sm:text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50 truncate">
                Data Rukun Warga (RW)
            </h1>
        </template>

        <div class="space-y-4">
            <!-- Page Header Container -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mx-4 sm:mx-0 mt-2 sm:mt-0">
                <div>
                    <h2 class="text-lg sm:text-2xl font-black text-surface-900 dark:text-surface-50">
                        Data RW Desa Sirnaputra
                    </h2>
                    <p class="text-xs sm:text-sm text-surface-500 dark:text-surface-400 font-medium">
                        Kelola data wilayah Rukun Warga (RW), foto Ketua RW, dan dusun induknya
                    </p>
                </div>
                <Link
                    v-if="!isKepalaDusun"
                    :href="route('master.rw.create')"
                    class="btn-primary text-xs sm:text-sm shrink-0 self-start sm:self-auto shadow-xs"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Tambah RW Baru</span>
                </Link>
            </div>

            <!-- Filter Bar Card -->
            <div class="card p-4 mx-4 sm:mx-0 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                <div v-if="!isKepalaDusun" class="flex items-center gap-3 w-full sm:w-auto">
                    <label class="text-xs font-bold text-surface-600 dark:text-surface-300 whitespace-nowrap">Filter Dusun:</label>
                    <select v-model="selectedDusun" class="input-field text-xs sm:text-sm max-w-xs">
                        <option value="">Semua Dusun</option>
                        <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                    </select>
                </div>
                <div v-else class="text-xs font-semibold text-surface-700 dark:text-surface-300">
                    📍 Wilayah: <span class="font-bold">Dusun {{ user?.dusun?.nama }}</span>
                </div>
                <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                    Total <span class="font-bold text-surface-900 dark:text-surface-100">{{ rwList.total }}</span> RW terdaftar
                </p>
            </div>

            <!-- Main Card Container -->
            <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0">
                <!-- Desktop Table -->
                <div class="hidden sm:block table-container border-0 rounded-none">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th>Wilayah RW</th>
                                <th>Induk Dusun</th>
                                <th>Ketua RW</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, i) in rwList.data" :key="item.id">
                                <td class="text-center font-bold text-xs text-surface-400">
                                    {{ (rwList.current_page - 1) * rwList.per_page + i + 1 }}
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 font-extrabold text-surface-900 dark:text-surface-100 text-sm">
                                        🏘️ {{ formatRwNumber(item.nomor) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-surface-700 dark:text-surface-300 bg-surface-100 dark:bg-surface-700/60 px-2.5 py-1 rounded-lg">
                                        📍 Dusun {{ item.dusun?.nama || '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <!-- Avatar Foto Ketua RW -->
                                        <div class="w-9 h-9 rounded-full overflow-hidden bg-primary-100 dark:bg-primary-950 border border-primary-200 dark:border-primary-800 shrink-0 flex items-center justify-center font-bold text-xs text-primary-700 shadow-xs">
                                            <img
                                                v-if="item.foto_ketua"
                                                :src="'/storage/' + item.foto_ketua"
                                                :alt="item.nama_ketua"
                                                class="w-full h-full object-cover"
                                            />
                                            <span v-else>👤</span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-surface-900 dark:text-surface-100 text-xs block">
                                                {{ item.nama_ketua || 'Belum diatur' }}
                                            </span>
                                            <span class="text-[11px] text-surface-400 font-medium">Ketua RW</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link :href="route('master.rw.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40">
                                            Edit
                                        </Link>
                                        <button
                                            v-if="!isKepalaDusun"
                                            @click="confirmDelete(item)"
                                            class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!rwList.data?.length">
                                <td colspan="5" class="text-center text-surface-400 dark:text-surface-500 py-12 font-medium">
                                    Belum ada data Rukun Warga (RW)
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List -->
                <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700/60">
                    <div v-if="!rwList.data?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm font-medium">
                        Belum ada data Rukun Warga (RW)
                    </div>
                    <div
                        v-for="(item, i) in rwList.data"
                        :key="item.id"
                        class="p-4 space-y-3"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-sm text-surface-900 dark:text-surface-100 flex items-center gap-1.5">
                                <span class="text-xs text-surface-400 font-semibold">{{ (rwList.current_page - 1) * rwList.per_page + i + 1 }}.</span>
                                🏘️ {{ formatRwNumber(item.nomor) }}
                            </span>
                            <div class="flex items-center gap-1">
                                <Link :href="route('master.rw.edit', item.id)" class="btn-ghost text-xs py-1 px-2 font-bold text-amber-600 dark:text-amber-400">
                                    Edit
                                </Link>
                                <button
                                    v-if="!isKepalaDusun"
                                    @click="confirmDelete(item)"
                                    class="btn-ghost text-xs py-1 px-2 font-bold text-rose-600 dark:text-rose-400"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 bg-surface-50 dark:bg-surface-800/60 p-2.5 rounded-xl border border-surface-200/60 dark:border-surface-700/60">
                            <!-- Avatar Foto -->
                            <div class="w-11 h-11 rounded-full overflow-hidden bg-primary-100 dark:bg-primary-950 border border-primary-200 dark:border-primary-800 shrink-0 flex items-center justify-center font-bold text-sm text-primary-700 shadow-xs">
                                <img
                                    v-if="item.foto_ketua"
                                    :src="'/storage/' + item.foto_ketua"
                                    :alt="item.nama_ketua"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>👤</span>
                            </div>

                            <div class="flex-1 min-w-0 text-xs">
                                <p class="font-extrabold text-surface-900 dark:text-surface-100 truncate">
                                    {{ item.nama_ketua || 'Belum ada Ketua RW' }}
                                </p>
                                <p class="text-[11px] font-semibold text-surface-500 dark:text-surface-400 mt-0.5">
                                    📍 Dusun {{ item.dusun?.nama }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-surface-200/80 dark:border-surface-700/80">
                    <Pagination :links="rwList" />
                </div>
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Data RW"
            :message="`Apakah Anda yakin ingin menghapus '${formatRwNumber(deleteTarget?.nomor)}'? Data laporan terkait RW ini juga dapat terpengaruh.`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
