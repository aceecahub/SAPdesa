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
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="open" class="fixed inset-0 z-[90] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="cancel" />
            <div class="relative bg-white rounded-sm shadow-md border border-surface-200 w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-surface-900 mb-2">{{ title }}</h3>
                <p class="text-sm text-surface-600 mb-6">{{ message }}</p>
                <div class="flex items-center justify-end gap-3">
                    <button @click="cancel" class="btn-secondary">{{ cancelLabel }}</button>
                    <button
                        @click="confirm"
                        :class="variant === 'danger' ? 'btn-danger' : 'btn-primary'"
                    >
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
