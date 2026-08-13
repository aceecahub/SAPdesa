<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    kategoriList: Array,
});

const form = useForm({
    kategori_id: '',
    nama: '',
    slug: '',
});

// Auto-generate slug from nama
watch(() => form.nama, (val) => {
    form.slug = val
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
});

const submit = () => {
    form.post(route('master.subkategori.store'));
};
</script>

<template>
    <Head title="Tambah Subkategori" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Tambah Subkategori</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Kategori Induk <span class="text-red-500">*</span></label>
                        <select v-model="form.kategori_id" class="input-field">
                            <option value="">Pilih kategori...</option>
                            <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                        </select>
                        <p v-if="form.errors.kategori_id" class="text-sm text-red-600 mt-1">{{ form.errors.kategori_id }}</p>
                    </div>

                    <div>
                        <label class="label-text">Nama Subkategori <span class="text-red-500">*</span></label>
                        <input v-model="form.nama" type="text" class="input-field" placeholder="Contoh: Infrastruktur" />
                        <p v-if="form.errors.nama" class="text-sm text-red-600 mt-1">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="label-text">Slug <span class="text-red-500">*</span></label>
                        <input v-model="form.slug" type="text" class="input-field" placeholder="auto-generated" />
                        <p class="text-xs text-surface-400 mt-1">Otomatis dibuat dari nama</p>
                        <p v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            Simpan
                        </button>
                        <Link :href="route('master.subkategori.index')" class="btn-secondary">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
