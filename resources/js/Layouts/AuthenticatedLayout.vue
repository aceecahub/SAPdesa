<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
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

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Close sidebar on route change
watch(() => page.url, () => {
    closeSidebar();
});

const navItems = computed(() => {
    const items = [
        {
            label: 'Dashboard',
            href: route('dashboard'),
            icon: 'dashboard',
            active: route().current('dashboard'),
            roles: ['superadmin', 'admin', 'kepala_dusun'],
        },
        {
            label: 'Laporan',
            href: route('laporan.index'),
            icon: 'laporan',
            active: route().current('laporan.*'),
            roles: ['superadmin', 'admin', 'kepala_dusun'],
        },
    ];

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

    if (['superadmin', 'admin'].includes(role.value)) {
        items.push({
            label: 'Export Data',
            href: route('export.index'),
            icon: 'export',
            active: route().current('export.*'),
            roles: ['superadmin', 'admin'],
        });
    }

    return items;
});

const roleBadgeClass = computed(() => {
    const map = {
        superadmin: 'bg-red-100 text-red-800 border border-red-200',
        admin: 'bg-sage text-primary-900 border border-primary-200',
        kepala_dusun: 'bg-amber-100 text-amber-800 border border-amber-200',
    };
    return map[role.value] || 'bg-surface-100 text-surface-800 border border-surface-200';
});

const roleLabel = computed(() => {
    const map = {
        superadmin: 'Superadmin',
        admin: 'Admin Desa',
        kepala_dusun: 'Kepala Dusun',
    };
    return map[role.value] || role.value;
});
</script>

<template>
    <div class="min-h-screen bg-surface-50">
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
                class="fixed inset-0 z-40 bg-black/40 lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-surface-200 flex flex-col transition-transform duration-200 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center gap-3 px-5 border-b border-surface-200 shrink-0 bg-primary-900">
                <div class="w-8 h-8 bg-white/10 border border-white/20 rounded-sm flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-white tracking-tight">SAPdesa</div>
                    <div class="text-[11px] text-primary-200 leading-none">Desa Sirnaputra</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-sm transition-colors duration-100',
                        item.active
                            ? 'bg-sage text-primary-900 border border-primary-200'
                            : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900',
                    ]"
                >
                    <!-- Dashboard -->
                    <svg v-if="item.icon === 'dashboard'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <!-- Laporan -->
                    <svg v-if="item.icon === 'laporan'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <!-- Dusun -->
                    <svg v-if="item.icon === 'dusun'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                    </svg>
                    <!-- Kategori -->
                    <svg v-if="item.icon === 'kategori'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    <!-- User -->
                    <svg v-if="item.icon === 'user'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <!-- Export -->
                    <svg v-if="item.icon === 'export'" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User info at bottom of sidebar -->
            <div class="border-t border-surface-200 px-4 py-3 shrink-0 bg-surface-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-surface-200 border border-surface-300 rounded-sm flex items-center justify-center text-sm font-semibold text-surface-700">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-surface-900 truncate">{{ user?.name }}</div>
                        <span :class="['inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded leading-none mt-0.5', roleBadgeClass]">
                            {{ roleLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="lg:pl-64 min-h-screen flex flex-col">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-surface-200 flex items-center justify-between px-4 sm:px-6 shrink-0 sticky top-0 z-30">
                <!-- Mobile hamburger (Hidden in favor of bottom nav) -->
                <div class="lg:hidden w-8"></div>

                <!-- Page title slot -->
                <div class="flex-1 min-w-0 lg:ml-0 ml-3">
                    <slot name="header" />
                </div>

                <!-- Right side: user dropdown -->
                <div id="user-dropdown-container" class="relative">
                    <button
                        @click="toggleUserDropdown"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm text-surface-600 hover:text-surface-900 hover:bg-surface-50 rounded transition-colors"
                    >
                        <span class="hidden sm:inline">{{ user?.name }}</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                            class="absolute right-0 mt-1 w-48 bg-white border border-surface-300 rounded-sm py-1 z-50"
                        >
                            <Link
                                :href="route('profile.edit')"
                                class="block px-4 py-2 text-sm text-surface-700 hover:bg-surface-50 transition-colors"
                                @click="closeUserDropdown"
                            >
                                Profil
                            </Link>
                            <div class="border-t border-surface-100 my-1"></div>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                @click="closeUserDropdown"
                            >
                                Keluar
                            </Link>
                        </div>
                    </Transition>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-0 sm:p-6 pb-20 lg:pb-6">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-surface-200 flex items-center justify-around z-40 pb-[env(safe-area-inset-bottom)] shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
            <Link
                :href="route('dashboard')"
                :class="[
                    'flex flex-col items-center justify-center w-full py-2.5 gap-1 text-[10px] font-medium transition-colors',
                    route().current('dashboard') ? 'text-primary-700' : 'text-surface-500 active:text-surface-900'
                ]"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                Beranda
            </Link>
            <Link
                :href="route('laporan.index')"
                :class="[
                    'flex flex-col items-center justify-center w-full py-2.5 gap-1 text-[10px] font-medium transition-colors',
                    route().current('laporan.*') ? 'text-primary-700' : 'text-surface-500 active:text-surface-900'
                ]"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Laporan
            </Link>
            <button
                @click="toggleSidebar"
                class="flex flex-col items-center justify-center w-full py-2.5 gap-1 text-[10px] font-medium text-surface-500 active:text-surface-900 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                Menu
            </button>
        </div>

        <!-- Toast -->
        <Toast />
    </div>
</template>
