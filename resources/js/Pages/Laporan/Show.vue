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

const statusOrderMap = {
    'baru': 1,
    'diproses': 2,
    'selesai': 3,
    'ditolak': 3,
};

const availableStatusOptions = computed(() => {
    const rawOptions = [
        { value: 'baru', label: 'Baru' },
        { value: 'diproses', label: 'Diproses' },
        { value: 'selesai', label: 'Selesai' },
        { value: 'ditolak', label: 'Ditolak' },
    ];

    const currentStatus = props.laporan.status;
    const currentOrder = statusOrderMap[currentStatus] || 1;

    return rawOptions.map(opt => {
        const optOrder = statusOrderMap[opt.value] || 1;
        let disabled = false;

        if (optOrder < currentOrder) {
            disabled = true;
        } else if (currentOrder === 3 && opt.value !== currentStatus) {
            disabled = true;
        }

        return {
            ...opt,
            disabled,
        };
    });
});

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
    <Head :title="`Detail - ${laporan.judul}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('laporan.index')"
                    class="p-2 text-surface-500 hover:text-surface-900 dark:text-surface-400 dark:hover:text-surface-100 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-xl transition-all shadow-xs"
                    title="Kembali ke Daftar Laporan"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Detail Laporan Aspirasi
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Rincian aduan dan perkembangan tindak lanjut
                    </p>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mx-4 sm:mx-0">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Laporan Card -->
                <div class="card p-6 rounded-2xl">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-surface-700 dark:text-surface-300 bg-surface-100 dark:bg-surface-700/60 px-2.5 py-1 rounded-lg">
                                    📍 RW {{ laporan.rw ? String(laporan.rw.nomor).padStart(2, '0') : '-' }} – Dusun {{ laporan.rw?.dusun?.nama || '-' }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary-800 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/60 px-2.5 py-1 rounded-lg">
                                    🏷️ {{ laporan.kategori?.nama || '-' }}
                                    <template v-if="laporan.subkategori"> / {{ laporan.subkategori.nama }}</template>
                                </span>
                            </div>

                            <h2 class="text-xl sm:text-2xl font-extrabold text-surface-900 dark:text-surface-50 tracking-tight leading-snug">
                                {{ laporan.judul }}
                            </h2>

                            <div class="flex items-center gap-2 text-xs font-medium text-surface-500 dark:text-surface-400">
                                <span>Oleh: <strong class="text-surface-800 dark:text-surface-200">{{ laporan.user?.name }}</strong></span>
                                <span>·</span>
                                <span>📅 {{ formatDate(laporan.tanggal) }}</span>
                            </div>
                        </div>

                        <StatusBadge :status="laporan.status" class="shrink-0" />
                    </div>

                    <!-- Report Content Body -->
                    <div class="border-t border-surface-100 dark:border-surface-700/60 pt-5 mt-4">
                        <h4 class="text-xs font-bold text-surface-400 dark:text-surface-500 uppercase tracking-wider mb-2">
                            Isi Aduan / Aspirasi:
                        </h4>
                        <p class="text-surface-800 dark:text-surface-200 whitespace-pre-wrap leading-relaxed text-sm sm:text-base font-normal">
                            {{ laporan.isi }}
                        </p>
                    </div>

                    <!-- Lampiran File -->
                    <div v-if="laporan.lampiran" class="border-t border-surface-100 dark:border-surface-700/60 pt-5 mt-5">
                        <h4 class="text-xs font-bold text-surface-400 dark:text-surface-500 uppercase tracking-wider mb-3">
                            Lampiran Berkas:
                        </h4>
                        <div v-if="isImage" class="border border-surface-200 dark:border-surface-700 rounded-2xl overflow-hidden max-w-md bg-surface-50 dark:bg-surface-900/50 p-1">
                            <img :src="lampiranUrl" alt="Lampiran" class="w-full h-auto rounded-xl object-cover" />
                        </div>
                        <a
                            v-else
                            :href="lampiranUrl"
                            target="_blank"
                            class="inline-flex items-center gap-2 text-sm text-primary-700 hover:text-primary-800 dark:text-primary-400 font-bold px-4 py-2.5 bg-primary-50 dark:bg-primary-950/50 border border-primary-200 dark:border-primary-800 rounded-xl transition-all shadow-xs"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                            </svg>
                            <span>Download Lampiran ({{ laporan.lampiran.split('/').pop() }})</span>
                        </a>
                    </div>

                    <!-- Official Admin Response -->
                    <div v-if="laporan.respon_admin" class="border-t border-surface-100 dark:border-surface-700/60 pt-5 mt-5">
                        <h4 class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Tanggapan Resmi Pemerintah Desa:
                        </h4>
                        <div class="bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/80 rounded-2xl p-4">
                            <p class="text-sm text-emerald-900 dark:text-emerald-200 whitespace-pre-wrap leading-relaxed font-medium">
                                {{ laporan.respon_admin }}
                            </p>
                        </div>
                    </div>

                    <!-- Action for Reporter -->
                    <div v-if="laporan.status === 'baru' && user.id === laporan.user_id" class="border-t border-surface-100 dark:border-surface-700/60 pt-4 mt-5 flex justify-end">
                        <Link :href="route('laporan.edit', laporan.id)" class="btn-secondary text-xs font-bold">
                            ✏️ Edit Laporan Saya
                        </Link>
                    </div>
                </div>

                <!-- Admin Status Update Card -->
                <div v-if="isAdmin" class="card p-6 rounded-2xl border-2 border-primary-500/20 shadow-md">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 flex items-center justify-center font-bold text-sm">
                            ⚙️
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-surface-900 dark:text-surface-100 tracking-tight">
                                Kelola Status & Tanggapan Laporan
                            </h3>
                            <p class="text-xs text-surface-500">Formulir perubahan status untuk pengelola desa</p>
                        </div>
                    </div>

                    <form @submit.prevent="submitStatus" class="space-y-4">
                        <div>
                            <label class="label-text">Pilih Status Penanganan</label>
                            <select v-model="statusForm.status" class="input-field">
                                <option
                                    v-for="opt in availableStatusOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                    :disabled="opt.disabled"
                                    :class="opt.disabled ? 'text-surface-400 bg-surface-100 dark:bg-surface-800' : ''"
                                >
                                    {{ opt.label }} {{ opt.disabled ? '(Sudah dilewati)' : '' }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="label-text">Tanggapan / Respon Resmi Desa</label>
                            <textarea
                                v-model="statusForm.respon_admin"
                                class="input-field"
                                rows="3"
                                placeholder="Tuliskan respon resmi, langkah penanganan, atau catatan untuk pelapor..."
                            ></textarea>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn-primary text-xs font-bold" :disabled="statusForm.processing">
                                Simpan Perubahan Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar Info & Timeline -->
            <div class="space-y-6">
                <!-- Info Summary Card -->
                <div class="card p-5 rounded-2xl">
                    <h3 class="text-xs font-bold text-surface-500 uppercase tracking-wider mb-4 pb-2 border-b border-surface-100 dark:border-surface-700/60">
                        Informasi Detail
                    </h3>
                    <dl class="space-y-3.5 text-xs">
                        <div class="flex justify-between items-center">
                            <dt class="text-surface-500">Nama Pelapor</dt>
                            <dd class="font-bold text-surface-900 dark:text-surface-100">{{ laporan.user?.name }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-surface-500">Wilayah RW</dt>
                            <dd class="font-bold text-surface-900 dark:text-surface-100">RW {{ laporan.rw ? String(laporan.rw.nomor).padStart(2, '0') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-surface-500">Dusun Induk</dt>
                            <dd class="font-bold text-surface-900 dark:text-surface-100">Dusun {{ laporan.rw?.dusun?.nama || '-' }}</dd>
                        </div>
                        <div class="flex justify-between items-start">
                            <dt class="text-surface-500">Kategori</dt>
                            <dd class="font-bold text-surface-900 dark:text-surface-100 text-right">
                                {{ laporan.kategori?.nama }}
                                <div v-if="laporan.subkategori" class="text-[11px] text-surface-400 font-normal">
                                    {{ laporan.subkategori.nama }}
                                </div>
                            </dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-surface-500">Tanggal Masuk</dt>
                            <dd class="font-bold text-surface-900 dark:text-surface-100">{{ formatDate(laporan.tanggal) }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-surface-500">Status Saat Ini</dt>
                            <dd><StatusBadge :status="laporan.status" /></dd>
                        </div>
                    </dl>
                </div>

                <!-- Timeline / Riwayat Card -->
                <div class="card p-5 rounded-2xl">
                    <h3 class="text-xs font-bold text-surface-500 uppercase tracking-wider mb-4 pb-2 border-b border-surface-100 dark:border-surface-700/60">
                        Riwayat Tindak Lanjut
                    </h3>

                    <div v-if="laporan.tindak_lanjut?.length" class="space-y-0 pt-2">
                        <div
                            v-for="(tl, i) in laporan.tindak_lanjut"
                            :key="tl.id"
                            class="relative pl-6 pb-5 last:pb-0"
                        >
                            <!-- Timeline line -->
                            <div v-if="i < laporan.tindak_lanjut.length - 1" class="absolute left-[7px] top-3 bottom-0 w-0.5 bg-primary-200 dark:bg-primary-900"></div>
                            
                            <!-- Dot -->
                            <div class="absolute left-0 top-1.5 w-4 h-4 rounded-full border-2 border-primary-600 bg-white dark:bg-surface-800 shadow-xs"></div>
                            
                            <div class="bg-surface-50 dark:bg-surface-700/40 p-3 rounded-xl border border-surface-200/60 dark:border-surface-700/60">
                                <p class="text-xs text-surface-800 dark:text-surface-200 font-semibold leading-relaxed">
                                    {{ tl.keterangan }}
                                </p>
                                <p class="text-[11px] text-surface-400 dark:text-surface-400 mt-1 font-medium">
                                    👤 {{ tl.user?.name }} · {{ formatDateTime(tl.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-6 text-center text-surface-400 dark:text-surface-500 text-xs font-medium">
                        Belum ada catatan riwayat tindak lanjut
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

