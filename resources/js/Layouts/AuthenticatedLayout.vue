<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.role);

const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const toggleUserDropdown = () => {
    userDropdownOpen.value = !userDropdownOpen.value;
};

const closeUserDropdown = () => {
    userDropdownOpen.value = false;
};

// Close dropdown on outside click
const handleClickOutside = (e) => {
    if (userDropdownOpen.value && !e.target.closest('#user-dropdown-container')) {
        closeUserDropdown();
    }
};

// Close sidebar on route change
watch(() => page.url, () => {
    closeSidebar();
});

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
    document.addEventListener('click', handleClickOutside);
    
    // Check initial theme
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDarkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const navItems = computed(() => {
    const items = [
        {
            label: 'Dashboard',
            href: route('dashboard'),
            icon: 'dashboard',
            active: route().current('dashboard'),
            roles: ['superadmin', 'admin', 'kepala_dusun', 'ketua_rw'],
        },
        {
            label: 'Laporan Aspirasi',
            href: route('laporan.index'),
            icon: 'laporan',
            active: route().current('laporan.*'),
            roles: ['superadmin', 'admin', 'kepala_dusun', 'ketua_rw'],
        },
    ];

    if (['superadmin', 'admin', 'kepala_dusun'].includes(role.value)) {
        items.push({
            label: 'Data RW',
            href: route('master.rw.index'),
            icon: 'rw',
            active: route().current('master.rw.*'),
            roles: ['superadmin', 'admin', 'kepala_dusun'],
        });
    }

    if (['superadmin', 'admin'].includes(role.value)) {
        items.push(
            {
                label: 'Data Dusun',
                href: route('master.dusun.index'),
                icon: 'dusun',
                active: route().current('master.dusun.*'),
                roles: ['superadmin', 'admin'],
            },
            {
                label: 'Data Kategori',
                href: route('master.kategori.index'),
                icon: 'kategori',
                active: route().current('master.kategori.*'),
                roles: ['superadmin', 'admin'],
            },
        );
    }

    if (role.value === 'superadmin') {
        items.push({
            label: 'Kelola User',
            href: route('user.index'),
            icon: 'user',
            active: route().current('user.*'),
            roles: ['superadmin'],
        });
    }

    if (['superadmin', 'admin', 'kepala_dusun'].includes(role.value)) {
        items.push({
            label: 'Export Data',
            href: route('export.index'),
            icon: 'export',
            active: route().current('export.*'),
            roles: ['superadmin', 'admin', 'kepala_dusun'],
        });
    }

    return items;
});

const roleBadgeClass = computed(() => {
    const map = {
        superadmin: 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
        admin: 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
        kepala_dusun: 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
        ketua_rw: 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
    };
    return map[role.value] || 'bg-surface-100 text-surface-700 border border-surface-200 dark:bg-surface-800 dark:text-surface-300 dark:border-surface-700';
});

const roleLabel = computed(() => {
    const map = {
        superadmin: 'Superadmin',
        admin: 'Admin Desa',
        kepala_dusun: 'Kepala Dusun',
        ketua_rw: 'Ketua RW',
    };
    return map[role.value] || role.value;
});
</script>

