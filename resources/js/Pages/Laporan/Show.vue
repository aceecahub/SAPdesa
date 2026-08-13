<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    laporan: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => ['admin', 'superadmin'].includes(user.value?.role));

const statusForm = useForm({
    status: props.laporan.status,
    respon_admin: props.laporan.respon_admin || '',
});

const submitStatus = () => {
    statusForm.patch(route('laporan.updateStatus', props.laporan.id));
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

const formatDateTime = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const statusOptions = [
    { value: 'baru', label: 'Baru' },
    { value: 'diproses', label: 'Diproses' },
    { value: 'selesai', label: 'Selesai' },
    { value: 'ditolak', label: 'Ditolak' },
];

const lampiranUrl = computed(() => {
    if (!props.laporan.lampiran) return null;
    return `/storage/${props.laporan.lampiran}`;
});

const isImage = computed(() => {
    if (!props.laporan.lampiran) return false;
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(props.laporan.lampiran);
});
</script>

<template>
    <Head :title="laporan.judul" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('laporan.index')" class="text-surface-400 hover:text-surface-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-lg font-semibold text-surface-900">Detail Laporan</h1>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Laporan Info -->
                <div class="card p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-surface-900 mb-2">{{ laporan.judul }}</h2>
                            <div class="flex items-center gap-3 text-sm text-surface-500">
                                <span>{{ laporan.dusun?.nama }}</span>
                                <span class="text-surface-300">·</span>
                                <span>{{ laporan.kategori?.nama }}</span>
                                <span class="text-surface-300">·</span>
                                <span>{{ formatDate(laporan.tanggal) }}</span>
                            </div>
                        </div>
                        <StatusBadge :status="laporan.status" />
                    </div>

                    <div class="border-t border-surface-100 pt-4">
                        <p class="text-surface-700 whitespace-pre-wrap leading-relaxed">{{ laporan.isi }}</p>
                    </div>

                    <!-- Lampiran -->
                    <div v-if="laporan.lampiran" class="border-t border-surface-100 pt-4 mt-4">
                        <p class="text-sm font-medium text-surface-600 mb-2">Lampiran</p>
                        <div v-if="isImage" class="border border-surface-200 rounded overflow-hidden max-w-md">
                            <img :src="lampiranUrl" alt="Lampiran" class="w-full h-auto" />
                        </div>
                        <a v-else :href="lampiranUrl" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                            </svg>
                            {{ laporan.lampiran.split('/').pop() }}
                        </a>
                    </div>

                    <!-- Respon Admin -->
                    <div v-if="laporan.respon_admin" class="border-t border-surface-100 pt-4 mt-4">
                        <p class="text-sm font-medium text-surface-600 mb-2">Respon Admin</p>
                        <div class="bg-primary-50 border border-primary-100 rounded p-4">
                            <p class="text-sm text-primary-800 whitespace-pre-wrap">{{ laporan.respon_admin }}</p>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div v-if="laporan.status === 'baru' && user.id === laporan.user_id" class="border-t border-surface-100 pt-4 mt-4">
                        <Link :href="route('laporan.edit', laporan.id)" class="btn-secondary text-sm">
                            Edit Laporan
                        </Link>
                    </div>
                </div>

                <!-- Admin: Update Status Form -->
                <div v-if="isAdmin" class="card p-6">
                    <h3 class="text-sm font-semibold text-surface-700 mb-4">Kelola Status Laporan</h3>
                    <form @submit.prevent="submitStatus" class="space-y-4">
                        <div>
                            <label class="label-text">Status</label>
                            <select v-model="statusForm.status" class="input-field">
                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="label-text">Respon / Umpan Balik</label>
                            <textarea v-model="statusForm.respon_admin" class="input-field" rows="3" placeholder="Tuliskan respon resmi dari pihak desa..."></textarea>
                        </div>

                        <button type="submit" class="btn-primary" :disabled="statusForm.processing">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar: Info & Timeline -->
            <div class="space-y-6">
                <!-- Info Card -->
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-surface-700 mb-4">Informasi</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-surface-500">Pelapor</dt>
                            <dd class="font-medium text-surface-900">{{ laporan.user?.name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-surface-500">Dusun</dt>
                            <dd class="font-medium text-surface-900">{{ laporan.dusun?.nama }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-surface-500">Kategori</dt>
                            <dd class="font-medium text-surface-900 text-right">
                                {{ laporan.kategori?.nama }}
                                <div v-if="laporan.subkategori" class="text-xs text-surface-400">{{ laporan.subkategori.nama }}</div>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-surface-500">Tanggal</dt>
                            <dd class="font-medium text-surface-900">{{ formatDate(laporan.tanggal) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-surface-500">Status</dt>
                            <dd><StatusBadge :status="laporan.status" /></dd>
                        </div>
                    </dl>
                </div>

                <!-- Timeline -->
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-surface-700 mb-4">Riwayat Tindak Lanjut</h3>
                    <div v-if="laporan.tindak_lanjut?.length" class="space-y-0">
                        <div
                            v-for="(tl, i) in laporan.tindak_lanjut"
                            :key="tl.id"
                            class="relative pl-5 pb-5 last:pb-0"
                        >
                            <!-- Timeline line -->
                            <div v-if="i < laporan.tindak_lanjut.length - 1" class="absolute left-[7px] top-3 bottom-0 w-px bg-surface-200"></div>
                            <!-- Dot -->
                            <div class="absolute left-0 top-1.5 w-[15px] h-[15px] rounded-full border-2 border-primary-500 bg-white"></div>
                            <div>
                                <p class="text-sm text-surface-700">{{ tl.keterangan }}</p>
                                <p class="text-xs text-surface-400 mt-1">
                                    {{ tl.user?.name }} · {{ formatDateTime(tl.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-surface-400">Belum ada riwayat</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
