<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    laporan: Object,
    kategoriList: Array,
    dusunList: Array,
    rwList: Array,
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isKetuaRw = computed(() => user.value?.role === 'ketua_rw');
const isKepalaDusun = computed(() => user.value?.role === 'kepala_dusun');

// Filter state
const search = ref(props.filters?.search || '');
const dusunId = ref(props.filters?.dusun_id || '');
const rwId = ref(props.filters?.rw_id || '');
const kategoriId = ref(props.filters?.kategori_id || '');
const status = ref(props.filters?.status || '');

let searchTimeout = null;

const applyFilters = () => {
    router.get(route('laporan.index'), {
        search: search.value || undefined,
        dusun_id: dusunId.value || undefined,
        rw_id: rwId.value || undefined,
        kategori_id: kategoriId.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    dusunId.value = '';
    rwId.value = '';
    kategoriId.value = '';
    status.value = '';
    applyFilters();
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([dusunId, rwId, kategoriId, status], applyFilters);

const deleteModal = ref(null);
const deleteTarget = ref(null);

const confirmDelete = (item) => {
    deleteTarget.value = item;
    deleteModal.value.show();
};

const handleDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('laporan.destroy', deleteTarget.value.id));
    }
};

const canDelete = (item) => {
    if (user.value?.role === 'superadmin' || user.value?.role === 'admin') return true;
    if (isKetuaRw.value && item.status === 'baru' && item.rw_id === user.value?.rw_id) return true;
    if (isKepalaDusun.value && item.status === 'baru' && item.rw?.dusun_id === user.value?.dusun_id) return true;
    return false;
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatRwNumber = (num) => {
    return 'RW ' + String(num).padStart(2, '0');
};
</script>

<template>
    <Head title="Daftar Laporan Aspirasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Daftar Laporan Aspirasi
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Kelola dan pantau seluruh aduan serta aspirasi warga Desa Sirnaputra
                    </p>
                </div>
                <Link :href="route('laporan.create')" class="btn-primary text-xs sm:text-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span class="hidden sm:inline">Buat Laporan Baru</span>
                    <span class="sm:hidden">Buat</span>
                </Link>
            </div>
        </template>

        <!-- Filter Bar Card -->
        <div class="card p-4 sm:p-5 mb-5 mx-4 sm:mx-0 rounded-2xl">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                <!-- Search Input with Icon -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-surface-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        class="input-field pl-10"
                        placeholder="Cari kata kunci judul atau isi laporan..."
                    />
                </div>

                <!-- Filters dropdown grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 lg:flex lg:items-center">
                    <select v-if="!isKetuaRw && !isKepalaDusun" v-model="dusunId" class="input-field text-xs sm:text-sm lg:w-40">
                        <option value="">Semua Dusun</option>
                        <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                    </select>

                    <select v-if="!isKetuaRw" v-model="rwId" class="input-field text-xs sm:text-sm lg:w-44">
                        <option value="">Semua RW</option>
                        <option v-for="rw in rwList" :key="rw.id" :value="rw.id">
                            {{ formatRwNumber(rw.nomor) }} - {{ rw.dusun?.nama }}
                        </option>
                    </select>

                    <select v-model="kategoriId" class="input-field text-xs sm:text-sm lg:w-40">
                        <option value="">Semua Kategori</option>
                        <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                    </select>

                    <select v-model="status" class="input-field text-xs sm:text-sm col-span-2 sm:col-span-1 lg:w-36">
                        <option value="">Semua Status</option>
                        <option value="baru">Baru</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>

                    <button @click="resetFilters" class="btn-ghost text-xs font-semibold whitespace-nowrap col-span-2 sm:col-span-4 lg:col-span-1">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card Container -->
        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                <p class="text-xs sm:text-sm font-medium text-surface-500 dark:text-surface-400">
                    Menampilkan total <span class="font-bold text-surface-900 dark:text-surface-100">{{ laporan.total }}</span> laporan
                </p>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul Laporan</th>
                            <th>Wilayah (RW / Dusun)</th>
                            <th>Kategori</th>
                            <th>Pelapor</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in laporan.data" :key="item.id">
                            <td class="max-w-xs font-semibold">
                                <Link
                                    :href="route('laporan.show', item.id)"
                                    class="text-surface-900 hover:text-primary-700 dark:text-surface-100 dark:hover:text-primary-400 transition-colors line-clamp-2"
                                >
                                    {{ item.judul }}
                                </Link>
                            </td>
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-surface-800 dark:text-surface-200">
                                        🏘️ {{ item.rw ? formatRwNumber(item.rw.nomor) : '-' }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-surface-500 dark:text-surface-400">
                                        📍 Dusun {{ item.rw?.dusun?.nama || '-' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary-800 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/60 px-2.5 py-1 rounded-lg">
                                        🏷️ {{ item.kategori?.nama || '-' }}
                                    </span>
                                    <span v-if="item.subkategori" class="text-[11px] text-surface-500 dark:text-surface-400 block mt-0.5">
                                        {{ item.subkategori.nama }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-xs font-medium text-surface-600 dark:text-surface-400">
                                {{ item.user?.name || '-' }}
                            </td>
                            <td class="text-xs font-medium text-surface-500 whitespace-nowrap">
                                {{ formatDate(item.tanggal) }}
                            </td>
                            <td>
                                <StatusBadge :status="item.status" />
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        :href="route('laporan.show', item.id)"
                                        class="btn-ghost text-xs py-1.5 px-2.5 font-bold"
                                        title="Detail"
                                    >
                                        Detail
                                    </Link>
                                    <Link
                                        v-if="item.status === 'baru' && item.user_id === user.id"
                                        :href="route('laporan.edit', item.id)"
                                        class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40"
                                        title="Edit"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="canDelete(item)"
                                        @click="confirmDelete(item)"
                                        class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                        title="Hapus"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!laporan.data?.length">
                            <td colspan="7" class="text-center text-surface-400 dark:text-surface-500 py-12 font-medium">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-surface-300 dark:text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span>Tidak ada laporan aduan ditemukan</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700/60">
                <div v-if="!laporan.data?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm font-medium">
                    Tidak ada laporan aduan ditemukan
                </div>
                <div
                    v-for="item in laporan.data"
                    :key="item.id"
                    class="px-4 py-3.5 space-y-2"
                >
                    <!-- Judul + Status -->
                    <div class="flex items-start justify-between gap-2">
                        <Link :href="route('laporan.show', item.id)" class="font-bold text-sm text-surface-900 dark:text-surface-100 leading-snug flex-1">
                            {{ item.judul }}
                        </Link>
                        <StatusBadge :status="item.status" />
                    </div>

                    <!-- Meta info chips -->
                    <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-surface-500 dark:text-surface-400">
                        <span v-if="item.rw">🏘️ {{ formatRwNumber(item.rw.nomor) }} (Dusun {{ item.rw?.dusun?.nama }})</span>
                        <span v-if="item.kategori">🏷️ {{ item.kategori.nama }}<template v-if="item.subkategori"> / {{ item.subkategori.nama }}</template></span>
                        <span>👤 {{ item.user?.name || '-' }}</span>
                        <span class="ml-auto">📅 {{ formatDate(item.tanggal) }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-1 border-t border-surface-100 dark:border-surface-700/40">
                        <Link :href="route('laporan.show', item.id)" class="btn-ghost text-xs py-1.5 px-3 font-bold">
                            Detail
                        </Link>
                        <Link
                            v-if="item.status === 'baru' && item.user_id === user.id"
                            :href="route('laporan.edit', item.id)"
                            class="btn-ghost text-xs py-1.5 px-3 font-bold text-amber-600 dark:text-amber-400"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete(item)"
                            @click="confirmDelete(item)"
                            class="btn-ghost text-xs py-1.5 px-3 font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40 ml-auto"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="px-5 py-4 border-t border-surface-200/80 dark:border-surface-700/80">
                <Pagination :links="laporan" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Laporan"
            :message="`Apakah Anda yakin ingin menghapus laporan '${deleteTarget?.judul}'?`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