<template>
    <div class="min-h-screen bg-surface-50 dark:bg-surface-900 transition-colors duration-200">
        <!-- Mobile sidebar backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-surface-800 border-r border-surface-200/80 dark:border-surface-700/80 flex flex-col transition-transform duration-200 lg:translate-x-0 shadow-lg lg:shadow-none',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo Header -->
            <div class="h-16 flex items-center gap-3 px-5 border-b border-surface-200/80 dark:border-surface-700/80 shrink-0 bg-primary-900">
                <div class="w-9 h-9 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <div class="text-base font-extrabold text-white tracking-tight">SAP<span class="text-primary-300">desa</span></div>
                    <div class="text-[11px] text-primary-200 font-medium leading-none">Desa Sirnaputra</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-150',
                        item.active
                            ? 'bg-primary-50/90 text-primary-800 dark:bg-primary-950/60 dark:text-primary-300 dark:border dark:border-primary-900/60 shadow-xs'
                            : 'text-surface-600 hover:bg-surface-100/80 hover:text-surface-900 dark:text-surface-400 dark:hover:bg-surface-700/70 dark:hover:text-surface-100',
                    ]"
                >
                    <!-- Dashboard -->
                    <svg v-if="item.icon === 'dashboard'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <!-- Laporan -->
                    <svg v-if="item.icon === 'laporan'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <!-- Dusun -->
                    <svg v-if="item.icon === 'dusun'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                    </svg>
                    <!-- Kategori -->
                    <svg v-if="item.icon === 'kategori'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    <!-- User -->
                    <svg v-if="item.icon === 'user'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <!-- Export -->
                    <svg v-if="item.icon === 'export'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <!-- User info at bottom of sidebar -->
            <div class="border-t border-surface-200/80 dark:border-surface-700/80 px-4 py-3.5 shrink-0 bg-surface-50/50 dark:bg-surface-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-primary-100 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 rounded-xl flex items-center justify-center text-sm font-extrabold text-primary-800 dark:text-primary-300 shadow-xs">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-surface-900 dark:text-surface-100 truncate">{{ user?.name }}</div>
                        <span :class="['inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full leading-none mt-1', roleBadgeClass]">
                            {{ roleLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="lg:pl-64 min-h-screen flex flex-col">
            <!-- Glassmorphism Topbar -->
            <header class="min-h-[4rem] bg-white/80 dark:bg-surface-800/80 backdrop-blur-md border-b border-surface-200/80 dark:border-surface-700/80 flex items-center justify-between px-4 sm:px-6 shrink-0 sticky top-0 z-30 transition-colors duration-200 py-2">
                <!-- Mobile empty spacer -->
                <div class="lg:hidden w-2"></div>

                <!-- Page title slot -->
                <div class="flex-1 min-w-0 lg:ml-0 ml-1 pr-2 overflow-hidden">
                    <slot name="header" />
                </div>

                <div class="flex items-center gap-3">
                    <!-- Dark Mode Toggle -->
                    <button
                        @click="toggleDarkMode"
                        class="p-2 text-surface-500 hover:text-surface-900 dark:text-surface-400 dark:hover:text-surface-100 rounded-xl hover:bg-surface-100 dark:hover:bg-surface-700 transition-all border border-transparent hover:border-surface-200 dark:hover:border-surface-600"
                        title="Toggle Dark Mode"
                    >
                        <svg v-if="!isDarkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    </button>

                    <!-- Right side: user dropdown -->
                    <div id="user-dropdown-container" class="relative">
                        <button
                            @click="toggleUserDropdown"
                            class="flex items-center gap-2 px-3 py-1.5 text-sm font-semibold text-surface-700 hover:text-surface-900 dark:text-surface-200 dark:hover:text-surface-50 hover:bg-surface-100 dark:hover:bg-surface-700/80 rounded-xl transition-all border border-transparent hover:border-surface-200 dark:hover:border-surface-600"
                        >
                            <span class="hidden sm:inline">{{ user?.name }}</span>
                            <svg class="w-4 h-4 text-surface-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <Transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div
                                v-if="userDropdownOpen"
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-2xl py-1.5 z-50 shadow-xl"
                            >
                                <Link
                                    :href="route('profile.edit')"
                                    class="block px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors"
                                    @click="closeUserDropdown"
                                >
                                    Profil Saya
                                </Link>
                                <div class="border-t border-surface-100 dark:border-surface-700/60 my-1"></div>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="block w-full text-left px-4 py-2 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                                    @click="closeUserDropdown"
                                >
                                    Keluar Sistem
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 pb-24 lg:pb-6">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Glass Navigation -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/90 dark:bg-surface-800/90 backdrop-blur-md border-t border-surface-200/80 dark:border-surface-700/80 flex items-center justify-around z-40 pb-[env(safe-area-inset-bottom)] shadow-lg">
            <Link
                :href="route('dashboard')"
                :class="[
                    'flex flex-col items-center justify-center w-full py-2.5 gap-1 text-[10px] font-bold transition-all',
                    route().current('dashboard') ? 'text-primary-700 dark:text-primary-400' : 'text-surface-500 dark:text-surface-400 active:text-surface-900 dark:active:text-surface-100'
                ]"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                Beranda
            </Link>
            <Link
                :href="route('laporan.index')"
                :class="[
                    'flex flex-col items-center justify-center w-full py-2.5 gap-1 text-[10px] font-bold transition-all',
                    route().current('laporan.*') ? 'text-primary-700 dark:text-primary-400' : 'text-surface-500 dark:text-surface-400 active:text-surface-900 dark:active:text-surface-100'
                ]"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Laporan
            </Link>
            <button
                @click="toggleSidebar"
                class="flex flex-col items-center justify-center w-full py-2.5 gap-1 text-[10px] font-bold text-surface-500 dark:text-surface-400 active:text-surface-900 dark:active:text-surface-100 transition-all"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                Menu
            </button>
        </div>

        <!-- Toast -->
        <Toast />
    </div>
</template>
