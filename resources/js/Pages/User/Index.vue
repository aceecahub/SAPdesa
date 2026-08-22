<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const deleteModal = ref(null);
const deleteTarget = ref(null);

const confirmDelete = (item) => {
    deleteTarget.value = item;
    deleteModal.value.show();
};

const handleDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('user.destroy', deleteTarget.value.id));
    }
};

const roleBadgeClass = (role) => {
    const map = {
        superadmin: 'bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/80',
        admin: 'bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/80',
        kepala_dusun: 'bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/80',
        ketua_rw: 'bg-blue-50 text-blue-700 border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/80',
    };
    return map[role] || 'bg-surface-100 text-surface-700 border-surface-200 dark:bg-surface-800 dark:text-surface-300';
};

const roleLabel = (role) => {
    const map = {
        superadmin: 'Superadmin',
        admin: 'Admin Desa',
        kepala_dusun: 'Kepala Dusun',
        ketua_rw: 'Ketua RW',
    };
    return map[role] || role;
};
</script>

<template>
    <Head title="Kelola Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Kelola Pengguna Sistem
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Manajemen akun Superadmin, Admin Desa, dan Kepala Dusun
                    </p>
                </div>
                <Link :href="route('user.create')" class="btn-primary text-xs sm:text-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Tambah User Baru</span>
                </Link>
            </div>
        </template>

        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0 mt-3 sm:mt-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                <p class="text-xs sm:text-sm font-medium text-surface-500 dark:text-surface-400">
                    Total <span class="font-bold text-surface-900 dark:text-surface-100">{{ users.total }}</span> pengguna terdaftar
                </p>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Pengguna</th>
                            <th>Alamat Email</th>
                            <th>Peran (Role)</th>
                            <th>Wilayah Dusun</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in users.data" :key="item.id">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-primary-100 dark:bg-primary-950 text-primary-800 dark:text-primary-300 border border-primary-200 dark:border-primary-800 flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                                        {{ item.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-surface-900 dark:text-surface-100 text-sm block">
                                            {{ item.name }}
                                            <span v-if="item.id === currentUser?.id" class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 ml-1">(Anda)</span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs font-medium text-surface-600 dark:text-surface-400">
                                {{ item.email }}
                            </td>
                            <td>
                                <span :class="['inline-flex items-center px-2.5 py-1 text-xs font-bold border rounded-full shadow-xs', roleBadgeClass(item.role)]">
                                    {{ roleLabel(item.role) }}
                                </span>
                            </td>
                            <td>
                                <span v-if="item.dusun" class="inline-flex items-center gap-1 text-xs font-semibold text-surface-700 dark:text-surface-300 bg-surface-100 dark:bg-surface-700 px-2.5 py-1 rounded-lg">
                                    📍 {{ item.dusun.nama }}
                                </span>
                                <span v-else class="text-xs text-surface-400 font-medium">-</span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Link :href="route('user.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40">
                                        Edit
                                    </Link>
                                    <button
                                        v-if="item.id !== currentUser?.id"
                                        @click="confirmDelete(item)"
                                        class="btn-ghost text-xs py-1.5 px-2.5 font-bold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700/60">
                <div
                    v-for="item in users.data"
                    :key="item.id"
                    class="px-4 py-3.5 space-y-2"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-primary-100 dark:bg-primary-950 text-primary-800 dark:text-primary-300 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ item.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-bold text-sm text-surface-900 dark:text-surface-100">
                                    {{ item.name }}
                                    <span v-if="item.id === currentUser?.id" class="text-xs text-primary-600 font-semibold ml-1">(Anda)</span>
                                </p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">{{ item.email }}</p>
                            </div>
                        </div>
                        <span :class="['inline-flex items-center px-2 py-0.5 text-[10px] font-bold border rounded-full shrink-0', roleBadgeClass(item.role)]">
                            {{ roleLabel(item.role) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <span v-if="item.dusun" class="font-medium text-surface-600 dark:text-surface-400">📍 Dusun {{ item.dusun.nama }}</span>
                        <span v-else class="text-surface-400">-</span>
                        <div class="flex items-center gap-1">
                            <Link :href="route('user.edit', item.id)" class="btn-ghost text-xs py-1 px-2 font-bold text-amber-600">
                                Edit
                            </Link>
                            <button
                                v-if="item.id !== currentUser?.id"
                                @click="confirmDelete(item)"
                                class="btn-ghost text-xs py-1 px-2 font-bold text-rose-600"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-surface-200/80 dark:border-surface-700/80">
                <Pagination :links="users" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus Akun Pengguna"
            :message="`Apakah Anda yakin ingin menghapus akun '${deleteTarget?.name}'?`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>

