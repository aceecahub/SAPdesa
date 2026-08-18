<script setup>
import { ref, watch, onUnmounted, computed } from 'vue';

const props = defineProps({
    modelValue: [File, Object, null],
    existingUrl: String,
    label: {
        type: String,
        default: 'Lampiran Foto / Berkas Pendukung (Opsional)'
    },
    error: String,
    accept: {
        type: String,
        default: '.jpg,.jpeg,.png,.webp,.pdf,.doc,.docx'
    },
    maxSizeMB: {
        type: Number,
        default: 5
    }
});

const emit = defineEmits(['update:modelValue', 'change', 'clear']);

const fileInputRef = ref(null);
const previewUrl = ref(null);
const isDragging = ref(false);
const showModal = ref(false);

const isImageFile = computed(() => {
    if (props.modelValue && props.modelValue instanceof File) {
        return props.modelValue.type.startsWith('image/');
    }
    if (props.existingUrl) {
        const ext = props.existingUrl.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(ext);
    }
    return false;
});

const fileName = computed(() => {
    if (props.modelValue && props.modelValue instanceof File) {
        return props.modelValue.name;
    }
    if (props.existingUrl) {
        return props.existingUrl.split('/').pop();
    }
    return '';
});

const fileSizeFormatted = computed(() => {
    if (props.modelValue && props.modelValue instanceof File && props.modelValue.size) {
        const bytes = props.modelValue.size;
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }
    return '';
});

const activePreviewUrl = computed(() => {
    if (previewUrl.value) return previewUrl.value;
    if (props.existingUrl) {
        return props.existingUrl.startsWith('http') || props.existingUrl.startsWith('/')
            ? props.existingUrl
            : `/storage/${props.existingUrl.replace(/^storage\//, '')}`;
    }
    return null;
});

const handleFileSelect = (file) => {
    if (!file) return;

    // Clean up previous blob URL
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }

    if (file.type.startsWith('image/')) {
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewUrl.value = null;
    }

    emit('update:modelValue', file);
    emit('change', file);
};

const onFileInputChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        handleFileSelect(file);
    }
};

const handleDragOver = (e) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file) {
        handleFileSelect(file);
    }
};

const clearFile = () => {
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
    emit('update:modelValue', null);
    emit('clear');
};

const triggerFileSelect = () => {
    if (fileInputRef.value) {
        fileInputRef.value.click();
    }
};

watch(() => props.modelValue, (newVal) => {
    if (!newVal) {
        if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
            URL.revokeObjectURL(previewUrl.value);
        }
        previewUrl.value = null;
        if (fileInputRef.value) {
            fileInputRef.value.value = '';
        }
    }
});

onUnmounted(() => {
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<template>
    <div>
        <label v-if="label" class="label-text">{{ label }}</label>

        <!-- Hidden input -->
        <input
            ref="fileInputRef"
            type="file"
            class="hidden"
            :accept="accept"
            @change="onFileInputChange"
        />

        <!-- IF FILE SELECTED OR EXISTING FILE -->
        <div v-if="modelValue || existingUrl" class="mt-1 card p-4 border border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-800/60 rounded-2xl shadow-xs">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                
                <!-- Image Preview Thumbnail -->
                <div v-if="isImageFile && activePreviewUrl" class="relative group shrink-0">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden bg-surface-900/10 border border-surface-200 dark:border-surface-700 shadow-xs relative">
                        <img
                            :src="activePreviewUrl"
                            alt="Preview Lampiran"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer"
                            @click="showModal = true"
                        />
                        <button
                            type="button"
                            @click="showModal = true"
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200"
                            title="Lihat Foto Berukuran Besar"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Non-Image File Icon -->
                <div v-else class="w-20 h-20 rounded-xl bg-primary-50 dark:bg-primary-950/50 border border-primary-200 dark:border-primary-800/60 flex items-center justify-center text-primary-700 dark:text-primary-400 shrink-0">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>

                <!-- File Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            {{ isImageFile ? 'Foto / Gambar' : 'Dokumen / Berkas' }}
                        </span>
                        <span v-if="fileSizeFormatted" class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                            • {{ fileSizeFormatted }}
                        </span>
                    </div>

                    <p class="text-sm font-bold text-surface-900 dark:text-surface-100 truncate mt-1">
                        {{ fileName }}
                    </p>

                    <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">
                        <span v-if="modelValue">✓ Berkas terpilih dan siap diunggah</span>
                        <span v-else-if="existingUrl">📎 Berkas terpilih saat ini dari laporan</span>
                    </p>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <button
                            type="button"
                            @click="triggerFileSelect"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-200/80 hover:bg-surface-300 dark:bg-surface-700 dark:hover:bg-surface-600 text-surface-700 dark:text-surface-200 transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Ganti Berkas
                        </button>

                        <button
                            type="button"
                            @click="clearFile"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50 transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Hapus / Bersihkan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DROPZONE WHEN NO FILE IS SELECTED -->
        <div
            v-else
            @click="triggerFileSelect"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            :class="[
                'mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-200',
                isDragging
                    ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-950/30 scale-[1.01]'
                    : 'border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-900/30 hover:border-primary-500/50 hover:bg-surface-100/50 dark:hover:bg-surface-800/40'
            ]"
        >
            <div class="space-y-2 text-center pointer-events-none">
                <div class="w-12 h-12 mx-auto rounded-full bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 flex items-center justify-center text-primary-600 dark:text-primary-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-primary-700 dark:text-primary-400 hover:underline">
                        Unggah foto / berkas
                    </span>
                    <span class="text-xs text-surface-600 dark:text-surface-400 pl-1">
                        atau tarik dan lepas di sini
                    </span>
                </div>
                <p class="text-[11px] text-surface-400 dark:text-surface-500">Format: JPG, PNG, WEBP, PDF, DOC (Maks: 5MB)</p>
            </div>
        </div>

        <p v-if="error" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
            {{ error }}
        </p>

        <!-- Image Modal Lightbox -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs" @click.self="showModal = false">
                <div class="relative max-w-4xl max-h-[90vh] bg-surface-900 rounded-2xl overflow-hidden shadow-2xl border border-surface-700">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="absolute top-3 right-3 z-10 p-2 rounded-full bg-black/60 hover:bg-black text-white transition-colors"
                        title="Tutup"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <img :src="activePreviewUrl" alt="Full Preview" class="max-w-full max-h-[85vh] object-contain mx-auto p-2" />
                    <div class="p-3 bg-surface-900 border-t border-surface-800 text-center text-xs text-surface-300">
                        {{ fileName }} <span v-if="fileSizeFormatted">({{ fileSizeFormatted }})</span>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
