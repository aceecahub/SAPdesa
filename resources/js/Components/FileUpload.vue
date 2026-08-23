<script setup>
import { ref, watch, onUnmounted, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Array, File, Object, null],
        default: () => []
    },
    existingUrls: {
        type: [Array, String, null],
        default: () => []
    },
    // Backward compatibility prop
    existingUrl: {
        type: String,
        default: null
    },
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
    },
    maxFiles: {
        type: Number,
        default: 4
    }
});

const emit = defineEmits(['update:modelValue', 'update:existingUrls', 'change', 'clear']);

const fileInputRef = ref(null);
const isDragging = ref(false);
const localError = ref(null);
const activeModalImage = ref(null);

// Blob URLs cache for new image files
const previewBlobMap = ref(new Map());

// Normalize existing URLs to array
const normalizedExistingUrls = computed(() => {
    let list = [];
    if (props.existingUrls) {
        list = Array.isArray(props.existingUrls) ? [...props.existingUrls] : [props.existingUrls];
    }
    if (props.existingUrl && !list.includes(props.existingUrl)) {
        list.push(props.existingUrl);
    }
    return list.filter(Boolean);
});

// Normalize new modelValue files to array of File objects
const normalizedNewFiles = computed(() => {
    if (!props.modelValue) return [];
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.filter(f => f instanceof File);
    }
    if (props.modelValue instanceof File) {
        return [props.modelValue];
    }
    return [];
});

const totalFileCount = computed(() => {
    return normalizedExistingUrls.value.length + normalizedNewFiles.value.length;
});

const isMaxReached = computed(() => {
    return totalFileCount.value >= props.maxFiles;
});

// Helper for formatting byte size
const formatFileSize = (bytes) => {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
};

// Check if a URL or filename is an image
const isImage = (filenameOrType) => {
    if (!filenameOrType) return false;
    if (typeof filenameOrType === 'string') {
        const ext = filenameOrType.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(ext);
    }
    return false;
};

// Get blob preview for new file
const getNewFilePreview = (file) => {
    if (!file || !file.type.startsWith('image/')) return null;
    if (!previewBlobMap.value.has(file)) {
        const url = URL.createObjectURL(file);
        previewBlobMap.value.set(file, url);
    }
    return previewBlobMap.value.get(file);
};

// Get storage URL for existing file path
const getExistingFileUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http') || path.startsWith('/')) return path;
    return `/storage/${path.replace(/^storage\//, '')}`;
};

// Process newly added files (from input or drop)
const addFiles = (filesToAdd) => {
    localError.value = null;
    if (!filesToAdd || filesToAdd.length === 0) return;

    const remainingSlots = props.maxFiles - totalFileCount.value;
    if (remainingSlots <= 0) {
        localError.value = `Maksimal ${props.maxFiles} berkas bukti telah tercapai.`;
        return;
    }

    const currentFiles = [...normalizedNewFiles.value];
    let addedCount = 0;
    let overflow = false;

    Array.from(filesToAdd).forEach(file => {
        if (!(file instanceof File)) return;

        // File size check
        if (file.size > props.maxSizeMB * 1024 * 1024) {
            localError.value = `Berkas "${file.name}" melebihi batas ukuran ${props.maxSizeMB}MB.`;
            return;
        }

        if (currentFiles.length + normalizedExistingUrls.value.length < props.maxFiles) {
            currentFiles.push(file);
            addedCount++;
        } else {
            overflow = true;
        }
    });

    if (overflow) {
        localError.value = `Hanya ${addedCount} berkas yang ditambahkan. Maksimal ${props.maxFiles} berkas bukti.`;
    }

    emit('update:modelValue', currentFiles);
    emit('change', currentFiles);
};

const onFileInputChange = (e) => {
    if (e.target.files && e.target.files.length > 0) {
        addFiles(e.target.files);
    }
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
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
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        addFiles(e.dataTransfer.files);
    }
};

