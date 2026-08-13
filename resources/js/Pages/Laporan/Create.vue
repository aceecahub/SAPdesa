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
    <Head title="Buat Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Buat Laporan Baru</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Judul Laporan <span class="text-red-500">*</span></label>
                        <input v-model="form.judul" type="text" class="input-field" placeholder="Ringkasan singkat laporan" />
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

                    <div v-if="!isKepalaDusun">
                        <label class="label-text">Dusun <span class="text-red-500">*</span></label>
                        <select v-model="form.dusun_id" class="input-field">
                            <option value="">Pilih dusun</option>
                            <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                        </select>
                        <p v-if="form.errors.dusun_id" class="text-sm text-red-600 mt-1">{{ form.errors.dusun_id }}</p>
                    </div>

                    <div v-if="isKepalaDusun" class="bg-surface-50 border border-surface-200 rounded p-3">
                        <p class="text-sm text-surface-600">
                            Dusun: <span class="font-medium text-surface-900">{{ user?.dusun?.nama || dusunList[0]?.nama }}</span>
                            <span class="text-surface-400">(otomatis sesuai dusun Anda)</span>
                        </p>
                    </div>

                    <div>
                        <label class="label-text">Isi Laporan <span class="text-red-500">*</span></label>
                        <textarea v-model="form.isi" class="input-field" rows="6" placeholder="Tuliskan isi laporan secara detail..."></textarea>
                        <p v-if="form.errors.isi" class="text-sm text-red-600 mt-1">{{ form.errors.isi }}</p>
                    </div>

                    <div>
                        <label class="label-text">Lampiran</label>
                        <input type="file" @change="handleFile" class="block w-full text-sm text-surface-500 file:mr-4 file:py-2 file:px-4 file:border file:border-surface-300 file:rounded file:text-sm file:font-medium file:bg-white file:text-surface-700 hover:file:bg-surface-50 file:cursor-pointer file:transition-colors" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                        <p class="text-xs text-surface-400 mt-1">Format: JPG, PNG, PDF, DOC. Maks: 5MB</p>
                        <p v-if="form.errors.lampiran" class="text-sm text-red-600 mt-1">{{ form.errors.lampiran }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Kirim Laporan
                        </button>
                        <Link :href="route('laporan.index')" class="btn-secondary">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
