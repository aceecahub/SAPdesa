<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    dusunList: Array,
    rwList: Array,
    kategoriList: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isKepalaDusun = computed(() => user.value?.role === 'kepala_dusun');

const dusunId = ref(isKepalaDusun.value ? user.value?.dusun_id : '');
const rwId = ref('');
const kategoriId = ref('');
const status = ref('');
const dariTanggal = ref('');
const sampaiTanggal = ref('');

const buildParams = () => {
    const params = new URLSearchParams();
    if (dusunId.value) params.set('dusun_id', dusunId.value);
    if (rwId.value) params.set('rw_id', rwId.value);
    if (kategoriId.value) params.set('kategori_id', kategoriId.value);
    if (status.value) params.set('status', status.value);
    if (dariTanggal.value) params.set('dari_tanggal', dariTanggal.value);
    if (sampaiTanggal.value) params.set('sampai_tanggal', sampaiTanggal.value);
    return params;
};

const exportExcel = () => {
    const url = route('export.excel') + '?' + buildParams().toString();
    window.open(url, '_blank');
};

const exportCsv = () => {
    const url = route('export.csv') + '?' + buildParams().toString();
    window.open(url, '_blank');
};

const resetFilters = () => {
    dusunId.value = isKepalaDusun.value ? user.value?.dusun_id : '';
    rwId.value = '';
    kategoriId.value = '';
    status.value = '';
    dariTanggal.value = '';
    sampaiTanggal.value = '';
};

const formatRwNumber = (num) => {
    return 'RW ' + String(num).padStart(2, '0');
};
</script>

<template>
    <Head title="Export Data Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Export Data Laporan Aspirasi
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Unduh rekapitulasi data aduan warga dalam format Excel rapih & CSV
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-4 sm:mx-0">
            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm space-y-6">
                <!-- Info banner -->
                <div class="bg-primary-50 dark:bg-primary-950/40 border border-primary-100 dark:border-primary-900/60 rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-2xl shrink-0">📊</span>
                    <div class="text-xs text-primary-900 dark:text-primary-200">
                        <p class="font-bold mb-0.5 text-sm">Unduh Rekap Laporan Terstruktur</p>
                        <p v-if="isKepalaDusun" class="leading-relaxed text-amber-700 dark:text-amber-300 font-semibold">
                            📍 Data yang dapat Anda ekspor otomatis dibatasi khusus untuk laporan dari RW-RW di wilayah <strong>Dusun {{ user?.dusun?.nama || '' }}</strong>.
                        </p>
                        <p v-else class="leading-relaxed text-surface-600 dark:text-surface-400">
                            Pilih kriteria wilayah Dusun, RW, Kategori, atau rentang tanggal. Berkas Excel/CSV yang diunduh secara otomatis telah diformat lengkap dengan <strong>Judul Laporan, Filter Metadata, Border Sel, Header Warna, dan Indicator Badge Status Penanganan</strong>.
                        </p>
                    </div>
                </div>

                <!-- Filter Controls Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="label-text">Wilayah Dusun</label>
                        <select v-model="dusunId" class="input-field" :disabled="isKepalaDusun">
                            <option value="">Semua Dusun</option>
                            <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                        </select>
                        <p v-if="isKepalaDusun" class="text-[11px] text-surface-400 mt-1">Dikunci pada wilayah dusun Anda.</p>
                    </div>

                    <div>
                        <label class="label-text">Wilayah RW</label>
                        <select v-model="rwId" class="input-field">
                            <option value="">Semua RW</option>
                            <option v-for="rw in rwList" :key="rw.id" :value="rw.id">
                                {{ formatRwNumber(rw.nomor) }} – Dusun {{ rw.dusun?.nama }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="label-text">Kategori Laporan</label>
                        <select v-model="kategoriId" class="input-field">
                            <option value="">Semua Kategori</option>
                            <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="label-text">Status Penanganan</label>
                        <select v-model="status" class="input-field">
                            <option value="">Semua Status</option>
                            <option value="baru">Baru</option>
                            <option value="diproses">Diproses</option>
                            <option value="selesai">Selesai</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-text">Rentang Tanggal Masuk Laporan</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <span class="text-[11px] text-surface-400 block mb-1">Dari Tanggal:</span>
                                <input v-model="dariTanggal" type="date" class="input-field text-xs" />
                            </div>
                            <div>
                                <span class="text-[11px] text-surface-400 block mb-1">Sampai Tanggal:</span>
                                <input v-model="sampaiTanggal" type="date" class="input-field text-xs" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Action Buttons -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-5 border-t border-surface-100 dark:border-surface-700/60">
                    <button @click="resetFilters" class="btn-ghost text-xs font-bold text-surface-500">
                        Reset Filter
                    </button>

                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="exportCsv" class="btn-secondary text-xs font-bold flex items-center gap-2">
                            <svg class="w-4 h-4 text-surface-600 dark:text-surface-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Unduh CSV Berwarna (.csv)</span>
                        </button>

                        <button @click="exportExcel" class="btn-primary text-xs font-bold flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25-6h17.25m-17.25-6h17.25M3.375 4.5h17.25" />
                            </svg>
                            <span>Unduh Excel Rapih (.xlsx)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