const removeExistingFile = (index) => {
    const updated = [...normalizedExistingUrls.value];
    updated.splice(index, 1);
    emit('update:existingUrls', updated);
};

const removeNewFile = (index) => {
    const fileToRemove = normalizedNewFiles.value[index];
    if (fileToRemove && previewBlobMap.value.has(fileToRemove)) {
        URL.revokeObjectURL(previewBlobMap.value.get(fileToRemove));
        previewBlobMap.value.delete(fileToRemove);
    }

    const updated = [...normalizedNewFiles.value];
    updated.splice(index, 1);
    emit('update:modelValue', updated);

    if (updated.length === 0 && normalizedExistingUrls.value.length === 0) {
        emit('clear');
    }
};

const triggerFileSelect = () => {
    if (fileInputRef.value && !isMaxReached.value) {
        fileInputRef.value.click();
    }
};

const openLightbox = (url, name) => {
    activeModalImage.value = { url, name };
};

// Cleanup blob URLs on unmount
onUnmounted(() => {
    previewBlobMap.value.forEach(url => URL.revokeObjectURL(url));
    previewBlobMap.value.clear();
});

// Clean up unused blob URLs if modelValue changes
watch(() => props.modelValue, (newVal) => {
    if (!newVal || (Array.isArray(newVal) && newVal.length === 0)) {
        previewBlobMap.value.forEach(url => URL.revokeObjectURL(url));
        previewBlobMap.value.clear();
    }
});
</script>

