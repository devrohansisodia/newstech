<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    csrfToken: {
        type: String,
        required: true,
    },
    uploadEndpoint: {
        type: String,
        required: true,
    },
    items: {
        type: Array,
        default: () => [],
    },
    paginationHtml: {
        type: String,
        default: '',
    },
    firstItem: {
        type: Number,
        default: 0,
    },
    lastItem: {
        type: Number,
        default: 0,
    },
    totalItems: {
        type: Number,
        default: 0,
    },
});

const mediaItems = ref(props.items.map((item) => ({ ...item })));
const uploadedItems = ref([]);
const isUploadModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const uploadFiles = ref([]);
const uploadAltText = ref('');
const uploadCaption = ref('');
const uploadStatus = ref('');
const detailsStatus = ref('');
const selectedMediaId = ref(null);

const selectedMedia = computed(() => mediaItems.value.find((item) => item.id === selectedMediaId.value) ?? null);
const hasItems = computed(() => mediaItems.value.length > 0);

function openUploadModal() {
    isUploadModalOpen.value = true;
    uploadStatus.value = '';
}

function closeUploadModal() {
    isUploadModalOpen.value = false;
    uploadFiles.value = [];
    uploadAltText.value = '';
    uploadCaption.value = '';
    uploadStatus.value = '';
}

function openDetailModal(media) {
    selectedMediaId.value = media.id;
    detailsStatus.value = '';
    isDetailModalOpen.value = true;
    isUploadModalOpen.value = false;
}

function closeDetailModal() {
    isDetailModalOpen.value = false;
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
    const newItems = Array.isArray(payload.media_items) ? payload.media_items : [];

    uploadedItems.value = newItems;
    mediaItems.value = [...newItems.slice().reverse(), ...mediaItems.value];
    uploadFiles.value = [];
    uploadAltText.value = '';
    uploadCaption.value = '';
    uploadStatus.value = payload.message || 'Media uploaded successfully.';
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

    mediaItems.value = mediaItems.value.map((item) => item.id === updatedMedia.id ? updatedMedia : item);
    uploadedItems.value = uploadedItems.value.map((item) => item.id === updatedMedia.id ? updatedMedia : item);
    selectedMediaId.value = updatedMedia.id;
    detailsStatus.value = payload.message || 'Media details updated successfully.';
}

