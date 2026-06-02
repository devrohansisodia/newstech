<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    fieldName: {
        type: String,
        required: true,
    },
    fieldId: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    emptyState: {
        type: String,
        required: true,
    },
    previewLabel: {
        type: String,
        required: true,
    },
    currentPath: {
        type: String,
        default: '',
    },
    currentUrl: {
        type: String,
        default: '',
    },
    uploadEndpoint: {
        type: String,
        required: true,
    },
    csrfToken: {
        type: String,
        required: true,
    },
    mediaItems: {
        type: Array,
        default: () => [],
    },
});

const items = ref(props.mediaItems.map((item) => ({ ...item })));
const isOpen = ref(false);
const activeTab = ref('media');
const selectedPath = ref(props.currentPath);
const selectedUrl = ref(props.currentUrl);
const uploadFiles = ref([]);
const uploadAltText = ref('');
const uploadCaption = ref('');
const uploadStatus = ref('');
const detailsStatus = ref('');
const selectedMediaId = ref(resolveInitialSelectedMediaId());

const selectedMedia = computed(() => items.value.find((item) => item.id === selectedMediaId.value) ?? null);
const hasSelection = computed(() => selectedPath.value.trim() !== '' && selectedUrl.value.trim() !== '');
const hiddenInput = ref(null);

function lockModalScroll() {
    document.documentElement.classList.add('nt-admin-modal-open');
    document.body.classList.add('nt-admin-modal-open');
}

function unlockModalScroll() {
    document.documentElement.classList.remove('nt-admin-modal-open');
    document.body.classList.remove('nt-admin-modal-open');
}

function resolveInitialSelectedMediaId() {
    const matchedMedia = props.mediaItems.find((item) => item.path === props.currentPath);

    return matchedMedia?.id ?? props.mediaItems[0]?.id ?? null;
}

function openModal() {
    isOpen.value = true;
    activeTab.value = 'media';
    uploadStatus.value = '';
    detailsStatus.value = '';
    lockModalScroll();
}

function closeModal() {
    isOpen.value = false;
    activeTab.value = 'media';
    uploadStatus.value = '';
    detailsStatus.value = '';
    uploadFiles.value = [];
    unlockModalScroll();
}

function clearSelection() {
    selectedPath.value = '';
    selectedUrl.value = '';
}

function setActiveTab(tab) {
    activeTab.value = tab;
}

function selectMediaItem(mediaId) {
    selectedMediaId.value = mediaId;
    detailsStatus.value = '';
}

function handleFileChange(event) {
    const input = event.target;

    uploadFiles.value = input instanceof HTMLInputElement
        ? Array.from(input.files ?? [])
        : [];
}

async function uploadMedia() {
    if (uploadFiles.value.length === 0) {
        uploadStatus.value = 'Choose at least one image to upload.';

        return;
    }

    const formData = new FormData();

    uploadFiles.value.forEach((file) => {
        formData.append('files[]', file);
    });

    formData.append('_token', props.csrfToken);
    formData.append('alt_text', uploadAltText.value);
    formData.append('caption', uploadCaption.value);

    uploadStatus.value = '';

    const response = await fetch(props.uploadEndpoint, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': props.csrfToken,
        },
        body: formData,
        credentials: 'same-origin',
    });

    if (! response.ok) {
        uploadStatus.value = 'Upload failed. Check the selected files and try again.';

        return;
    }

    const payload = await response.json();
    const uploadedItems = Array.isArray(payload.media_items) ? payload.media_items : [];

    items.value = [...uploadedItems.reverse(), ...items.value];
    selectedMediaId.value = uploadedItems[0]?.id ?? selectedMediaId.value;
    uploadFiles.value = [];
    uploadAltText.value = '';
    uploadCaption.value = '';
    uploadStatus.value = 'Media uploaded. Choose an image from the Media tab.';
    activeTab.value = 'media';
}

