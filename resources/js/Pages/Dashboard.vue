<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, ArcElement, Title, Tooltip, Legend);
const props = defineProps({
    stats: Object,
    perKategori: Object,
    perSubkategori: Object,
    perDusun: Object,
    perBulan: Object,
    laporanTerbaru: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isKepalaDusun = computed(() => user.value?.role === 'kepala_dusun');

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1, font: { family: 'Inter', size: 11 } },
            grid: { color: 'rgba(0,0,0,0.04)' },
        },
        x: {
            ticks: { font: { family: 'Inter', size: 11 } },
            grid: { display: false },
        },
    },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: { font: { family: 'Inter', size: 12 }, padding: 16 },
        },
    },
};

const perBulanData = computed(() => {
    const labels = Object.keys(props.perBulan || {}).map((b) => {
        const [y, m] = b.split('-');
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return months[parseInt(m) - 1] + ' ' + y;
    });
    return {
        labels,
        datasets: [{
            data: Object.values(props.perBulan || {}),
            borderColor: '#2d5a27', // Leaf Green
            backgroundColor: '#2d5a27',
            borderWidth: 2,
            tension: 0.3,
            pointRadius: 4,
            pointHoverRadius: 6,
        }],
    };
});

const perKategoriData = computed(() => ({
    labels: Object.keys(props.perKategori || {}),
    datasets: [{
        data: Object.values(props.perKategori || {}),
        backgroundColor: ['#1e4620', '#2d5a27', '#8c6239', '#e8efe9', '#689f72'], // Forest Green, Leaf Green, Wood, Sage, Light Green
        borderWidth: 0,
    }],
}));

const perDusunData = computed(() => ({
    labels: Object.keys(props.perDusun || {}),
    datasets: [{
        data: Object.values(props.perDusun || {}),
        backgroundColor: '#2d5a27',
        borderRadius: 3,
    }],
}));

const perSubkategoriData = computed(() => ({
    labels: Object.keys(props.perSubkategori || {}),
    datasets: [{
        data: Object.values(props.perSubkategori || {}),
        backgroundColor: '#8c6239', // Wood Brown
        borderRadius: 3,
    }],
}));

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold tracking-tight text-surface-900 dark:text-surface-50">Dashboard Utama</h1>
        </template>

        <!-- Greeting Banner -->
        <div class="mb-6 px-4 sm:px-0 pt-2 sm:pt-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-surface-600 dark:text-surface-400 text-sm">
                        Selamat datang kembali, <span class="font-bold text-surface-900 dark:text-surface-100">{{ user?.name }}</span>
                        <template v-if="isKepalaDusun && user?.dusun"> — <span class="font-semibold text-primary-700 dark:text-primary-400">{{ user.dusun.nama }}</span></template>
                    </p>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5 mb-6 px-4 sm:px-0">
            <StatCard label="Total Laporan" :value="stats.total" icon="total" color="primary" />
            <StatCard label="Baru" :value="stats.baru" icon="baru" color="amber" />
            <StatCard label="Diproses" :value="stats.diproses" icon="diproses" color="blue" />
            <StatCard label="Selesai" :value="stats.selesai" icon="selesai" color="green" />
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 px-4 sm:px-0">
            <!-- Per Bulan -->
            <div class="card p-5 lg:col-span-2 rounded-2xl">
                <h3 class="text-sm font-bold text-surface-800 dark:text-surface-200 mb-4 tracking-tight">Laporan per Bulan</h3>
                <div class="h-56">
                    <Line v-if="Object.keys(perBulan || {}).length" :data="perBulanData" :options="chartOptions" />
                    <div v-else class="flex items-center justify-center h-full text-surface-400 dark:text-surface-500 text-sm font-medium">
                        Belum ada data laporan
                    </div>
                </div>
            </div>

            <!-- Per Kategori -->
            <div class="card p-5 rounded-2xl">
                <h3 class="text-sm font-bold text-surface-800 dark:text-surface-200 mb-4 tracking-tight">Per Kategori</h3>
                <div class="h-56">
                    <Doughnut v-if="Object.keys(perKategori || {}).length" :data="perKategoriData" :options="doughnutOptions" />
                    <div v-else class="flex items-center justify-center h-full text-surface-400 dark:text-surface-500 text-sm font-medium">
                        Belum ada data
                    </div>
                </div>
            </div>
        </div>

        <!-- Per Dusun (admin/superadmin only) -->
        <div v-if="!isKepalaDusun && Object.keys(perDusun || {}).length" class="card p-5 mb-6 rounded-2xl mx-4 sm:mx-0">
            <h3 class="text-sm font-bold text-surface-800 dark:text-surface-200 mb-4 tracking-tight">Laporan per Dusun</h3>
            <div class="h-48">
                <Bar :data="perDusunData" :options="chartOptions" />
            </div>
        </div>

        <!-- Per Subkategori -->
        <div v-if="Object.keys(perSubkategori || {}).length" class="card p-5 mb-6 rounded-2xl mx-4 sm:mx-0">
            <h3 class="text-sm font-bold text-surface-800 dark:text-surface-200 mb-4 tracking-tight">Aduan Terbanyak per Subkategori</h3>
            <div class="h-48">
                <Bar :data="perSubkategoriData" :options="chartOptions" />
            </div>
        </div>

        <!-- Laporan Terbaru -->
        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between">
                <h3 class="text-sm font-bold text-surface-800 dark:text-surface-200 tracking-tight">Laporan Terbaru</h3>
                <Link :href="route('laporan.index')" class="text-xs text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-bold flex items-center gap-1 transition-colors">
                    <span>Lihat semua</span>
                    <span>&rarr;</span>
                </Link>
            </div>
            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Dusun</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in laporanTerbaru" :key="item.id">
                            <td>
                                <Link :href="route('laporan.show', item.id)" class="text-primary-700 hover:text-primary-800 dark:text-primary-400 font-semibold transition-colors">
                                    {{ item.judul }}
                                </Link>
                            </td>
                            <td class="font-medium">{{ item.dusun?.nama || '-' }}</td>
                            <td class="font-medium">{{ item.kategori?.nama || '-' }}</td>
                            <td class="text-xs text-surface-500 font-medium">{{ formatDate(item.tanggal) }}</td>
                            <td><StatusBadge :status="item.status" /></td>
                        </tr>
                        <tr v-if="!laporanTerbaru?.length">
                            <td colspan="5" class="text-center text-surface-400 dark:text-surface-500 py-8 font-medium">Belum ada laporan</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700/60">
                <div v-if="!laporanTerbaru?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm font-medium">
                    Belum ada laporan
                </div>
                <Link
                    v-for="item in laporanTerbaru"
                    :key="item.id"
                    :href="route('laporan.show', item.id)"
                    class="flex flex-col gap-2 px-4 py-3.5 hover:bg-surface-50/80 dark:hover:bg-surface-700/40 active:bg-surface-100 dark:active:bg-surface-700/70 transition-all"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-bold text-sm text-surface-900 dark:text-surface-100 leading-snug">{{ item.judul }}</span>
                        <StatusBadge :status="item.status" />
                    </div>
                    <div class="flex items-center gap-3 text-xs font-medium text-surface-500 dark:text-surface-400">
                        <span v-if="item.dusun" class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            {{ item.dusun.nama }}
                        </span>
                        <span v-if="item.kategori" class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-surface-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                            {{ item.kategori.nama }}
                        </span>
                        <span class="ml-auto flex items-center gap-1 text-surface-400">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" /></svg>
                            {{ formatDate(item.tanggal) }}
                        </span>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
