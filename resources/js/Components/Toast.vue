<script setup>
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
let timeout = null;

const flash = computed(() => page.props.flash);

const show = (msg, t = 'success') => {
    message.value = msg;
    type.value = t;
    visible.value = true;
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        visible.value = false;
    }, 4000);
};

watch(
    () => flash.value,
    (val) => {
        if (val?.success) show(val.success, 'success');
        else if (val?.error) show(val.error, 'error');
        else if (val?.info) show(val.info, 'info');
    },
    { immediate: true, deep: true }
);

const dismiss = () => {
    visible.value = false;
    if (timeout) clearTimeout(timeout);
};

const bgClass = computed(() => {
    const map = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-blue-600',
    };
    return map[type.value] || 'bg-surface-700';
});

const iconPath = computed(() => {
    const map = {
        success: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        error: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
        info: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
    };
    return map[type.value] || map.info;
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="visible"
            :class="['fixed bottom-5 right-5 z-[100] max-w-sm w-full px-4 py-3 rounded shadow-lg text-white flex items-start gap-3', bgClass]"
        >
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath" />
            </svg>
            <p class="text-sm flex-1">{{ message }}</p>
            <button @click="dismiss" class="shrink-0 text-white/80 hover:text-white">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </Transition>
</template>
