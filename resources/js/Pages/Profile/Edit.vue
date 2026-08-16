<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Pengaturan Profil Saya" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Pengaturan Profil Saya
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Perbarui informasi akun, alamat email, dan kata sandi Anda
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-4 sm:mx-0 space-y-6">
            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="card p-6 sm:p-8 rounded-2xl shadow-sm border border-rose-200/60 dark:border-rose-900/40">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

