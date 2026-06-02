<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    modalTitle: {
        type: String,
        required: true,
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
const selectedMediaId = ref(items.value[0]?.id ?? null);
const uploadFiles = ref([]);
const uploadAltText = ref('');
const uploadCaption = ref('');
const uploadStatus = ref('');
const detailsStatus = ref('');
const activeEditorId = ref(null);

const selectedMedia = computed(() => items.value.find((item) => item.id === selectedMediaId.value) ?? null);

function lockModalScroll() {
    document.documentElement.classList.add('nt-admin-modal-open');
    document.body.classList.add('nt-admin-modal-open');
}

function unlockModalScroll() {
    document.documentElement.classList.remove('nt-admin-modal-open');
    document.body.classList.remove('nt-admin-modal-open');
}

function handleOpen(event) {
    activeEditorId.value = event.detail?.editorId ?? null;
    isOpen.value = true;
    activeTab.value = 'media';
    uploadStatus.value = '';
    detailsStatus.value = '';
    window.NewsTechDebugRichText = window.NewsTechDebugRichText ?? {};
    window.NewsTechDebugRichText.lastModalOpen = {
        editorId: activeEditorId.value,
        itemCount: items.value.length,
        timestamp: Date.now(),
    };
    lockModalScroll();

    if (selectedMediaId.value === null && items.value.length > 0) {
        selectedMediaId.value = items.value[0].id;
    }
}

function closeModal() {
    isOpen.value = false;
    uploadStatus.value = '';
    detailsStatus.value = '';
    uploadFiles.value = [];
    activeEditorId.value = null;
    unlockModalScroll();
}

function setActiveTab(tab) {
    activeTab.value = tab;
}

function selectMediaItem(mediaId) {
    selectedMediaId.value = mediaId;
    detailsStatus.value = '';
}

function selectAndInsertMedia(media) {
    selectedMediaId.value = media.id;
    detailsStatus.value = '';
    insertMedia(media);
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
    uploadStatus.value = 'Media uploaded. Review it in the Media tab, then insert it.';
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
    detailsStatus.value = payload.message || 'Media details updated.';
}

function insertSelectedImage() {
    if (! selectedMedia.value) {
        return;
    }

    insertMedia(selectedMedia.value);
}

function insertMedia(media) {
    const src = resolveMediaSrc(media);

    window.NewsTechDebugRichText = window.NewsTechDebugRichText ?? {};
    window.NewsTechDebugRichText.lastInsertEvent = {
        editorId: activeEditorId.value,
        mediaId: media?.id ?? null,
        src,
        timestamp: Date.now(),
    };

    if (! activeEditorId.value || src === '') {
        detailsStatus.value = 'Select an active editor and an image with a valid URL.';

        return;
    }

    window.dispatchEvent(new CustomEvent('newstech:rich-text-image-picker:insert', {
        detail: {
            editorId: activeEditorId.value,
            imageAttributes: {
                src,
                alt: media.alt_text || media.original_name || media.filename || '',
                ...(media.caption
                    ? {
                          title: media.caption,
                      }
                    : {}),
            },
        },
    }));

    closeModal();
}

function resolveMediaSrc(media) {
    if (! media || typeof media !== 'object') {
        return '';
    }

    if (typeof media.url === 'string' && media.url.trim() !== '') {
        return media.url.trim();
    }

    if (typeof media.public_url === 'string' && media.public_url.trim() !== '') {
        return media.public_url.trim();
    }

    if (typeof media.path === 'string' && media.path.trim() !== '') {
        return media.path.startsWith('/')
            ? media.path
            : `/storage/${media.path}`;
    }

    return '';
}

onMounted(() => {
    window.addEventListener('newstech:rich-text-image-picker:open', handleOpen);
});

onBeforeUnmount(() => {
    window.removeEventListener('newstech:rich-text-image-picker:open', handleOpen);
    unlockModalScroll();
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="nt-admin-modal-layer"
            role="dialog"
            aria-modal="true"
            data-editor-image-modal="open"
            data-state="open"
            :data-active-editor-id="activeEditorId || ''"
            data-rich-text-editor-image-picker-dialog
        >
            <div class="absolute inset-0 bg-stone-950/70" @click="closeModal"></div>

            <div class="nt-admin-modal-panel relative">
                <div class="relative w-full overflow-hidden rounded-3xl border border-stone-200 bg-stone-100 shadow-2xl">
                    <div class="flex items-center justify-between border-b border-stone-200 bg-white px-6 py-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Insert Image</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-stone-950">{{ modalTitle }}</h3>
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
                                    @click="setActiveTab('upload')"
                                >
                                    Upload
                                </button>
                            </div>

                            <div v-show="activeTab === 'media'" class="space-y-4">
                                <div>
                                    <p class="text-sm font-semibold text-stone-950">Image library</p>
                                    <p class="text-sm text-stone-500">Select an existing image to place inside the content body.</p>
                                </div>

                                <div class="grid max-h-[60vh] gap-4 overflow-y-auto pr-1 sm:grid-cols-2 xl:grid-cols-3">
                                    <button
                                        v-for="item in items"
                                        :key="item.id"
                                        type="button"
                                        class="cursor-pointer rounded-2xl border bg-white p-3 text-left shadow-sm transition hover:border-amber-300 hover:bg-amber-50/40"
                                        :class="selectedMediaId === item.id ? 'border-amber-300 ring-2 ring-amber-300' : 'border-stone-200'"
                                        data-rich-text-editor-image-library-item
                                        @click="selectAndInsertMedia(item)"
                                    >
                                        <img
                                            :src="item.url"
                                            :alt="item.alt_text || item.original_name || item.filename"
                                            class="h-32 w-full rounded-xl border border-stone-200 bg-stone-50 object-cover"
                                        >

                                        <p class="mt-3 truncate text-sm font-semibold text-stone-900">
                                            {{ item.original_name || item.filename }}
                                        </p>
                                    </button>

                                    <div
                                        v-if="items.length === 0"
                                        class="rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-sm leading-7 text-stone-500 sm:col-span-2 xl:col-span-3"
                                    >
                                        No images uploaded yet. Use the Upload tab to add the first inline image.
                                    </div>
                                </div>
                            </div>

                            <div v-show="activeTab === 'upload'" class="space-y-4">
                                <div>
                                    <p class="text-sm font-semibold text-stone-950">Upload new image</p>
                                    <p class="mt-1 text-sm text-stone-500">Upload one or more images, then return to Media to choose the asset you want to insert.</p>
                                </div>

                                <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-5">
                                    <div class="space-y-3">
                                        <label class="block text-sm font-semibold tracking-tight text-stone-900">
                                            Image files <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            multiple
                                            class="block w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 file:mr-4 file:rounded-full file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-700"
                                            @change="handleFileChange"
                                        >
                                    </div>

                                    <div class="space-y-3">
                                        <label class="block text-sm font-semibold tracking-tight text-stone-900">Alt text</label>
                                        <input
                                            v-model="uploadAltText"
                                            type="text"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700"
                                            placeholder="Describe the uploaded image"
                                        >
                                    </div>

                                    <div class="space-y-3">
                                        <label class="block text-sm font-semibold tracking-tight text-stone-900">Caption</label>
                                        <textarea
                                            v-model="uploadCaption"
                                            rows="3"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700"
                                            placeholder="Optional caption or internal note"
                                        ></textarea>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-3">
                                        <button
                                            type="button"
                                            class="cursor-pointer rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100"
                                            data-rich-text-editor-image-upload
                                            @click="uploadMedia"
                                        >
                                            Upload Images
                                        </button>
                                    </div>

                                    <p v-if="uploadStatus" class="text-sm text-stone-500">{{ uploadStatus }}</p>
                                </div>
                            </div>
                        </div>

                        <aside class="space-y-4 rounded-3xl border border-stone-200 bg-white p-5">
                            <template v-if="selectedMedia">
                                <div class="space-y-4">
                                    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-stone-50">
                                        <img
                                            :src="selectedMedia.url"
                                            :alt="selectedMedia.alt_text || selectedMedia.original_name || selectedMedia.filename"
                                            class="h-48 w-full object-cover"
                                        >
                                    </div>

                                    <div class="space-y-1">
                                        <h4 class="text-lg font-bold tracking-tight text-stone-950">
                                            {{ selectedMedia.original_name || selectedMedia.filename }}
                                        </h4>
                                        <p class="text-sm text-stone-500">{{ selectedMedia.mime_type || 'Image' }}</p>
                                    </div>

                                    <div class="space-y-3">
                                        <label class="block text-sm font-semibold tracking-tight text-stone-900">Alt text</label>
                                        <input
                                            v-model="selectedMedia.alt_text"
                                            type="text"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700"
                                            placeholder="Describe the image for readers"
                                        >
                                    </div>

                                    <div class="space-y-3">
                                        <label class="block text-sm font-semibold tracking-tight text-stone-900">Caption</label>
                                        <textarea
                                            v-model="selectedMedia.caption"
                                            rows="3"
                                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700"
                                            placeholder="Optional caption"
                                        ></textarea>
                                    </div>

                                    <p class="text-xs uppercase tracking-[0.25em] text-stone-400">
                                        Insert Image
                                    </p>
                                    <p class="text-sm leading-6 text-stone-500">
                                        Adds this image inside the article or page content. Featured Image remains separate.
                                    </p>
                                    <p
                                        v-if="activeEditorId"
                                        class="text-xs uppercase tracking-[0.2em] text-stone-400"
                                        data-editor-image-modal-active-editor
                                    >
                                        Active editor: {{ activeEditorId }}
                                    </p>

                                    <div class="flex flex-wrap gap-3">
                                        <button
                                            type="button"
                                            class="cursor-pointer rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50"
                                            @click="updateSelectedMedia"
                                        >
                                            Save Details
                                        </button>

                                        <button
                                            type="button"
                                            class="cursor-pointer rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100"
                                            @click="insertSelectedImage"
                                        >
                                            Insert Selected Image
                                        </button>
                                    </div>

                                    <p v-if="detailsStatus" class="text-sm text-stone-500">{{ detailsStatus }}</p>
                                </div>
                            </template>

                            <div v-else class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-5 text-sm leading-7 text-stone-500">
                                Choose an image from the media library or upload a new one to insert it into the content body.
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
