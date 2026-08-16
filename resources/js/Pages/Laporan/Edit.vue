<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    laporan: Object,
    kategoriList: Array,
});

const form = useForm({
    judul: props.laporan.judul,
    isi: props.laporan.isi,
    kategori_id: props.laporan.kategori_id,
    subkategori_id: props.laporan.subkategori_id || '',
    lampiran: null,
    _method: 'PUT',
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
    form.post(route('laporan.update', props.laporan.id), {
        forceFormData: true,
    });
};

const handleFile = (e) => {
    form.lampiran = e.target.files[0];
};
</script>

<template>
    <Head :title="`Edit - ${laporan.judul}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('laporan.show', laporan.id)"
                    class="p-2 text-surface-500 hover:text-surface-900 dark:text-surface-400 dark:hover:text-surface-100 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl transition-all shadow-xs"
                    title="Batal"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Edit Laporan Aspirasi
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Perbarui rincian aduan atau berkas pendukung
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-4 sm:mx-0">
            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="label-text">
                            Judul Laporan <span class="text-rose-500">*</span>
                        </label>
                        <input v-model="form.judul" type="text" class="input-field" />
                        <p v-if="form.errors.judul" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.judul }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="label-text">
                                Kategori <span class="text-rose-500">*</span>
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

                    <div>
                        <label class="label-text">
                            Isi Aduan / Aspirasi <span class="text-rose-500">*</span>
                        </label>
                        <textarea v-model="form.isi" class="input-field" rows="6"></textarea>
                        <p v-if="form.errors.isi" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.isi }}
                        </p>
                    </div>

                    <div>
                        <label class="label-text">Ganti Lampiran File (Opsional)</label>
                        <input
                            type="file"
                            @change="handleFile"
                            class="block w-full text-xs text-surface-500 file:mr-4 file:py-2 file:px-4 file:border file:border-surface-200 dark:file:border-surface-700 file:rounded-xl file:text-xs file:font-bold file:bg-surface-100 dark:file:bg-surface-700 file:text-surface-700 dark:file:text-surface-200 hover:file:bg-surface-200 dark:hover:file:bg-surface-600 file:cursor-pointer file:transition-colors"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                        />
                        <p v-if="laporan.lampiran" class="text-xs font-medium text-surface-500 dark:text-surface-400 mt-1.5">
                            📎 Lampiran saat ini: <strong class="text-surface-800 dark:text-surface-200">{{ laporan.lampiran.split('/').pop() }}</strong>
                        </p>
                        <p v-if="form.errors.lampiran" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
                            {{ form.errors.lampiran }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-100 dark:border-surface-700/60">
                        <Link :href="route('laporan.show', laporan.id)" class="btn-secondary text-xs font-bold">
                            Batal
                        </Link>
                        <button type="submit" class="btn-primary text-xs font-bold" :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Perbarui Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

