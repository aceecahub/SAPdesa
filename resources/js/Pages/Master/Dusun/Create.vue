<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    nama: '',
    jumlah_penduduk: '',
    nama_kepala: '',
    foto_kepala: null,
    email: '',
    password: '',
    password_confirmation: '',
});

const imagePreview = ref(null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.foto_kepala = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('master.dusun.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Tambah Dusun Baru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('master.dusun.index')"
                    class="p-2 text-surface-500 hover:text-surface-900 dark:text-surface-400 dark:hover:text-surface-100 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl transition-all shadow-xs"
                    title="Kembali"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Tambah Dusun Baru
                    </h1>
                </div>
            </div>
        </template>

        <div class="max-w-2xl mx-4 sm:mx-0">
            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-surface-400 uppercase tracking-wider">Informasi Wilayah Dusun</h3>

                        <div>
                            <label class="label-text">
                                Nama Dusun <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.nama" type="text" class="input-field" placeholder="Contoh: Dusun Ciherang" />
                            <p v-if="form.errors.nama" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">{{ form.errors.nama }}</p>
                        </div>

                        <div>
                            <label class="label-text">Jumlah Penduduk (Jiwa)</label>
                            <input v-model="form.jumlah_penduduk" type="number" min="0" class="input-field" placeholder="Contoh: 1250" />
                            <p v-if="form.errors.jumlah_penduduk" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">{{ form.errors.jumlah_penduduk }}</p>
                        </div>

                        <div>
                            <label class="label-text">Nama Kepala Dusun</label>
                            <input v-model="form.nama_kepala" type="text" class="input-field" placeholder="Masukkan nama lengkap Kepala Dusun" />
                            <p v-if="form.errors.nama_kepala" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">{{ form.errors.nama_kepala }}</p>
                        </div>

                        <!-- Upload Foto Kepala Dusun -->
                        <div>
                            <label class="label-text">Foto Profil Kepala Dusun (Opsional)</label>
                            <div class="mt-2 flex items-center gap-4">
                                <div class="w-16 h-16 rounded-full overflow-hidden bg-surface-100 dark:bg-surface-700 border-2 border-primary-500/40 shrink-0 flex items-center justify-center text-xl font-black text-surface-400 shadow-sm">
                                    <img v-if="imagePreview" :src="imagePreview" alt="Preview Foto" class="w-full h-full object-cover" />
                                    <span v-else>👤</span>
                                </div>

                                <div class="flex-1">
                                    <input
                                        type="file"
                                        @change="handleFileChange"
                                        accept="image/*"
                                        class="block w-full text-xs text-surface-500 file:mr-4 file:py-2 file:px-4 file:border file:border-surface-200 dark:file:border-surface-700 file:rounded-xl file:text-xs file:font-bold file:bg-surface-100 dark:file:bg-surface-700 file:text-surface-700 dark:file:text-surface-200 hover:file:bg-surface-200 dark:hover:file:bg-surface-600 file:cursor-pointer file:transition-colors"
                                    />
                                    <p class="text-[11px] text-surface-400 dark:text-surface-500 mt-1">Format: JPG, PNG, WEBP. Maksimum 2MB.</p>
                                </div>
                            </div>
                            <p v-if="form.errors.foto_kepala" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">{{ form.errors.foto_kepala }}</p>
                        </div>
                    </div>

                    <!-- User Account Section -->
                    <div class="border-t border-surface-100 dark:border-surface-700/60 pt-6 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🔐</span>
                            <h3 class="text-xs font-bold text-surface-900 dark:text-surface-100 uppercase tracking-wider">
                                Akun Login Kepala Dusun
                            </h3>
                        </div>

                        <div>
                            <label class="label-text">
                                Email Login <span class="text-rose-500">*</span>
                            </label>
                            <input v-model="form.email" type="email" class="input-field" placeholder="kadus.ciherang@sirnaputra.desa.id" />
                            <p v-if="form.errors.email" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">{{ form.errors.email }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label-text">
                                    Password <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.password" type="password" class="input-field" placeholder="••••••••" />
                                <p v-if="form.errors.password" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <label class="label-text">
                                    Konfirmasi Password <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="form.password_confirmation" type="password" class="input-field" placeholder="••••••••" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-100 dark:border-surface-700/60">
                        <Link :href="route('master.dusun.index')" class="btn-secondary text-xs font-bold">
                            Batal
                        </Link>
                        <button type="submit" class="btn-primary text-xs font-bold" :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Simpan Dusun & Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
