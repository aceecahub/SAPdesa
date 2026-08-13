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
        _method: 'PUT',
    });
};

const handleFile = (e) => {
    form.lampiran = e.target.files[0];
};
</script>

<template>
    <Head title="Edit Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Edit Laporan</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Judul Laporan <span class="text-red-500">*</span></label>
                        <input v-model="form.judul" type="text" class="input-field" />
                        <p v-if="form.errors.judul" class="text-sm text-red-600 mt-1">{{ form.errors.judul }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Kategori <span class="text-red-500">*</span></label>
                            <select v-model="form.kategori_id" class="input-field">
                                <option value="">Pilih kategori</option>
                                <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                            </select>
                            <p v-if="form.errors.kategori_id" class="text-sm text-red-600 mt-1">{{ form.errors.kategori_id }}</p>
                        </div>
                        <div v-if="subkategoriList.length > 0">
                            <label class="label-text">Subkategori <span class="text-red-500">*</span></label>
                            <select v-model="form.subkategori_id" class="input-field">
                                <option value="">Pilih subkategori</option>
                                <option v-for="sk in subkategoriList" :key="sk.id" :value="sk.id">{{ sk.nama }}</option>
                            </select>
                            <p v-if="form.errors.subkategori_id" class="text-sm text-red-600 mt-1">{{ form.errors.subkategori_id }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="label-text">Isi Laporan <span class="text-red-500">*</span></label>
                        <textarea v-model="form.isi" class="input-field" rows="6"></textarea>
                        <p v-if="form.errors.isi" class="text-sm text-red-600 mt-1">{{ form.errors.isi }}</p>
                    </div>

                    <div>
                        <label class="label-text">Ganti Lampiran</label>
                        <input type="file" @change="handleFile" class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:border file:border-surface-300 file:rounded file:text-sm file:font-medium file:bg-white file:text-surface-700 hover:file:bg-surface-50 file:cursor-pointer file:transition-colors" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                        <p v-if="laporan.lampiran" class="text-xs text-surface-400 mt-1">Lampiran saat ini: {{ laporan.lampiran.split('/').pop() }}</p>
                        <p v-if="form.errors.lampiran" class="text-sm text-red-600 mt-1">{{ form.errors.lampiran }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            Perbarui
                        </button>
                        <Link :href="route('laporan.show', laporan.id)" class="btn-secondary">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
