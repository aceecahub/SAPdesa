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

const roleBadge = (role) => {
    const map = {
        superadmin: 'bg-red-100 text-red-700',
        admin: 'bg-primary-100 text-primary-700',
        kepala_dusun: 'bg-amber-100 text-amber-700',
    };
    return map[role] || 'bg-surface-100 text-surface-700';
};

const roleLabel = (role) => {
    const map = {
        superadmin: 'Superadmin',
        admin: 'Admin',
        kepala_dusun: 'Kepala Dusun',
    };
    return map[role] || role;
};
</script>

<template>
    <Head title="Kelola User" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold tracking-tight text-surface-900 dark:text-surface-50">Kelola Pengguna</h1>
        </template>

        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0 mt-3 sm:mt-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between gap-4">
                <p class="text-sm font-semibold text-surface-600 dark:text-surface-400">Total <span class="font-bold text-surface-900 dark:text-surface-100">{{ users.total }}</span> pengguna terdaftar</p>
                <Link :href="route('user.create')" class="btn-primary text-xs sm:text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Tambah User Baru</span>
                </Link>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Dusun</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in users.data" :key="item.id">
                            <td class="font-medium text-surface-900">
                                {{ item.name }}
                                <span v-if="item.id === currentUser?.id" class="text-xs text-surface-400 ml-1">(Anda)</span>
                            </td>
                            <td class="text-surface-500">{{ item.email }}</td>
                            <td>
                                <span :class="['inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded', roleBadge(item.role)]">
                                    {{ roleLabel(item.role) }}
                                </span>
                            </td>
                            <td class="text-surface-500">{{ item.dusun?.nama || '-' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('user.edit', item.id)" class="btn-ghost text-xs py-1.5 px-2.5">
                                        Edit
                                    </Link>
                                    <button
                                        v-if="item.id !== currentUser?.id"
                                        @click="confirmDelete(item)"
                                        class="btn-ghost text-xs py-1.5 px-2.5 text-red-600 hover:bg-red-50"
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
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700">
                <div
                    v-for="item in users.data"
                    :key="item.id"
                    class="px-4 py-3.5"
                >
                    <!-- Nama + Role -->
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div>
                            <p class="font-semibold text-sm text-surface-900 dark:text-surface-100">
                                {{ item.name }}
                                <span v-if="item.id === currentUser?.id" class="text-xs text-surface-400 dark:text-surface-500 font-normal ml-1">(Anda)</span>
                            </p>
                            <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">{{ item.email }}</p>
                        </div>
                        <span :class="['inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded flex-shrink-0', roleBadge(item.role)]">
                            {{ roleLabel(item.role) }}
                        </span>
                    </div>
                    <!-- Dusun -->
                    <div v-if="item.dusun" class="flex items-center gap-1 text-xs text-surface-500 dark:text-surface-400 mb-3">
                        <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        <span>{{ item.dusun.nama }}</span>
                    </div>
                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <Link :href="route('user.edit', item.id)" class="btn-ghost text-xs py-1.5 px-3">
                            Edit
                        </Link>
                        <button
                            v-if="item.id !== currentUser?.id"
                            @click="confirmDelete(item)"
                            class="btn-ghost text-xs py-1.5 px-3 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 ml-auto"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3">
                <Pagination :links="users" />
            </div>
        </div>

        <ConfirmModal
            ref="deleteModal"
            title="Hapus User"
            :message="`Yakin ingin menghapus user '${deleteTarget?.name}'?`"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
