<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nama: '',
    jumlah_penduduk: '',
    nama_kepala: '',
});

const submit = () => {
    form.post(route('master.dusun.store'));
};
</script>

<template>
    <Head title="Tambah Dusun" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Tambah Dusun</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Nama Dusun <span class="text-red-500">*</span></label>
                        <input v-model="form.nama" type="text" class="input-field" placeholder="Contoh: Dusun Ciherang" />
                        <p v-if="form.errors.nama" class="text-sm text-red-600 mt-1">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="label-text">Jumlah Penduduk</label>
                        <input v-model="form.jumlah_penduduk" type="number" min="0" class="input-field" placeholder="0" />
                        <p v-if="form.errors.jumlah_penduduk" class="text-sm text-red-600 mt-1">{{ form.errors.jumlah_penduduk }}</p>
                    </div>

                    <div>
                        <label class="label-text">Nama Kepala Dusun</label>
                        <input v-model="form.nama_kepala" type="text" class="input-field" placeholder="Nama lengkap" />
                        <p v-if="form.errors.nama_kepala" class="text-sm text-red-600 mt-1">{{ form.errors.nama_kepala }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            Simpan
                        </button>
                        <Link :href="route('master.dusun.index')" class="btn-secondary">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
