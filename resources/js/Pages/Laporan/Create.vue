<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    kategoriList: Array,
    dusunList: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isKepalaDusun = computed(() => user.value?.role === 'kepala_dusun');

const form = useForm({
    judul: '',
    isi: '',
    kategori_id: '',
    subkategori_id: '',
    dusun_id: isKepalaDusun.value ? user.value?.dusun_id : '',
    lampiran: null,
});

const subkategoriList = computed(() => {
    if (!form.kategori_id) return [];
    const kat = props.kategoriList.find(k => k.id === form.kategori_id);
    return kat ? (kat.subkategori || []) : [];
});

watch(() => form.kategori_id, () => {
    form.subkategori_id = '';
});

const submit = () => {
    form.post(route('laporan.store'), {
        forceFormData: true,
    });
};

const handleFile = (e) => {
    form.lampiran = e.target.files[0];
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

                        <div v-if="subkategoriList.length > 0">
                            <label class="label-text">
                                Subkategori <span class="text-rose-500">*</span>
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

                    <!-- Wilayah Dusun -->
                    <div v-if="!isKepalaDusun">
                        <label class="label-text">
                            Wilayah Dusun <span class="text-rose-500">*</span>
                        </label>
                        <select v-model="form.dusun_id" class="input-field">
                            <option value="">-- Pilih Wilayah Dusun --</option>
                            <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                        </select>
                        <p v-if="form.errors.dusun_id" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.dusun_id }}
                        </p>
                    </div>

                    <div v-if="isKepalaDusun" class="bg-surface-50 dark:bg-surface-700/50 border border-surface-200 dark:border-surface-700 rounded-2xl p-4 flex items-center gap-3">
                        <span class="text-xl">📍</span>
                        <div class="text-xs">
                            <span class="text-surface-500 dark:text-surface-400 font-medium">Wilayah Laporan: </span>
                            <span class="font-bold text-surface-900 dark:text-surface-100">Dusun {{ user?.dusun?.nama || dusunList[0]?.nama }}</span>
                            <p class="text-[11px] text-surface-400 dark:text-surface-500 mt-0.5">Otomatis disesuaikan dengan wilayah kewenangan Anda.</p>
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

                    <!-- Lampiran File -->
                    <div>
                        <label class="label-text">Lampiran Foto / Berkas Pendukung (Opsional)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-surface-200 dark:border-surface-700 border-dashed rounded-2xl bg-surface-50/50 dark:bg-surface-900/30 hover:border-primary-500/50 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-10 w-10 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="flex text-xs text-surface-600 dark:text-surface-400">
                                    <label class="relative cursor-pointer font-bold text-primary-700 dark:text-primary-400 hover:underline">
                                        <span>Unggah foto/berkas</span>
                                        <input type="file" @change="handleFile" class="sr-only" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                                    </label>
                                    <p class="pl-1">atau tarik dan lepas di sini</p>
                                </div>
                                <p class="text-[11px] text-surface-400">Format: JPG, PNG, PDF, DOC (Maks: 5MB)</p>
                                <p v-if="form.lampiran" class="text-xs font-bold text-primary-700 dark:text-primary-400 pt-2">
                                    ✓ Berkas terpilih: {{ form.lampiran.name }}
                                </p>
                            </div>
                        </div>
                        <p v-if="form.errors.lampiran" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.lampiran }}
                        </p>
                    </div>

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

