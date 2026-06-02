import '../../../../../../resources/js/bootstrap';
import { initializeFileInputPreviews } from './admin-forms';
import MediaLibrary from './components/MediaLibrary.vue';
import MediaPicker from './components/MediaPicker.vue';
import SeoScorePanel from './components/SeoScorePanel.vue';
import {
    initializeGlobalRichTextEditorImageModal,
    initializeRichTextEditors,
    syncRichTextEditorsInForm,
} from './rich-text-editor';
import { mountVueRoots } from './vue-mount';

function bootstrapAdminUi() {
    initializeRichTextEditors();
    initializeMediaLibrary();
    initializeMediaPickers();
    initializeSeoScorePanels();
    initializeGlobalRichTextEditorImageModal();
    initializeFileInputPreviews();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapAdminUi, { once: true });
} else {
    bootstrapAdminUi();
}

document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (! (form instanceof HTMLFormElement)) {
        return;
    }

    syncRichTextEditorsInForm(form);
});

function initializeMediaLibrary() {
    mountVueRoots(
        '[data-media-library-root]',
        MediaLibrary,
        '[data-media-library-config]',
        'mediaLibraryInitialized',
    );
}

function initializeMediaPickers() {
    mountVueRoots(
        '[data-media-picker-root]',
        MediaPicker,
        '[data-media-picker-config]',
        'mediaPickerInitialized',
    );
}

function initializeSeoScorePanels() {
    mountVueRoots(
        '[data-seo-score-panel-root]',
        SeoScorePanel,
        '[data-seo-score-panel-config]',
        'seoScorePanelInitialized',
    );
}
