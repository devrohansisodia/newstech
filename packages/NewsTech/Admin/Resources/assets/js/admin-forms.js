export function initializeFileInputPreviews() {
    document.addEventListener('change', handleFileInputPreviewChange);
}

function handleFileInputPreviewChange(event) {
    const input = event.target;

    if (! (input instanceof HTMLInputElement) || input.type !== 'file' || ! input.dataset.filePreviewInput) {
        return;
    }

    const [file] = input.files ?? [];
    const previewContainer = document.getElementById(input.dataset.previewTarget ?? '');
    const previewImage = document.getElementById(input.dataset.previewImage ?? '');
    const emptyState = document.getElementById(input.dataset.emptyState ?? '');

    if (! previewContainer || ! previewImage || ! (previewImage instanceof HTMLImageElement)) {
        return;
    }

    if (! file || ! file.type.startsWith('image/')) {
        previewImage.removeAttribute('src');
        previewImage.classList.add('hidden');

        if (emptyState) {
            emptyState.classList.remove('hidden');
        }

        previewContainer.classList.remove('border-stone-200', 'bg-white');
        previewContainer.classList.add('border-dashed', 'border-stone-200', 'bg-stone-50');

        return;
    }

    previewImage.src = URL.createObjectURL(file);
    previewImage.classList.remove('hidden');

    if (emptyState) {
        emptyState.classList.add('hidden');
    }

    previewContainer.classList.remove('border-dashed', 'bg-stone-50');
    previewContainer.classList.add('border-stone-200', 'bg-white');
}
