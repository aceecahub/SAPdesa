<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    userEdit: Object,
    dusunList: Array,
});

const form = useForm({
    name: props.userEdit.name,
    email: props.userEdit.email,
    password: '',
    password_confirmation: '',
    role: props.userEdit.role,
    dusun_id: props.userEdit.dusun_id || '',
});

watch(() => form.role, (val) => {
    if (val !== 'kepala_dusun') {
        form.dusun_id = '';
    }
});

const submit = () => {
    form.put(route('user.update', props.userEdit.id));
};
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-surface-900">Edit User</h1>
        </template>

        <div class="max-w-2xl">
            <div class="card p-6">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="label-text">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" class="input-field" />
                        <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="label-text">Email <span class="text-red-500">*</span></label>
                        <input v-model="form.email" type="email" class="input-field" />
                        <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label-text">Password Baru</label>
                            <input v-model="form.password" type="password" class="input-field" placeholder="Kosongkan jika tidak diubah" />
                            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="label-text">Konfirmasi Password</label>
                            <input v-model="form.password_confirmation" type="password" class="input-field" />
                        </div>
                    </div>

                    <div>
                        <label class="label-text">Role <span class="text-red-500">*</span></label>
                        <select v-model="form.role" class="input-field">
                            <option value="kepala_dusun">Kepala Dusun</option>
                            <option value="admin">Admin Desa</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                        <p v-if="form.errors.role" class="text-sm text-red-600 mt-1">{{ form.errors.role }}</p>
                    </div>

                    <div v-if="form.role === 'kepala_dusun'">
                        <label class="label-text">Dusun <span class="text-red-500">*</span></label>
                        <select v-model="form.dusun_id" class="input-field">
                            <option value="">Pilih dusun</option>
                            <option v-for="d in dusunList" :key="d.id" :value="d.id">{{ d.nama }}</option>
                        </select>
                        <p v-if="form.errors.dusun_id" class="text-sm text-red-600 mt-1">{{ form.errors.dusun_id }}</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            Perbarui
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
