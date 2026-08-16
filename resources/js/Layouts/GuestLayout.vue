<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isDarkMode = ref(false);

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDarkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
});
</script>

<template>
    <div class="min-h-screen flex bg-surface-50 dark:bg-surface-900 transition-colors duration-200 relative">
        <!-- Floating Dark Mode Toggle (Top Right) -->
        <div class="absolute top-4 right-4 z-50">
            <button
                @click="toggleDarkMode"
                class="p-2.5 bg-white/80 dark:bg-surface-800/80 backdrop-blur border border-surface-200 dark:border-surface-700 text-surface-600 dark:text-surface-300 hover:text-surface-900 dark:hover:text-surface-100 rounded-full shadow-sm hover:shadow transition-all"
                title="Toggle Dark Mode"
            >
                <svg v-if="!isDarkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
            </button>
        </div>

        <!-- Left Side: Branding & Visuals (Desktop) -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-primary-950 via-primary-900 to-primary-800 overflow-hidden select-none">
            <!-- Decorative SVG Pattern -->
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px]"></div>

            <!-- Animated Decorative Blobs -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-700/50 rounded-full mix-blend-screen filter blur-3xl opacity-70 animate-blob"></div>
            <div class="absolute top-1/2 -right-24 w-96 h-96 bg-primary-600/40 rounded-full mix-blend-screen filter blur-3xl opacity-60 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-32 left-1/3 w-96 h-96 bg-primary-950/80 rounded-full mix-blend-multiply filter blur-3xl opacity-80 animate-blob animation-delay-4000"></div>
            
            <div class="relative z-10 w-full flex flex-col justify-between p-12 lg:p-16 text-white">
                <!-- Top Badge -->
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-primary-100 tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Aplikasi Pelayanan Desa Digital
                    </div>
                </div>

                <!-- Center Content -->
                <div class="my-auto py-8">
                    <div class="w-20 h-20 mb-8 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl flex items-center justify-center shadow-2xl shadow-primary-950/50">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 v5m-4 0h4" />
                        </svg>
                    </div>

                    <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight mb-4 text-white">
                        SAP<span class="text-primary-300">desa</span>
                    </h1>

                    <p class="text-primary-100 text-lg lg:text-xl font-normal leading-relaxed max-w-md">
                        Sistem Aspirasi & Pengaduan Warga Desa Sirnaputra
                    </p>

                    <div class="mt-6 flex items-center gap-2 text-primary-300 text-sm font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>Kec. Cigalontang, Kab. Tasikmalaya</span>
                    </div>
                </div>

                <!-- Bottom Features -->
                <div class="pt-8 border-t border-white/10 grid grid-cols-3 gap-4 text-xs text-primary-200">
                    <div class="flex flex-col gap-1">
                        <span class="font-bold text-white text-sm">Real-Time</span>
                        <span class="text-primary-300">Pantau Aspirasi</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="font-bold text-white text-sm">Transparan</span>
                        <span class="text-primary-300">Tindak Lanjut Dusun</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="font-bold text-white text-sm">Terintegrasi</span>
                        <span class="text-primary-300">Respon Cepat</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Form Content -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between px-6 py-12 sm:px-12 lg:px-20 min-h-screen">
            <div class="my-auto w-full max-w-md mx-auto">
                <!-- Mobile Logo Header -->
                <div class="lg:hidden flex flex-col items-center mb-8 text-center">
                    <div class="w-16 h-16 mb-3 bg-primary-900 dark:bg-primary-950 rounded-2xl flex items-center justify-center shadow-lg border border-primary-800">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 v5m-4 0h4" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-extrabold text-surface-900 dark:text-surface-50">
                        SAP<span class="text-primary-700 dark:text-primary-400">desa</span>
                    </h2>
                    <p class="text-xs text-surface-500 dark:text-surface-400 font-medium mt-1">
                        Desa Sirnaputra • Kec. Cigalontang
                    </p>
                </div>

                <!-- Form Card -->
                <div class="bg-white dark:bg-surface-800 p-8 sm:p-10 rounded-2xl shadow-xl shadow-surface-200/50 dark:shadow-black/40 border border-surface-200 dark:border-surface-700">
                    <slot />
                </div>
            </div>

            <!-- Footer Copy -->
            <div class="text-center text-xs text-surface-400 dark:text-surface-500 mt-8">
                &copy; {{ new Date().getFullYear() }} SAPdesa — Pemerintah Desa Sirnaputra
            </div>
        </div>
    </div>
</template>

<style>
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.08); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
  100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
  animation: blob 8s infinite ease-in-out;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
.animation-delay-4000 {
  animation-delay: 4s;
}
</style>
