<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileUpload from '@/Components/FileUpload.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    kategoriList: Array,
    subkategoriList: Array,
    rwList: Array,
    dusunList: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isKetuaRw = computed(() => user.value?.role === 'ketua_rw');
const isKepalaDusun = computed(() => user.value?.role === 'kepala_dusun');

const form = useForm({
    judul: '',
    isi: '',
    kategori_id: '',
    subkategori_id: '',
    rw_id: isKetuaRw.value ? user.value?.rw_id : '',
    lampiran: [],
});

const formatRwLabel = (rw) => {
    if (!rw) return '';
    const rwNum = 'RW ' + String(rw.nomor).padStart(2, '0');
    const dusunNama = rw.dusun?.nama ? ` – Dusun ${rw.dusun.nama}` : '';
    return `${rwNum}${dusunNama}`;
};

const submit = () => {
    form.post(route('laporan.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Buat Laporan Baru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('laporan.index')"
                    class="p-2 text-surface-500 hover:text-surface-900 dark:text-surface-400 dark:hover:text-surface-100 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl transition-all shadow-xs"
                    title="Kembali"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Buat Laporan Aspirasi Baru
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Isi formulir di bawah ini dengan informasi yang jelas dan akurat
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-4 sm:mx-0">
            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Judul Laporan -->
                    <div>
                        <label class="label-text">
                            Judul Laporan / Aspirasi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.judul"
                            type="text"
                            class="input-field"
                            placeholder="Contoh: Perbaikan Lampu Jalan Rusak di RW 02"
                        />
                        <p v-if="form.errors.judul" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.judul }}
                        </p>
                    </div>

                    <!-- Kategori & Subkategori -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="label-text">
                                Pilih Kategori <span class="text-rose-500">*</span>
                            </label>
                            <select v-model="form.kategori_id" class="input-field">
                                <option value="">-- Pilih Kategori --</option>
                                <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                            </select>
                            <p v-if="form.errors.kategori_id" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                                {{ form.errors.kategori_id }}
                            </p>
                        </div>

                        <div v-if="subkategoriList && subkategoriList.length > 0">
                            <label class="label-text">
                                Subkategori <span class="text-surface-400 font-normal">(Opsional)</span>
                            </label>
                            <select v-model="form.subkategori_id" class="input-field">
                                <option value="">-- Pilih Subkategori --</option>
                                <option v-for="sk in subkategoriList" :key="sk.id" :value="sk.id">{{ sk.nama }}</option>
                            </select>
                            <p v-if="form.errors.subkategori_id" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                                {{ form.errors.subkategori_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Selector Wilayah RW -->
                    <div v-if="!isKetuaRw">
                        <label class="label-text">
                            Wilayah RW <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="form.rw_id" class="input-field">
                            <option value="">-- Pilih Wilayah RW --</option>
                            <option v-for="rw in rwList" :key="rw.id" :value="rw.id">
                                {{ formatRwLabel(rw) }}
                            </option>
                        </select>
                        <p v-if="form.errors.rw_id" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.rw_id }}
                        </p>
                    </div>

                    <div v-if="isKetuaRw" class="bg-surface-50 dark:bg-surface-700/50 border border-surface-200 dark:border-surface-700 rounded-2xl p-4 flex items-center gap-3">
                        <span class="text-xl">📍</span>
                        <div class="text-xs">
                            <span class="text-surface-500 dark:text-surface-400 font-medium">Wilayah Laporan: </span>
                            <span class="font-bold text-surface-900 dark:text-surface-100">
                                {{ formatRwLabel(user?.rw) || 'RW Anda' }}
                            </span>
                            <p class="text-[11px] text-surface-400 dark:text-surface-500 mt-0.5">Otomatis disesuaikan dengan wilayah kewenangan RW Anda.</p>
                        </div>
                    </div>

                    <!-- Isi Laporan -->
                    <div>
                        <label class="label-text">
                            Isi Aduan / Aspirasi Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="form.isi"
                            class="input-field"
                            rows="6"
                            placeholder="Uraikan masalah, lokasi pasti, dan harapan tindak lanjut secara detail..."
                        ></textarea>
                        <p v-if="form.errors.isi" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.isi }}
                        </p>
                    </div>

                    <FileUpload
                        v-model="form.lampiran"
                        label="Lampiran Foto / Berkas Bukti Pendukung (Opsional, Maksimal 4 Berkas)"
                        :max-files="4"
                        :error="form.errors.lampiran"
                    />

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-100 dark:border-surface-700/60">
                        <Link :href="route('laporan.index')" class="btn-secondary text-xs font-bold">
                            Batal
                        </Link>
                        <button type="submit" class="btn-primary text-xs font-bold" :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Kirim Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
