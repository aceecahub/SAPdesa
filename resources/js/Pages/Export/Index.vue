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
    <Head title="Export Data" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Export Data Laporan</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <p class="text-sm text-surface-500 mb-6">
                    Export data laporan ke format CSV. Anda bisa memfilter data sebelum mengexport.
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="label-text">Dusun</label>
                        <select v-model="dusunId" class="input-field">
                            <option value="">Semua Dusun</option>
                            <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="label-text">Kategori</label>
                        <select v-model="kategoriId" class="input-field">
                            <option value="">Semua Kategori</option>
                            <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="label-text">Status</label>
                        <select v-model="status" class="input-field">
                            <option value="">Semua Status</option>
                            <option value="baru">Baru</option>
                            <option value="diproses">Diproses</option>
                            <option value="selesai">Selesai</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Dari Tanggal</label>
                            <input v-model="dariTanggal" type="date" class="input-field" />
                        </div>
                        <div>
                            <label class="label-text">Sampai Tanggal</label>
                            <input v-model="sampaiTanggal" type="date" class="input-field" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-6 pt-4 border-t border-surface-100">
                    <button @click="exportCsv" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download CSV
                    </button>
                    <button @click="resetFilters" class="btn-secondary">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
