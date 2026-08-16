<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
    Filler,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

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
const isSuperadmin = computed(() => user.value?.role === 'superadmin');

// Today Date Formatted
const todayFormatted = computed(() => {
    return new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});

// Chart global options
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            titleFont: { family: 'Inter', size: 12, weight: 'bold' },
            bodyFont: { family: 'Inter', size: 12 },
            padding: 10,
            cornerRadius: 10,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1, font: { family: 'Inter', size: 11 } },
            grid: { color: 'rgba(148, 163, 184, 0.1)' },
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
    cutout: '70%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                font: { family: 'Inter', size: 11, weight: '500' },
                padding: 16,
                usePointStyle: true,
                pointStyle: 'circle',
            },
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.9)',
            padding: 10,
            cornerRadius: 10,
        },
    },
};

// Colors Palette
const categoryColors = ['#2d6a4f', '#40916c', '#52b788', '#8c6239', '#d4a373', '#74c69d', '#95d5b2'];
const dusunColors = [
    '#2d6a4f', // Emerald
    '#0284c7', // Sky Blue
    '#d97706', // Amber / Gold
    '#7c3aed', // Purple
    '#e11d48', // Rose / Red
    '#059669', // Teal
    '#ea580c', // Orange
    '#0891b2', // Cyan
    '#4f46e5', // Indigo
    '#9333ea', // Violet
];

const perBulanData = computed(() => {
    const labels = Object.keys(props.perBulan || {}).map((b) => {
        const [y, m] = b.split('-');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        return months[parseInt(m) - 1] + ' ' + y;
    });
    return {
        labels,
        datasets: [
            {
                label: 'Jumlah Laporan',
                data: Object.values(props.perBulan || {}),
                borderColor: '#2d6a4f',
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 220);
                    gradient.addColorStop(0, 'rgba(45, 106, 79, 0.35)');
                    gradient.addColorStop(1, 'rgba(45, 106, 79, 0.0)');
                    return gradient;
                },
                fill: true,
                borderWidth: 2.5,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#2d6a4f',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverRadius: 6,
            },
        ],
    };
});

const perKategoriData = computed(() => ({
    labels: Object.keys(props.perKategori || {}),
    datasets: [
        {
            data: Object.values(props.perKategori || {}),
            backgroundColor: categoryColors.slice(0, Object.keys(props.perKategori || {}).length),
            borderWidth: 2,
            borderColor: 'rgba(255, 255, 255, 0.8)',
            hoverOffset: 6,
        },
    ],
}));

const perDusunData = computed(() => {
    const keys = Object.keys(props.perDusun || {});
    return {
        labels: keys,
        datasets: [
            {
                label: 'Jumlah Laporan',
                data: Object.values(props.perDusun || {}),
                backgroundColor: keys.map((_, i) => dusunColors[i % dusunColors.length]),
                borderRadius: 8,
            },
        ],
    };
});

const perSubkategoriData = computed(() => {
    const keys = Object.keys(props.perSubkategori || {});
    return {
        labels: keys,
        datasets: [
            {
                label: 'Laporan',
                data: Object.values(props.perSubkategori || {}),
                backgroundColor: keys.map((_, i) => dusunColors[(i + 2) % dusunColors.length]),
                borderRadius: 8,
            },
        ],
    };
});

// Top Kategori Ranked List
const topKategoriList = computed(() => {
    const total = props.stats?.total || 1;
    const entries = Object.entries(props.perKategori || {});
    return entries
        .map(([name, count], index) => ({
            name,
            count,
            percent: Math.round((count / total) * 100),
            color: categoryColors[index % categoryColors.length],
        }))
        .sort((a, b) => b.count - a.count)
        .slice(0, 5);
});