async function updateSelectedMedia() {
    if (! selectedMedia.value?.update_url) {
        return;
    }

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('_token', props.csrfToken);
    formData.append('alt_text', selectedMedia.value.alt_text ?? '');
    formData.append('caption', selectedMedia.value.caption ?? '');

    detailsStatus.value = '';

    const response = await fetch(selectedMedia.value.update_url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            'X-CSRF-TOKEN': props.csrfToken,
        },
        body: formData,
        credentials: 'same-origin',
    });

    if (! response.ok) {
        detailsStatus.value = 'Could not save media details. Try again.';

        return;
    }

    const payload = await response.json();
    const updatedMedia = payload.media ?? null;

    if (! updatedMedia) {
        detailsStatus.value = 'Could not save media details. Try again.';

        return;
    }

    items.value = items.value.map((item) => item.id === updatedMedia.id ? updatedMedia : item);
    selectedMediaId.value = updatedMedia.id;

    if (selectedPath.value === updatedMedia.path) {
        selectedUrl.value = updatedMedia.url ?? selectedUrl.value;
    }

    detailsStatus.value = payload.message || 'Media details updated.';
}

function applySelectedMedia() {
    if (! selectedMedia.value) {
        return;
    }

    selectedPath.value = selectedMedia.value.path ?? '';
    selectedUrl.value = selectedMedia.value.url ?? '';
    closeModal();
}

watch(selectedPath, async () => {
    await nextTick();

    if (!(hiddenInput.value instanceof HTMLInputElement)) {
        return;
    }

    hiddenInput.value.dispatchEvent(new Event('input', { bubbles: true }));
    hiddenInput.value.dispatchEvent(new Event('change', { bubbles: true }));
});

onBeforeUnmount(() => {
    unlockModalScroll();
});
</script>

