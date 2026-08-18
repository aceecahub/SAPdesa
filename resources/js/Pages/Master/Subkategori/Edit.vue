<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    subkategori: Object,
});

const form = useForm({
    nama: props.subkategori.nama,
    slug: props.subkategori.slug,
});

const submit = () => {
    form.put(route('master.subkategori.update', props.subkategori.id));
};
</script>

<template>
    <Head title="Edit Subkategori" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900 dark:text-surface-50">Edit Subkategori</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Nama Subkategori <span class="text-red-500">*</span></label>
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
                        <Link :href="route('master.subkategori.index')" class="btn-secondary">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
