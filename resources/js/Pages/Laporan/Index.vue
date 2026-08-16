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
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isKepalaDusun = computed(() => user.value?.role === 'kepala_dusun');

// Filter state
const search = ref(props.filters?.search || '');
const dusunId = ref(props.filters?.dusun_id || '');
const kategoriId = ref(props.filters?.kategori_id || '');
const status = ref(props.filters?.status || '');

let searchTimeout = null;

const applyFilters = () => {
    router.get(route('laporan.index'), {
        search: search.value || undefined,
        dusun_id: dusunId.value || undefined,
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
    kategoriId.value = '';
    status.value = '';
    applyFilters();
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([dusunId, kategoriId, status], applyFilters);

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
    if (isKepalaDusun.value && item.status === 'baru' && item.dusun_id === user.value?.dusun_id) return true;
    return false;
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Daftar Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold tracking-tight text-surface-900 dark:text-surface-50">Daftar Laporan Aspirasi</h1>
        </template>

        <!-- Filter Bar -->
        <div class="card p-4 sm:p-5 mb-5 mx-4 sm:mx-0 mt-3 sm:mt-0 rounded-2xl">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        class="input-field"
                        placeholder="Cari judul atau isi laporan..."
                    />
                </div>
                <select v-if="!isKepalaDusun" v-model="dusunId" class="input-field sm:w-44">
                    <option value="">Semua Dusun</option>
                    <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                </select>
                <select v-model="kategoriId" class="input-field sm:w-44">
                    <option value="">Semua Kategori</option>
                    <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                </select>
                <select v-model="status" class="input-field sm:w-40">
                    <option value="">Semua Status</option>
                    <option value="baru">Baru</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                    <option value="ditolak">Ditolak</option>
                </select>
                <button @click="resetFilters" class="btn-ghost text-xs font-semibold whitespace-nowrap px-4">
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- Table Card Container -->
        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                <p class="text-sm font-semibold text-surface-600 dark:text-surface-400">
                    Total <span class="font-bold text-surface-900 dark:text-surface-100">{{ laporan.total }}</span> laporan ditemukan
                </p>
                <Link :href="route('laporan.create')" class="btn-primary text-xs sm:text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Buat Laporan Baru</span>
                </Link>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul Laporan</th>
                            <th>Dusun</th>
                            <th>Kategori</th>
                            <th>Pelapor</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in laporan.data" :key="item.id">
                            <td>
                                <Link :href="route('laporan.show', item.id)" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ item.judul }}
                                </Link>
                            </td>
                            <td>{{ item.dusun?.nama || '-' }}</td>
                            <td>
                                {{ item.kategori?.nama || '-' }}
                                <span v-if="item.subkategori" class="text-xs text-surface-400 block">{{ item.subkategori.nama }}</span>
                            </td>
                            <td class="text-surface-500">{{ item.user?.name || '-' }}</td>
                            <td class="text-surface-500 whitespace-nowrap">{{ formatDate(item.tanggal) }}</td>
                            <td><StatusBadge :status="item.status" /></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('laporan.show', item.id)" class="btn-ghost text-xs py-1.5 px-2">
                                        Detail
                                    </Link>
                                    <Link
                                        v-if="item.status === 'baru' && item.user_id === user.id"
                                        :href="route('laporan.edit', item.id)"
                                        class="btn-ghost text-xs py-1.5 px-2"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="canDelete(item)"
                                        @click="confirmDelete(item)"
                                        class="btn-ghost text-xs py-1.5 px-2 text-red-600 hover:bg-red-50"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!laporan.data?.length">
                            <td colspan="7" class="text-center text-surface-400 dark:text-surface-500 py-8">Tidak ada laporan ditemukan</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700">
                <div v-if="!laporan.data?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm">
                    Tidak ada laporan ditemukan
                </div>
                <div
                    v-for="item in laporan.data"
                    :key="item.id"
                    class="px-4 py-3.5"
                >
                    <!-- Judul + Status -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <Link :href="route('laporan.show', item.id)" class="font-semibold text-sm text-primary-700 dark:text-primary-400 leading-snug flex-1">
                            {{ item.judul }}
                        </Link>
                        <StatusBadge :status="item.status" />
                    </div>
                    <!-- Meta info -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-surface-500 dark:text-surface-400 mb-3">
                        <div v-if="item.dusun" class="flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <span>{{ item.dusun.nama }}</span>
                        </div>
                        <div v-if="item.kategori" class="flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                            <span>{{ item.kategori.nama }}<template v-if="item.subkategori"> / {{ item.subkategori.nama }}</template></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            <span>{{ item.user?.name || '-' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" /></svg>
                            <span>{{ formatDate(item.tanggal) }}</span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <Link :href="route('laporan.show', item.id)" class="btn-ghost text-xs py-1.5 px-3">
                            Detail
                        </Link>
                        <Link
                            v-if="item.status === 'baru' && item.user_id === user.id"
                            :href="route('laporan.edit', item.id)"
                            class="btn-ghost text-xs py-1.5 px-3"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete(item)"
                            @click="confirmDelete(item)"
                            class="btn-ghost text-xs py-1.5 px-3 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 ml-auto"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3">
                <Pagination :links="laporan" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Laporan"
            :message="`Yakin ingin menghapus laporan '${deleteTarget?.judul}'?`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
