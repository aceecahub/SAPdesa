<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    dusunList: Array,
    rwList: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'ketua_rw',
    dusun_id: '',
    rw_id: '',
});

watch(() => form.role, (val) => {
    if (val !== 'kepala_dusun') {
        form.dusun_id = '';
    }
    if (val !== 'ketua_rw') {
        form.rw_id = '';
    }
});

const formatRwLabel = (rw) => {
    if (!rw) return '';
    const rwNum = 'RW ' + String(rw.nomor).padStart(2, '0');
    const dusunNama = rw.dusun?.nama ? ` – Dusun ${rw.dusun.nama}` : '';
    return `${rwNum}${dusunNama}`;
};

const submit = () => {
    form.post(route('user.store'));
};
</script>

<template>
    <Head title="Tambah User" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Tambah User</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" class="input-field" placeholder="Nama lengkap" />
                        <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="label-text">Email <span class="text-red-500">*</span></label>
                        <input v-model="form.email" type="email" class="input-field" placeholder="email@contoh.com" />
                        <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Password <span class="text-red-500">*</span></label>
                            <input v-model="form.password" type="password" class="input-field" placeholder="Min. 8 karakter" />
                            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="label-text">Konfirmasi Password <span class="text-red-500">*</span></label>
                            <input v-model="form.password_confirmation" type="password" class="input-field" />
                        </div>
                    </div>

                    <div>
                        <label class="label-text">Role / Hak Akses <span class="text-red-500">*</span></label>
                        <select v-model="form.role" class="input-field">
                            <option value="ketua_rw">Ketua RW (Hanya RW miliknya)</option>
                            <option value="kepala_dusun">Kepala Dusun (Melihat semua RW di dusunnya)</option>
                            <option value="admin">Admin Desa (Kelola seluruh Laporan & Master Data)</option>
                            <option value="superadmin">Superadmin (Akses Penuh Sistem & Kelola User)</option>
                        </select>
                        <p v-if="form.errors.role" class="text-sm text-red-600 mt-1">{{ form.errors.role }}</p>
                    </div>

                    <!-- Role Permission Preview Banner -->
                    <div class="bg-surface-50 dark:bg-surface-800/60 border border-surface-200 dark:border-surface-700/80 rounded-xl p-3.5 text-xs text-surface-600 dark:text-surface-300">
                        <span class="font-bold text-surface-900 dark:text-surface-100 block mb-1">Rincian Hak Akses (Permission):</span>
                        <ul v-if="form.role === 'ketua_rw'" class="list-disc list-inside space-y-0.5 text-surface-500 dark:text-surface-400">
                            <li>Membuat laporan aspirasi untuk RW-nya sendiri.</li>
                            <li>Melihat & mengedit laporan yang dibuat oleh dirinya sendiri.</li>
                        </ul>
                        <ul v-else-if="form.role === 'kepala_dusun'" class="list-disc list-inside space-y-0.5 text-surface-500 dark:text-surface-400">
                            <li>Melihat daftar RW di bawah dusunnya & mengedit data RW.</li>
                            <li>Melihat seluruh laporan yang masuk dari seluruh RW di dusunnya.</li>
                        </ul>
                        <ul v-else-if="form.role === 'admin'" class="list-disc list-inside space-y-0.5 text-surface-500 dark:text-surface-400">
                            <li>Akses penuh ke seluruh Laporan, Master Data (Dusun, RW, Kategori).</li>
                            <li>Mengubah status laporan (Diproses, Selesai, Ditolak) & memberi respon.</li>
                            <li>Export laporan ke CSV.</li>
                        </ul>
                        <ul v-else-if="form.role === 'superadmin'" class="list-disc list-inside space-y-0.5 text-surface-500 dark:text-surface-400">
                            <li>Hak akses penuh ke seluruh fitur sistem.</li>
                            <li>Kelola akun pengguna & pengaturan role permission.</li>
                        </ul>
                    </div>

                    <div v-if="form.role === 'ketua_rw'">
                        <label class="label-text">Pilih RW <span class="text-red-500">*</span></label>
                        <select v-model="form.rw_id" class="input-field">
                            <option value="">Pilih RW</option>
                            <option v-for="rw in rwList" :key="rw.id" :value="rw.id">
                                {{ formatRwLabel(rw) }}
                            </option>
                        </select>
                        <p v-if="form.errors.rw_id" class="text-sm text-red-600 mt-1">{{ form.errors.rw_id }}</p>
                    </div>

                    <div v-if="form.role === 'kepala_dusun'">
                        <label class="label-text">Pilih Dusun <span class="text-red-500">*</span></label>
                        <select v-model="form.dusun_id" class="input-field">
                            <option value="">Pilih dusun</option>
                            <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                        </select>
                        <p v-if="form.errors.dusun_id" class="text-sm text-red-600 mt-1">{{ form.errors.dusun_id }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            Simpan
                        </button>
                        <Link :href="route('user.index')" class="btn-secondary">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
