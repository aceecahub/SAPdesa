<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    dusunList: Array,
    kategoriList: Array,
});

const dusunId = ref('');
const kategoriId = ref('');
const status = ref('');
const dariTanggal = ref('');
const sampaiTanggal = ref('');

const exportCsv = () => {
    const params = new URLSearchParams();
    if (dusunId.value) params.set('dusun_id', dusunId.value);
    if (kategoriId.value) params.set('kategori_id', kategoriId.value);
    if (status.value) params.set('status', status.value);
    if (dariTanggal.value) params.set('dari_tanggal', dariTanggal.value);
    if (sampaiTanggal.value) params.set('sampai_tanggal', sampaiTanggal.value);

    const url = route('export.csv') + '?' + params.toString();
    window.open(url, '_blank');
};

const resetFilters = () => {
    dusunId.value = '';
    kategoriId.value = '';
    status.value = '';
    dariTanggal.value = '';
    sampaiTanggal.value = '';
};
</script>

<template>
    <Head title="Export Data Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Export Data Laporan
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Unduh data pengaduan dan aspirasi warga ke format CSV / Excel
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-4 sm:mx-0">
            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm space-y-6">
                <!-- Info banner -->
                <div class="bg-primary-50 dark:bg-primary-950/40 border border-primary-100 dark:border-primary-900/60 rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl shrink-0">📊</span>
                    <div class="text-xs text-primary-900 dark:text-primary-200">
                        <p class="font-bold mb-0.5">Filter Data Sebelum Mengunduh</p>
                        <p class="leading-relaxed text-surface-600 dark:text-surface-400">
                            Pilih kriteria wilayah dusun, kategori, status aduan, atau rentang tanggal tertentu untuk menyaring data laporan yang akan diexport.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="label-text">Wilayah Dusun</label>
                        <select v-model="dusunId" class="input-field">
                            <option value="">Semua Dusun</option>
                            <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
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

                    <div>
                        <label class="label-text">Rentang Tanggal Masuk</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="dariTanggal" type="date" class="input-field text-xs" title="Dari Tanggal" />
                            <input v-model="sampaiTanggal" type="date" class="input-field text-xs" title="Sampai Tanggal" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-100 dark:border-surface-700/60">
                    <button @click="resetFilters" class="btn-secondary text-xs font-bold">
                        Reset Filter
                    </button>
                    <button @click="exportCsv" class="btn-primary text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Unduh Berkas CSV</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