<template>
    <div>
        <!-- Label and Counter -->
        <div class="flex items-center justify-between mb-1.5">
            <label v-if="label" class="label-text mb-0">{{ label }}</label>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 border border-surface-200 dark:border-surface-700">
                {{ totalFileCount }} / {{ maxFiles }} Berkas
            </span>
        </div>

        <!-- Hidden input supporting multiple selection -->
        <input
            ref="fileInputRef"
            type="file"
            multiple
            class="hidden"
            :accept="accept"
            @change="onFileInputChange"
        />

        <!-- LIST OF ATTACHED FILES (EXISTING + NEW) -->
        <div v-if="totalFileCount > 0" class="space-y-3 mb-3">
            <!-- Existing Files (retained from server) -->
            <div
                v-for="(urlPath, idx) in normalizedExistingUrls"
                :key="'exist-' + idx"
                class="p-3.5 card border border-surface-200 dark:border-surface-700 bg-surface-50/60 dark:bg-surface-800/60 rounded-2xl shadow-xs flex items-center justify-between gap-3"
            >
                <div class="flex items-center gap-3.5 min-w-0">
                    <!-- Thumbnail preview or file icon -->
                    <div v-if="isImage(urlPath)" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden bg-surface-900/10 border border-surface-200 dark:border-surface-700 shrink-0 relative group cursor-pointer" @click="openLightbox(getExistingFileUrl(urlPath), urlPath.split('/').pop())">
                        <img :src="getExistingFileUrl(urlPath)" alt="Preview Bukti" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>
                    <div v-else class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 flex items-center justify-center text-primary-600 dark:text-primary-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded bg-primary-500/10 text-primary-700 dark:text-primary-300 border border-primary-500/20">
                                Berkas Tersimpan
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-surface-900 dark:text-surface-100 truncate mt-0.5">
                            {{ urlPath.split('/').pop() }}
                        </p>
                        <p class="text-[11px] text-surface-500 dark:text-surface-400">
                            📎 Berkas terpilih saat ini dari laporan
                        </p>
                    </div>
                </div>

                <!-- Remove button -->
                <button
                    type="button"
                    @click="removeExistingFile(idx)"
                    class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/50 transition-colors cursor-pointer shrink-0"
                    title="Hapus berkas ini"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            </div>

            <!-- Newly selected Files -->
            <div
                v-for="(file, idx) in normalizedNewFiles"
                :key="'new-' + idx"
                class="p-3.5 card border border-surface-200 dark:border-surface-700 bg-surface-50/60 dark:bg-surface-800/60 rounded-2xl shadow-xs flex items-center justify-between gap-3"
            >
                <div class="flex items-center gap-3.5 min-w-0">
                    <!-- Thumbnail preview or file icon -->
                    <div v-if="file.type.startsWith('image/')" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden bg-surface-900/10 border border-surface-200 dark:border-surface-700 shrink-0 relative group cursor-pointer" @click="openLightbox(getNewFilePreview(file), file.name)">
                        <img :src="getNewFilePreview(file)" alt="Preview Bukti" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>
                    <div v-else class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                Barubaru ini dipilih
                            </span>
                            <span class="text-xs text-surface-500 dark:text-surface-400 font-medium">
                                • {{ formatFileSize(file.size) }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-surface-900 dark:text-surface-100 truncate mt-0.5">
                            {{ file.name }}
                        </p>
                        <p class="text-[11px] text-surface-500 dark:text-surface-400">
                            ✓ Berkas siap diunggah
                        </p>
                    </div>
                </div>

                <!-- Remove button -->
                <button
                    type="button"
                    @click="removeNewFile(idx)"
                    class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/50 transition-colors cursor-pointer shrink-0"
                    title="Hapus berkas ini"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- DROPZONE / ADD BUTTON (WHEN NOT MAXED OUT) -->
        <div
            v-if="!isMaxReached"
            @click="triggerFileSelect"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            :class="[
                'flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-200',
                isDragging
                    ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-950/30 scale-[1.01]'
                    : 'border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-900/30 hover:border-primary-500/50 hover:bg-surface-100/50 dark:hover:bg-surface-800/40'
            ]"
        >
            <div class="space-y-2 text-center pointer-events-none">
                <div class="w-12 h-12 mx-auto rounded-full bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 flex items-center justify-center text-primary-600 dark:text-primary-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-primary-700 dark:text-primary-400 hover:underline">
                        {{ totalFileCount > 0 ? '+ Tambah foto / berkas bukti lagi' : 'Unggah foto / berkas bukti' }}
                    </span>
                    <span class="text-xs text-surface-600 dark:text-surface-400 pl-1">
                        atau tarik dan lepas di sini
                    </span>
                </div>
                <p class="text-[11px] text-surface-400 dark:text-surface-500">
                    Dapat memilih lebih dari 1 berkas sekaligus (Maksimal {{ maxFiles }} berkas, @ {{ maxSizeMB }}MB)
                </p>
            </div>
        </div>

        <div v-else class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-center text-xs font-semibold text-emerald-700 dark:text-emerald-400">
            ✓ Kuota maksimal {{ maxFiles }} berkas bukti sudah terpenuhi. Hapus salah satu berkas di atas jika ingin mengganti.
        </div>

        <!-- ERROR MESSAGES -->
        <p v-if="localError || error" class="text-xs font-semibold text-rose-600 dark:text-rose-400 mt-1.5">
            {{ localError || error }}
        </p>

        <!-- LIGHTBOX MODAL -->
        <Teleport to="body">
            <div v-if="activeModalImage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs" @click.self="activeModalImage = null">
                <div class="relative max-w-4xl max-h-[90vh] bg-surface-900 rounded-2xl overflow-hidden shadow-2xl border border-surface-700">
                    <button
                        type="button"
                        @click="activeModalImage = null"
                        class="absolute top-3 right-3 z-10 p-2 rounded-full bg-black/60 hover:bg-black text-white transition-colors cursor-pointer"
                        title="Tutup"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <img :src="activeModalImage.url" :alt="activeModalImage.name" class="max-w-full max-h-[85vh] object-contain mx-auto p-2" />
                    <div class="p-3 bg-surface-900 border-t border-surface-800 text-center text-xs text-surface-300">
                        {{ activeModalImage.name }}
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