// Status Distribution Breakdown Bar
const statusBreakdown = computed(() => {
    const total = props.stats?.total || 0;
    if (total === 0) return [];

    return [
        { key: 'baru', label: 'Baru', count: props.stats.baru, color: 'bg-amber-500', textColor: 'text-amber-700 dark:text-amber-300', percent: Math.round((props.stats.baru / total) * 100) },
        { key: 'diproses', label: 'Diproses', count: props.stats.diproses, color: 'bg-blue-500', textColor: 'text-blue-700 dark:text-blue-300', percent: Math.round((props.stats.diproses / total) * 100) },
        { key: 'selesai', label: 'Selesai', count: props.stats.selesai, color: 'bg-emerald-500', textColor: 'text-emerald-700 dark:text-emerald-300', percent: Math.round((props.stats.selesai / total) * 100) },
        { key: 'ditolak', label: 'Ditolak', count: props.stats.ditolak, color: 'bg-rose-500', textColor: 'text-rose-700 dark:text-rose-300', percent: Math.round((props.stats.ditolak / total) * 100) },
    ].filter(item => item.count > 0);
});

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Dashboard Utama" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-surface-900 dark:text-surface-50">
                        Dashboard Utama
                    </h1>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        Sistem Informasi Pengaduan & Aspirasi Desa Sirnaputra
                    </p>
                </div>
            </div>
        </template>

        <!-- Hero Welcome Banner -->
        <div class="relative bg-gradient-to-br from-primary-900 via-primary-950 to-emerald-950 text-white rounded-3xl p-6 sm:p-8 overflow-hidden shadow-xl border border-primary-800/40 mb-6 mx-4 sm:mx-0">
            <!-- Background Decorative Glows -->
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -top-12 w-48 h-48 bg-primary-400/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold bg-white/10 backdrop-blur-md rounded-full text-primary-200 border border-white/15">
                            <svg class="w-3.5 h-3.5 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                            </svg>
                            {{ todayFormatted }}
                        </span>
                        <span v-if="isKepalaDusun && user?.dusun" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold bg-amber-500/20 backdrop-blur-md text-amber-200 rounded-full border border-amber-400/30">
                            📍 Dusun {{ user.dusun.nama }}
                        </span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white leading-tight">
                        Selamat Datang kembali, <span class="text-primary-300">{{ user?.name }}</span>!
                    </h2>
                    
                    <p class="text-sm text-surface-200 leading-relaxed font-normal">
                        <template v-if="isKepalaDusun">
                            Berikut adalah rangkuman laporan aduan & aspirasi masyarakat di wilayah <span class="font-semibold text-white">Dusun {{ user?.dusun?.nama || '' }}</span>.
                        </template>
                        <template v-else>
                            Pantau secara real-time pengaduan dan perkembangan layanan kemasyarakatan di seluruh dusun Desa Sirnaputra.
                        </template>
                    </p>

                    <!-- Alert Badge if there are unhandled 'baru' reports -->
                    <div v-if="stats.baru > 0" class="pt-2 flex items-center gap-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/20 border border-amber-400/40 text-amber-200 text-xs font-bold backdrop-blur-sm animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            Terdapat {{ stats.baru }} laporan baru membutuhkan tindakan segera!
                        </span>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <Link
                        :href="route('laporan.index')"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-surface-900 bg-white hover:bg-surface-100 rounded-xl shadow-md transition-all duration-200 active:scale-95"
                    >
                        <svg class="w-4 h-4 text-primary-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>Kelola Laporan</span>
                    </Link>
                    
                    <Link
                        v-if="!isKepalaDusun"
                        :href="route('export.index')"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/25 rounded-xl backdrop-blur-md transition-all duration-200 active:scale-95"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Export Data</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-5 mb-6 px-4 sm:px-0">
            <StatCard
                label="Total Laporan"
                :value="stats.total"
                icon="total"
                color="primary"
                :subtitle="`${stats.laporanBulanIni || 0} laporan bulan ini`"
                :trend="stats.trenBulanIni !== 0 ? `${stats.trenBulanIni > 0 ? '+' : ''}${stats.trenBulanIni}%` : null"
                :trendType="stats.trenBulanIni >= 0 ? 'up' : 'down'"
            />
            <StatCard
                label="Laporan Baru"
                :value="stats.baru"
                icon="baru"
                color="amber"
                subtitle="Perlu respon awal"
                :badge="stats.baru > 0 ? 'Pending' : null"
                badgeColor="amber"
            />
            <StatCard
                label="Diproses"
                :value="stats.diproses"
                icon="diproses"
                color="blue"
                subtitle="Sedang ditangani"
            />
            <StatCard
                label="Selesai"
                :value="stats.selesai"
                icon="selesai"
                color="green"
                subtitle="Tuntas ditindak"
            />
            <StatCard
                label="Penyelesaian"
                :value="`${stats.rateSelesai || 0}%`"
                icon="rate"
                color="purple"
                subtitle="Tingkat Respons"
                badge="Sangat Baik"
                badgeColor="emerald"
            />
        </div>

        <!-- Status Breakdown Progress Bar -->
        <div v-if="stats.total > 0" class="card p-5 mb-6 rounded-2xl mx-4 sm:mx-0">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-wider">
                    Distribusi Status Laporan
                </h3>
                <span class="text-xs font-semibold text-surface-500">Total: {{ stats.total }} Aduan</span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full h-3.5 bg-surface-100 dark:bg-surface-700 rounded-full overflow-hidden flex gap-0.5 p-0.5">
                <div
                    v-for="item in statusBreakdown"
                    :key="item.key"
                    :class="[item.color, 'h-full transition-all duration-500 rounded-full']"
                    :style="{ width: `${item.percent}%` }"
                    :title="`${item.label}: ${item.count} (${item.percent}%)`"
                ></div>
            </div>

            <!-- Legend Pills -->
            <div class="flex flex-wrap items-center justify-between gap-3 mt-4 text-xs font-medium">
                <div v-for="item in statusBreakdown" :key="item.key" class="flex items-center gap-2">
                    <span :class="['w-2.5 h-2.5 rounded-full', item.color]"></span>
                    <span class="text-surface-600 dark:text-surface-400">{{ item.label }}:</span>
                    <span :class="['font-extrabold', item.textColor]">{{ item.count }} ({{ item.percent }}%)</span>
                </div>
            </div>
        </div>

        <!-- Charts Row 1: Line Chart & Doughnut Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 px-4 sm:px-0">
            <!-- Trend Monthly Line Chart -->
            <div class="card p-5 lg:col-span-2 rounded-2xl flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-surface-800 dark:text-surface-100 tracking-tight">
                            Tren Laporan Bulanan
                        </h3>
                        <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                            Frekuensi aduan masyarakat 6 bulan terakhir
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold bg-surface-100 dark:bg-surface-700 text-surface-600 dark:text-surface-300 rounded-lg">
                        📈 6 Bulan
                    </span>
                </div>
                <div class="h-60 w-full">
                    <Line v-if="Object.keys(perBulan || {}).length" :data="perBulanData" :options="chartOptions" />
                    <div v-else class="flex items-center justify-center h-full text-surface-400 dark:text-surface-500 text-sm font-medium">
                        Belum ada data tren laporan
                    </div>
                </div>
            </div>

            <!-- Category Doughnut Chart -->
            <div class="card p-5 rounded-2xl flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-surface-800 dark:text-surface-100 tracking-tight">
                            Distribusi Kategori
                        </h3>
                        <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                            Persentase bidang aduan
                        </p>
                    </div>
                </div>
                <div class="h-60 relative flex items-center justify-center">
                    <Doughnut v-if="Object.keys(perKategori || {}).length" :data="perKategoriData" :options="doughnutOptions" />
                    <div v-else class="flex items-center justify-center h-full text-surface-400 dark:text-surface-500 text-sm font-medium">
                        Belum ada data kategori
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Top Categories Ranking & Subcategory Bar Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 px-4 sm:px-0">
            <!-- Top Kategori Progress Widget -->
            <div class="card p-5 rounded-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-surface-800 dark:text-surface-100 tracking-tight">
                            Kategori Paling Sering Dilaporkan
                        </h3>
                        <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                            Urutan peringkat bidang laporan tertinggi
                        </p>
                    </div>
                </div>

                <div v-if="topKategoriList.length" class="space-y-3.5">
                    <div v-for="(item, idx) in topKategoriList" :key="item.name" class="space-y-1">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-surface-800 dark:text-surface-200 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-md bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-300 flex items-center justify-center text-[10px] font-bold">
                                    #{{ idx + 1 }}
                                </span>
                                {{ item.name }}
                            </span>
                            <span class="text-surface-500 dark:text-surface-400 font-bold">
                                {{ item.count }} Laporan ({{ item.percent }}%)
                            </span>
                        </div>
                        <div class="w-full h-2 bg-surface-100 dark:bg-surface-700 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :style="{ width: `${item.percent}%`, backgroundColor: item.color }"
                            ></div>
                        </div>
                    </div>
                </div>
                <div v-else class="py-12 text-center text-surface-400 text-sm font-medium">
                    Belum ada data kategori
                </div>
            </div>

            <!-- Per Subkategori or Per Dusun Chart -->
            <div class="card p-5 rounded-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 v-if="!isKepalaDusun && Object.keys(perDusun || {}).length" class="text-sm font-bold text-surface-800 dark:text-surface-100 tracking-tight">
                            Sebaran Laporan per Dusun
                        </h3>
                        <h3 v-else class="text-sm font-bold text-surface-800 dark:text-surface-100 tracking-tight">
                            Laporan per Subkategori
                        </h3>
                        <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                            Rincian detail sub-bidang aduan
                        </p>
                    </div>
                </div>

                <div class="h-56">
                    <Bar v-if="!isKepalaDusun && Object.keys(perDusun || {}).length" :data="perDusunData" :options="chartOptions" />
                    <Bar v-else-if="Object.keys(perSubkategori || {}).length" :data="perSubkategoriData" :options="chartOptions" />
                    <div v-else class="flex items-center justify-center h-full text-surface-400 dark:text-surface-500 text-sm font-medium">
                        Belum ada data rincian subkategori
                    </div>
                </div>
            </div>
        </div>

        <!-- Laporan Terbaru Section -->
        <div class="card rounded-2xl overflow-hidden mx-4 sm:mx-0 mb-6">
            <div class="px-5 py-4 border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-surface-800 dark:text-surface-100 tracking-tight">
                        Laporan Terbaru Masuk
                    </h3>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                        5 daftar aduan paling akhir diterima
                    </p>
                </div>
                <Link
                    :href="route('laporan.index')"
                    class="text-xs text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-bold flex items-center gap-1 transition-colors px-3 py-1.5 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-950/40"
                >
                    <span>Lihat Semua</span>
                    <span>&rarr;</span>
                </Link>
            </div>

            <!-- Desktop Table -->
            <div class="hidden sm:block table-container border-0 rounded-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul Laporan</th>
                            <th>Wilayah Dusun</th>
                            <th>Kategori</th>
                            <th>Tanggal Masuk</th>
                            <th>Status Penanganan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in laporanTerbaru" :key="item.id">
                            <td class="font-semibold max-w-xs truncate">
                                <Link
                                    :href="route('laporan.show', item.id)"
                                    class="text-surface-900 hover:text-primary-700 dark:text-surface-100 dark:hover:text-primary-400 transition-colors"
                                >
                                    {{ item.judul }}
                                </Link>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-surface-700 dark:text-surface-300 bg-surface-100 dark:bg-surface-700/60 px-2.5 py-1 rounded-lg">
                                    📍 {{ item.dusun?.nama || '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary-800 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/60 border border-primary-100 dark:border-primary-900/60 px-2.5 py-1 rounded-lg">
                                    🏷️ {{ item.kategori?.nama || '-' }}
                                </span>
                            </td>
                            <td class="text-xs text-surface-500 font-medium">
                                {{ formatDate(item.tanggal) }}
                            </td>
                            <td>
                                <StatusBadge :status="item.status" />
                            </td>
                            <td class="text-right">
                                <Link
                                    :href="route('laporan.show', item.id)"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                                >
                                    Detail
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!laporanTerbaru?.length">
                            <td colspan="6" class="text-center text-surface-400 dark:text-surface-500 py-12 font-medium">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-10 h-10 text-surface-300 dark:text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <span>Belum ada laporan aduan yang tercatat</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List -->
            <div class="sm:hidden divide-y divide-surface-100 dark:divide-surface-700/60">
                <div v-if="!laporanTerbaru?.length" class="py-10 text-center text-surface-400 dark:text-surface-500 text-sm font-medium">
                    Belum ada laporan aduan tercatat
                </div>
                <Link
                    v-for="item in laporanTerbaru"
                    :key="item.id"
                    :href="route('laporan.show', item.id)"
                    class="flex flex-col gap-2.5 px-4 py-3.5 hover:bg-surface-50/80 dark:hover:bg-surface-700/40 active:bg-surface-100 dark:active:bg-surface-700/70 transition-all"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-bold text-sm text-surface-900 dark:text-surface-100 leading-snug">
                            {{ item.judul }}
                        </span>
                        <StatusBadge :status="item.status" />
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs font-medium text-surface-500 dark:text-surface-400">
                        <span v-if="item.dusun" class="flex items-center gap-1">
                            📍 {{ item.dusun.nama }}
                        </span>
                        <span v-if="item.kategori" class="flex items-center gap-1">
                            🏷️ {{ item.kategori.nama }}
                        </span>
                        <span class="ml-auto flex items-center gap-1 text-surface-400">
                            📅 {{ formatDate(item.tanggal) }}
                        </span>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