<template>
    <div class="space-y-4" data-media-picker data-media-picker-vue="true">
        <input
            ref="hiddenInput"
            :id="`${fieldId}-media-path`"
            :name="fieldName"
            type="hidden"
            :value="selectedPath"
            data-media-picker-hidden-input
        >

        <div
            class="rounded-xl border p-4"
            :class="hasSelection ? 'border-stone-200 bg-white' : 'border-dashed border-stone-200 bg-stone-50'"
            data-media-picker-preview
        >
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">{{ previewLabel }}</p>

            <img
                :id="`${fieldId}-media-preview-image`"
                :src="selectedUrl"
                :alt="label"
                class="max-h-28 w-auto rounded-lg object-contain"
                :class="{ hidden: ! hasSelection }"
                data-media-picker-preview-image
            >

            <p
                :id="`${fieldId}-media-preview-text`"
                class="mt-3 text-sm leading-6 text-stone-500"
                :class="{ hidden: hasSelection }"
                data-media-picker-preview-text
            >
                {{ emptyState }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button
                type="button"
                class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-amber-300 bg-amber-100 px-5 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100 disabled:cursor-not-allowed disabled:border-stone-200 disabled:bg-stone-100 disabled:text-stone-400"
                data-media-picker-open
                @click="openModal"
            >
                Select Image
            </button>

            <button
                type="button"
                class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100 disabled:cursor-not-allowed disabled:border-stone-200 disabled:bg-stone-100 disabled:text-stone-400"
                data-media-picker-clear
                @click="clearSelection"
            >
                Clear
            </button>
        </div>

        <Teleport to="body">
            <div
                v-if="isOpen"
                class="nt-admin-modal-layer"
                aria-modal="true"
                role="dialog"
                data-media-picker-modal="open"
            >
                <div class="absolute inset-0 bg-stone-950/70" @click="closeModal"></div>

                <div class="nt-admin-modal-panel relative">
                    <div class="relative w-full overflow-hidden rounded-3xl border border-stone-200 bg-stone-100 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-stone-200 bg-white px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Media Picker</p>
                                <h3 class="mt-1 text-xl font-black tracking-tight text-stone-950">Select or upload an image</h3>
                            </div>

                            <button
                                type="button"
                                class="cursor-pointer rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-100"
                                @click="closeModal"
                            >
                                Close
                            </button>
                        </div>

                        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <button
                                        type="button"
                                        class="cursor-pointer rounded-full px-4 py-2 text-sm font-semibold"
                                        :class="activeTab === 'media'
                                            ? 'border border-amber-200 bg-amber-50 text-amber-700'
                                            : 'border border-stone-200 bg-white text-stone-600'"
                                        data-media-picker-tab="media"
                                        @click="setActiveTab('media')"
                                    >
                                        Media
                                    </button>

                                    <button
                                        type="button"
                                        class="cursor-pointer rounded-full px-4 py-2 text-sm font-semibold"
                                        :class="activeTab === 'upload'
                                            ? 'border border-amber-200 bg-amber-50 text-amber-700'
                                            : 'border border-stone-200 bg-white text-stone-600'"
                                        data-media-picker-tab="upload"
                                        @click="setActiveTab('upload')"
                                    >
                                        Upload
                                    </button>
                                </div>

                                <div v-show="activeTab === 'media'" class="space-y-4">
                                    <div>
                                        <p class="text-sm font-semibold text-stone-950">Media library</p>
                                        <p class="text-sm text-stone-500">Newest uploads appear first. Click an image to view details, then choose it from the right panel.</p>
                                    </div>

                                    <div class="grid max-h-[60vh] gap-4 overflow-y-auto pr-1 sm:grid-cols-2 xl:grid-cols-3">
                                        <button
                                            v-for="item in items"
                                            :key="item.id"
                                            type="button"
                                            class="cursor-pointer rounded-2xl border bg-white p-3 text-left shadow-sm transition hover:border-amber-300 hover:bg-amber-50/40"
                                            :class="selectedMediaId === item.id ? 'border-amber-300 ring-2 ring-amber-300' : 'border-stone-200'"
                                            data-media-library-item
                                            @click="selectMediaItem(item.id)"
                                        >
                                            <img
                                                v-if="item.is_image"
                                                :src="item.url"
                                                :alt="item.alt_text || item.filename"
                                                class="h-32 w-full rounded-xl border border-stone-200 bg-stone-50 object-cover"
                                            >

                                            <div
                                                v-else
                                                class="flex h-32 items-center justify-center rounded-xl border border-stone-200 bg-stone-50 text-sm font-semibold text-stone-500"
                                            >
                                                File
                                            </div>

                                            <p class="mt-3 truncate text-sm font-semibold text-stone-900">{{ item.original_name || item.filename }}</p>
                                        </button>

                                        <div
                                            v-if="items.length === 0"
                                            class="rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-sm leading-7 text-stone-500 sm:col-span-2 xl:col-span-3"
                                        >
                                            No media uploaded yet. Use the Upload tab to add the first image.
                                        </div>
                                    </div>
                                </div>

                                <div v-show="activeTab === 'upload'" class="space-y-4">
                                    <div>
                                        <p class="text-sm font-semibold text-stone-950">Upload new image</p>
                                        <p class="mt-1 text-sm text-stone-500">Upload one or more images, then switch back to Media to explicitly choose the asset you want to use.</p>
                                    </div>

                                    <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-5">
                                        <div class="space-y-3">
                                            <label :for="`${fieldId}-picker-files`" class="block text-sm font-semibold tracking-tight text-stone-900">
                                                Image files <span class="text-rose-500">*</span>
                                            </label>
                                            <input
                                                :id="`${fieldId}-picker-files`"
                                                type="file"
                                                accept=".jpg,.jpeg,.png,.webp"
                                                multiple
                                                class="block w-full rounded-lg border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 file:mr-4 file:rounded-md file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-700 hover:file:bg-amber-100 focus:outline-none"
                                                @change="handleFileChange"
                                            >
                                        </div>

                                        <div class="space-y-3">
                                            <label :for="`${fieldId}-picker-alt-text`" class="block text-sm font-semibold tracking-tight text-stone-900">
                                                Alt text
                                            </label>
                                            <input
                                                :id="`${fieldId}-picker-alt-text`"
                                                v-model="uploadAltText"
                                                type="text"
                                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                            >
                                        </div>

                                        <div class="space-y-3">
                                            <label :for="`${fieldId}-picker-caption`" class="block text-sm font-semibold tracking-tight text-stone-900">
                                                Caption
                                            </label>
                                            <textarea
                                                :id="`${fieldId}-picker-caption`"
                                                v-model="uploadCaption"
                                                rows="4"
                                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                            ></textarea>
                                        </div>

                                        <p
                                            v-if="uploadStatus !== ''"
                                            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                                            data-media-picker-upload-status
                                        >
                                            {{ uploadStatus }}
                                        </p>

                                        <button
                                            type="button"
                                            class="inline-flex w-full cursor-pointer items-center justify-center rounded-xl border border-amber-300 bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                                            data-media-picker-upload
                                            @click="uploadMedia"
                                        >
                                            Upload To Media Library
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-5" data-media-picker-details-panel>
                                <div v-if="! selectedMedia" class="space-y-2">
                                    <p class="text-sm font-semibold text-stone-950">Image details</p>
                                    <p class="text-sm leading-7 text-stone-500">Choose an image from the Media tab to review details, edit metadata, and select it for this field.</p>
                                </div>

                                <div v-else class="space-y-4" data-media-picker-details-content>
                                    <img :src="selectedMedia.url" :alt="selectedMedia.alt_text || selectedMedia.filename" class="h-48 w-full rounded-2xl border border-stone-200 bg-stone-50 object-contain">

                                    <dl class="space-y-3 text-sm text-stone-600">
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Filename</dt>
                                            <dd class="text-right">{{ selectedMedia.filename }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Original name</dt>
                                            <dd class="text-right">{{ selectedMedia.original_name || selectedMedia.filename }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Path</dt>
                                            <dd class="max-w-[18rem] break-all text-right">{{ selectedMedia.path }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Public URL</dt>
                                            <dd class="max-w-[18rem] break-all text-right">
                                                <a :href="selectedMedia.url" target="_blank" rel="noopener noreferrer" class="text-amber-700 underline underline-offset-4">{{ selectedMedia.url }}</a>
                                            </dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Mime type</dt>
                                            <dd class="text-right">{{ selectedMedia.mime_type || 'Unknown type' }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Extension</dt>
                                            <dd class="text-right">{{ selectedMedia.extension || 'Unknown extension' }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Size</dt>
                                            <dd class="text-right">{{ selectedMedia.size ? `${(Number(selectedMedia.size) / 1024).toFixed(1)} KB` : 'Unknown size' }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Uploaded</dt>
                                            <dd class="text-right">{{ selectedMedia.created_at_label || 'Unknown date' }}</dd>
                                        </div>
                                    </dl>

                                    <div class="space-y-3">
                                        <label :for="`${fieldId}-details-alt-text`" class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Alt text
                                        </label>
                                        <input
                                            :id="`${fieldId}-details-alt-text`"
                                            v-model="selectedMedia.alt_text"
                                            type="text"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                        >
                                    </div>

                                    <div class="space-y-3">
                                        <label :for="`${fieldId}-details-caption`" class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Caption
                                        </label>
                                        <textarea
                                            :id="`${fieldId}-details-caption`"
                                            v-model="selectedMedia.caption"
                                            rows="4"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                        ></textarea>
                                    </div>

                                    <p
                                        v-if="detailsStatus !== ''"
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                                        data-media-picker-details-status
                                    >
                                        {{ detailsStatus }}
                                    </p>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <button
                                            type="button"
                                            class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-100"
                                            data-media-picker-update
                                            @click="updateSelectedMedia"
                                        >
                                            Save Details
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-amber-300 bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                                            data-media-picker-select-from-panel
                                            @click="applySelectedMedia"
                                        >
                                            Use This Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