async function deleteSelectedMedia() {
    if (! selectedMedia.value?.delete_url) {
        return;
    }

    const deleteUrl = selectedMedia.value.delete_url;
    const deletedId = selectedMedia.value.id;

    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('_token', props.csrfToken);

    const response = await fetch(deleteUrl, {
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
        detailsStatus.value = 'Could not delete media. Try again.';

        return;
    }

    mediaItems.value = mediaItems.value.filter((item) => item.id !== deletedId);
    uploadedItems.value = uploadedItems.value.filter((item) => item.id !== deletedId);
    closeDetailModal();
}

function formattedSize(media) {
    return media.size ? `${(Number(media.size) / 1024).toFixed(1)} KB` : 'Unknown size';
}
</script>

<template>
    <div class="space-y-5" data-media-library-vue="true">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Library Items</p>
                <h3 class="mt-2 text-xl font-black tracking-tight text-stone-950">Uploaded media</h3>
                <p class="mt-2 text-sm leading-7 text-stone-500">
                    Smaller cards keep the library fast to scan. Click any image to review details or update alt text and caption.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <p class="text-sm text-stone-500">
                    Showing {{ firstItem }}-{{ lastItem }} of {{ totalItems }}
                </p>

                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-amber-300 bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                    data-media-upload-open
                    @click="openUploadModal"
                >
                    Add Media
                </button>
            </div>
        </div>

        <div id="media-library-grid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6" data-media-library-grid>
            <article
                v-for="media in mediaItems"
                :key="media.id"
                class="group rounded-2xl border border-stone-200 bg-stone-50/60 p-2"
                data-media-card
                :data-media-id="media.id"
            >
                <div class="relative overflow-hidden rounded-xl border border-stone-200 bg-white">
                    <button
                        type="button"
                        class="block h-20 w-full cursor-pointer overflow-hidden text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                        data-media-detail-open
                        @click="openDetailModal(media)"
                    >
                        <img
                            v-if="media.is_image"
                            :src="media.url"
                            :alt="media.alt_text || media.filename"
                            class="h-20 w-full object-cover"
                        >

                        <div
                            v-else
                            class="flex h-20 items-center justify-center text-xs font-semibold text-stone-500"
                        >
                            File
                        </div>
                    </button>

                    <div class="pointer-events-none absolute inset-0 bg-stone-950/15 opacity-0 transition duration-150 group-hover:opacity-100 group-focus-within:opacity-100"></div>

                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center invisible opacity-0 transition duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                        <button
                            type="button"
                            class="pointer-events-auto inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border border-white/90 bg-white/95 text-stone-800 shadow-lg transition hover:scale-[1.03] hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                            data-media-detail-open
                            aria-label="Edit media details"
                            @click="openDetailModal(media)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path d="M13.586 3.586a2 2 0 1 1 2.828 2.828l-8.9 8.9a2 2 0 0 1-.878.513l-2.879.823.823-2.879a2 2 0 0 1 .513-.878l8.493-8.493Z" />
                            </svg>
                        </button>
                    </div>

                    <div class="pointer-events-none absolute bottom-2 left-2 invisible opacity-0 transition duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                        <button
                            type="button"
                            class="pointer-events-auto inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border border-white/90 bg-white/95 text-rose-600 shadow-lg transition hover:scale-[1.03] hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300"
                            aria-label="Delete media"
                            @click="openDetailModal(media)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M8.75 2.5a.75.75 0 0 0-.75.75V4H5.5a.75.75 0 0 0 0 1.5h.44l.713 9.265A2.25 2.25 0 0 0 8.897 17h2.206a2.25 2.25 0 0 0 2.244-2.235L14.06 5.5h.44a.75.75 0 0 0 0-1.5H12V3.25a.75.75 0 0 0-.75-.75h-2.5ZM9.5 4v-.5h1V4h-1Zm-.718 3.03a.75.75 0 0 1 .748.752l.058 5a.75.75 0 0 1-1.5.018l-.059-5a.75.75 0 0 1 .753-.77Zm2.436.77a.75.75 0 0 0-1.5 0v5a.75.75 0 0 0 1.5 0v-5Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </article>

            <div
                v-if="!hasItems"
                id="media-library-empty-state"
                class="rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-sm leading-7 text-stone-500 sm:col-span-2 lg:col-span-3 xl:col-span-4"
            >
                No media uploaded yet. Use the Add Media button to upload the first asset.
            </div>
        </div>

        <div v-if="paginationHtml !== ''" v-html="paginationHtml"></div>

        <Teleport to="body">
            <div v-if="isUploadModalOpen" class="fixed inset-0 z-50" aria-hidden="false" data-media-upload-modal>
                <div class="absolute inset-0 bg-stone-950/70" data-media-upload-close @click="closeUploadModal"></div>

                <div class="relative mx-auto flex min-h-full max-w-6xl items-center justify-center px-4 py-6 sm:px-6">
                    <div class="relative w-full overflow-hidden rounded-3xl border border-stone-200 bg-stone-100 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-stone-200 bg-white px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Add Media</p>
                                <h3 class="mt-1 text-xl font-black tracking-tight text-stone-950">Upload one or more images</h3>
                            </div>

                            <button
                                type="button"
                                class="cursor-pointer rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-100"
                                data-media-upload-close
                                @click="closeUploadModal"
                            >
                                Close
                            </button>
                        </div>

                        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                            <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-5">
                                <div>
                                    <p class="text-sm font-semibold text-stone-950">Bulk upload</p>
                                    <p class="mt-1 text-sm text-stone-500">Select multiple JPG, JPEG, PNG, or WebP files. New uploads appear in the review area immediately.</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-3">
                                        <label for="media-library-files" class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Image files <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            id="media-library-files"
                                            type="file"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            multiple
                                            required
                                            class="block w-full rounded-lg border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 file:mr-4 file:rounded-md file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-700 hover:file:bg-amber-100 focus:outline-none"
                                            @change="handleFileChange"
                                        >
                                    </div>

                                    <div class="space-y-3">
                                        <label for="media-library-alt-text" class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Alt text
                                        </label>
                                        <p class="text-sm leading-6 text-stone-500">Optional default alt text applied to this upload batch. You can adjust each item afterward.</p>
                                        <input
                                            id="media-library-alt-text"
                                            v-model="uploadAltText"
                                            type="text"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                        >
                                    </div>

                                    <div class="space-y-3">
                                        <label for="media-library-caption" class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Caption
                                        </label>
                                        <p class="text-sm leading-6 text-stone-500">Optional default caption applied to this upload batch.</p>
                                        <textarea
                                            id="media-library-caption"
                                            v-model="uploadCaption"
                                            rows="4"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                        ></textarea>
                                    </div>

                                    <p
                                        v-if="uploadStatus !== ''"
                                        id="media-library-upload-status"
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                                    >
                                        {{ uploadStatus }}
                                    </p>

                                    <button
                                        type="button"
                                        class="inline-flex w-full cursor-pointer items-center justify-center rounded-xl border border-amber-300 bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                                        @click="uploadMedia"
                                    >
                                        Upload Media
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-stone-950">Review uploaded items</p>
                                        <p class="mt-1 text-sm text-stone-500">Click a new upload to review details and update alt text or caption one-by-one.</p>
                                    </div>
                                </div>

                                <div
                                    v-if="uploadedItems.length === 0"
                                    id="media-library-upload-results-empty"
                                    class="rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-sm leading-7 text-stone-500"
                                >
                                    Uploaded images will appear here after the modal upload finishes.
                                </div>

                                <div
                                    v-else
                                    id="media-library-upload-results"
                                    class="grid max-h-[55vh] grid-cols-2 gap-3 overflow-y-auto pr-1 sm:grid-cols-3"
                                    data-media-upload-results
                                >
                                    <article
                                        v-for="media in uploadedItems"
                                        :key="`uploaded-${media.id}`"
                                        class="group rounded-2xl border border-stone-200 bg-white p-2"
                                    >
                                        <div class="relative overflow-hidden rounded-xl border border-stone-200 bg-white">
                                            <button
                                                type="button"
                                                class="block h-20 w-full cursor-pointer overflow-hidden text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                                                @click="openDetailModal(media)"
                                            >
                                                <img
                                                    v-if="media.is_image"
                                                    :src="media.url"
                                                    :alt="media.alt_text || media.filename"
                                                    class="h-20 w-full object-cover"
                                                >
                                            </button>
                                        </div>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div v-if="isDetailModalOpen && selectedMedia" class="fixed inset-0 z-50" aria-hidden="false" data-media-detail-modal>
                <div class="absolute inset-0 bg-stone-950/70" data-media-detail-close @click="closeDetailModal"></div>

                <div class="relative mx-auto flex min-h-full max-w-6xl items-center justify-center px-4 py-6 sm:px-6">
                    <div class="relative w-full overflow-hidden rounded-3xl border border-stone-200 bg-stone-100 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-stone-200 bg-white px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Media Details</p>
                                <h3 class="mt-1 text-xl font-black tracking-tight text-stone-950">Review and update media</h3>
                            </div>

                            <button
                                type="button"
                                class="cursor-pointer rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-100"
                                data-media-detail-close
                                @click="closeDetailModal"
                            >
                                Close
                            </button>
                        </div>

                        <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.95fr)]">
                            <div class="space-y-4">
                                <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                                    <img :src="selectedMedia.url" :alt="selectedMedia.alt_text || selectedMedia.filename" class="h-[24rem] w-full object-contain">
                                </div>

                                <div class="rounded-2xl border border-stone-200 bg-white p-5">
                                    <p class="text-sm font-semibold text-stone-950">File metadata</p>
                                    <dl class="mt-4 space-y-3 text-sm text-stone-600">
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Filename</dt>
                                            <dd class="text-right">{{ selectedMedia.filename }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Original name</dt>
                                            <dd class="text-right">{{ selectedMedia.original_name || selectedMedia.filename }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Storage path</dt>
                                            <dd class="max-w-[22rem] break-all text-right">{{ selectedMedia.path }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Public URL</dt>
                                            <dd class="max-w-[22rem] break-all text-right">
                                                <a :href="selectedMedia.url" target="_blank" rel="noopener noreferrer" class="text-amber-700 underline underline-offset-4">Open file</a>
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
                                            <dd class="text-right">{{ formattedSize(selectedMedia) }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-4">
                                            <dt class="font-semibold text-stone-900">Uploaded</dt>
                                            <dd class="text-right">{{ selectedMedia.created_at_label || 'Unknown date' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-5">
                                <div>
                                    <p class="text-sm font-semibold text-stone-950">Editable details</p>
                                    <p class="mt-1 text-sm text-stone-500">Update accessibility and editorial metadata without leaving the library.</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-3">
                                        <label for="media-detail-alt-text-input" class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Alt text
                                        </label>
                                        <input
                                            id="media-detail-alt-text-input"
                                            v-model="selectedMedia.alt_text"
                                            type="text"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                        >
                                    </div>

                                    <div class="space-y-3">
                                        <label for="media-detail-caption-input" class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Caption
                                        </label>
                                        <textarea
                                            id="media-detail-caption-input"
                                            v-model="selectedMedia.caption"
                                            rows="5"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-400 focus:outline-none"
                                        ></textarea>
                                    </div>

                                    <p
                                        v-if="detailsStatus !== ''"
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                                    >
                                        {{ detailsStatus }}
                                    </p>

                                    <button
                                        type="button"
                                        class="inline-flex w-full cursor-pointer items-center justify-center rounded-xl border border-amber-300 bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                                        @click="updateSelectedMedia"
                                    >
                                        Save Media Details
                                    </button>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <a
                                        :href="selectedMedia.edit_url"
                                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
                                    >
                                        Open Full Edit Page
                                    </a>

                                    <button
                                        type="button"
                                        class="inline-flex w-full cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
                                        @click="deleteSelectedMedia"
                                    >
                                        Delete Media
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
