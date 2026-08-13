<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

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
            backgroundColor: '#2d5a27', // Leaf Green
            borderRadius: 3,
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
            <h1 class="text-lg font-semibold text-surface-900">Dashboard</h1>
        </template>

        <!-- Greeting -->
        <div class="mb-6 px-4 sm:px-0 pt-4 sm:pt-0">
            <p class="text-surface-500 text-sm">
                Selamat datang, <span class="font-medium text-surface-700">{{ user?.name }}</span>
                <template v-if="isKepalaDusun && user?.dusun"> — {{ user.dusun.nama }}</template>
            </p>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 px-4 sm:px-0">
            <StatCard label="Total Laporan" :value="stats.total" icon="total" color="primary" />
            <StatCard label="Baru" :value="stats.baru" icon="baru" color="amber" />
            <StatCard label="Diproses" :value="stats.diproses" icon="diproses" color="blue" />
            <StatCard label="Selesai" :value="stats.selesai" icon="selesai" color="green" />
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-y-2 sm:gap-4 mb-6">
            <!-- Per Bulan -->
            <div class="card p-5 lg:col-span-2 rounded-none border-x-0 sm:rounded-sm sm:border-x">
                <h3 class="text-sm font-semibold text-surface-700 mb-4">Laporan per Bulan</h3>
                <div class="h-56">
                    <Bar v-if="Object.keys(perBulan || {}).length" :data="perBulanData" :options="chartOptions" />
                    <div v-else class="flex items-center justify-center h-full text-surface-400 text-sm">
                        Belum ada data laporan
                    </div>
                </div>
            </div>

            <!-- Per Kategori -->
            <div class="card p-5 rounded-none border-x-0 sm:rounded-sm sm:border-x">
                <h3 class="text-sm font-semibold text-surface-700 mb-4">Per Kategori</h3>
                <div class="h-56">
                    <Doughnut v-if="Object.keys(perKategori || {}).length" :data="perKategoriData" :options="doughnutOptions" />
                    <div v-else class="flex items-center justify-center h-full text-surface-400 text-sm">
                        Belum ada data
                    </div>
                </div>
            </div>
        </div>

        <!-- Per Dusun (admin/superadmin only) -->
        <div v-if="!isKepalaDusun && Object.keys(perDusun || {}).length" class="card p-5 mb-2 sm:mb-6 rounded-none border-x-0 sm:rounded-sm sm:border-x">
            <h3 class="text-sm font-semibold text-surface-700 mb-4">Laporan per Dusun</h3>
            <div class="h-48">
                <Bar :data="perDusunData" :options="chartOptions" />
            </div>
        </div>

        <!-- Per Subkategori -->
        <div v-if="Object.keys(perSubkategori || {}).length" class="card p-5 mb-2 sm:mb-6 rounded-none border-x-0 sm:rounded-sm sm:border-x">
            <h3 class="text-sm font-semibold text-surface-700 mb-4">Aduan Terbanyak per Subkategori</h3>
            <div class="h-48">
                <Bar :data="perSubkategoriData" :options="chartOptions" />
            </div>
        </div>

        <!-- Laporan Terbaru -->
        <div class="card rounded-none border-x-0 border-b-0 sm:rounded-sm sm:border-x sm:border-b">
            <div class="px-5 py-4 border-b border-surface-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-surface-700">Laporan Terbaru</h3>
                <Link :href="route('laporan.index')" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Lihat semua &rarr;
                </Link>
            </div>
            <div class="table-container">
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
                                <Link :href="route('laporan.show', item.id)" class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ item.judul }}
                                </Link>
                            </td>
                            <td>{{ item.dusun?.nama || '-' }}</td>
                            <td>{{ item.kategori?.nama || '-' }}</td>
                            <td>{{ formatDate(item.tanggal) }}</td>
                            <td><StatusBadge :status="item.status" /></td>
                        </tr>
                        <tr v-if="!laporanTerbaru?.length">
                            <td colspan="5" class="text-center text-surface-400 py-8">Belum ada laporan</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
