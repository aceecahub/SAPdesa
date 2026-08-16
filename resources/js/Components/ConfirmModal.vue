<script setup>
import { ref } from 'vue';

const props = defineProps({
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, default: 'Apakah Anda yakin ingin melakukan tindakan ini?' },
    confirmLabel: { type: String, default: 'Ya, Hapus' },
    cancelLabel: { type: String, default: 'Batal' },
    variant: { type: String, default: 'danger' },
});

const emit = defineEmits(['confirm', 'cancel']);

const open = ref(false);

const show = () => { open.value = true; };
const hide = () => { open.value = false; };

const confirm = () => {
    emit('confirm');
    hide();
};

const cancel = () => {
    emit('cancel');
    hide();
};

defineExpose({ show, hide });
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="open" class="fixed inset-0 z-[90] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs dark:bg-black/80" @click="cancel" />
            <div class="relative bg-white dark:bg-surface-800 rounded-2xl shadow-2xl border border-surface-200 dark:border-surface-700 w-full max-w-md p-6 overflow-hidden">
                <div class="flex items-center gap-3 mb-3">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', variant === 'danger' ? 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400' : 'bg-primary-50 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400']">
                        <svg v-if="variant === 'danger'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-surface-50">{{ title }}</h3>
                </div>
                
                <p class="text-sm text-surface-600 dark:text-surface-400 mb-6 leading-relaxed">{{ message }}</p>

                <div class="flex items-center justify-end gap-2.5">
                    <button @click="cancel" class="btn-secondary text-xs px-4 py-2">{{ cancelLabel }}</button>
                    <button
                        @click="confirm"
                        :class="['text-xs px-4 py-2', variant === 'danger' ? 'btn-danger' : 'btn-primary']"
                    >
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
