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
            <h1 class="text-lg font-semibold text-surface-900">Kelola User</h1>
        </template>

        <div class="card">
            <div class="px-5 py-4 border-b border-surface-200 flex items-center justify-between">
                <p class="text-sm text-surface-500">{{ users.total }} pengguna terdaftar</p>
                <Link :href="route('user.create')" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah User
                </Link>
            </div>

            <div class="table-container">
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
