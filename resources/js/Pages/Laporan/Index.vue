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
            <h1 class="text-lg font-semibold text-surface-900">Daftar Laporan</h1>
        </template>

        <!-- Filter Bar -->
        <div class="card p-4 mb-4 mx-4 sm:mx-0 mt-4 sm:mt-0 rounded-none sm:rounded-sm border-x-0 sm:border-x">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        class="input-field"
                        placeholder="Cari judul laporan..."
                    />
                </div>
                <select v-if="!isKepalaDusun" v-model="dusunId" class="input-field sm:w-40">
                    <option value="">Semua Dusun</option>
                    <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                </select>
                <select v-model="kategoriId" class="input-field sm:w-40">
                    <option value="">Semua Kategori</option>
                    <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                </select>
                <select v-model="status" class="input-field sm:w-36">
                    <option value="">Semua Status</option>
                    <option value="baru">Baru</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                    <option value="ditolak">Ditolak</option>
                </select>
                <button @click="resetFilters" class="btn-ghost text-xs whitespace-nowrap">
                    Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="card rounded-none sm:rounded-sm border-x-0 sm:border-x border-b-0 sm:border-b">
            <div class="px-5 py-4 border-b border-surface-200 flex items-center justify-between">
                <p class="text-sm text-surface-500">
                    {{ laporan.total }} laporan ditemukan
                </p>
                <Link :href="route('laporan.create')" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Buat Laporan
                </Link>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
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
                            <td colspan="7" class="text-center text-surface-400 py-8">Tidak ada laporan ditemukan</td>
                        </tr>
                    </tbody>
                </table>
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
