<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    kategori: Object,
});

const form = useForm({
    nama: props.kategori.nama,
    slug: props.kategori.slug,
});

const submit = () => {
    form.put(route('master.kategori.update', props.kategori.id));
};
</script>

<template>
    <Head title="Edit Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Edit Kategori</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Nama Kategori <span class="text-red-500">*</span></label>
                        <input v-model="form.nama" type="text" class="input-field" />
                        <p v-if="form.errors.nama" class="text-sm text-red-600 mt-1">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="label-text">Slug <span class="text-red-500">*</span></label>
                        <input v-model="form.slug" type="text" class="input-field" />
                        <p v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            Perbarui
                        </button>
                        <Link :href="route('master.kategori.index')" class="btn-secondary">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
