import { Editor } from '@tiptap/core';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import StarterKit from '@tiptap/starter-kit';
import { createApp } from 'vue';
import RichTextEditorImageModal from './components/RichTextEditorImageModal.vue';

const richTextEditors = new WeakMap();
const richTextEditorRegistry = window.NewsTechRichTextEditors instanceof Map
    ? window.NewsTechRichTextEditors
    : new Map();
const defaultDebugState = {
    enabled: false,
    modalMount: null,
    lastOpenEvent: null,
    lastModalOpen: null,
    lastInsertEvent: null,
    lastInsertResult: null,
    registryKeys: [],
};

let imageInsertListenerRegistered = false;

window.NewsTechRichTextEditors = richTextEditorRegistry;

function getRichTextDebugState() {
    if (! window.NewsTechDebugRichText || typeof window.NewsTechDebugRichText !== 'object') {
        window.NewsTechDebugRichText = { ...defaultDebugState };
    }

    Object.entries(defaultDebugState).forEach(([key, value]) => {
        if (!(key in window.NewsTechDebugRichText)) {
            window.NewsTechDebugRichText[key] = value;
        }
    });

    return window.NewsTechDebugRichText;
}

function updateRichTextDebugState(partialState, { verbose = false } = {}) {
    const debugState = getRichTextDebugState();

    if (verbose && debugState.enabled !== true) {
        return;
    }

    Object.assign(debugState, partialState);
}

function registerImageInsertListener() {
    if (imageInsertListenerRegistered) {
        return;
    }

    window.addEventListener('newstech:rich-text-image-picker:insert', (event) => {
        const detail = event.detail ?? {};
        const editorId = typeof detail.editorId === 'string' ? detail.editorId : '';
        const imageAttributes = normalizeImageAttributes(detail.imageAttributes ?? {});
        const editorEntry = richTextEditorRegistry.get(editorId);

        if (! editorEntry || imageAttributes === null) {
            updateRichTextDebugState({
                lastInsertResult: {
                    status: 'failed-before-insert',
                    editorId,
                    hasEditor: richTextEditorRegistry.has(editorId),
                    hasImageAttributes: imageAttributes !== null,
                    timestamp: Date.now(),
                },
            });

            return;
        }

        const { editor, source, toolbar } = editorEntry;
        const inserted = editor.chain().focus().setImage(imageAttributes).run();

        syncEditorSource(editor, source);
        updateToolbarState(toolbar, editor);

        updateRichTextDebugState({
            lastInsertResult: {
                status: inserted ? 'inserted' : 'insert-command-failed',
                editorId,
                containsImage: source.value.includes('<img'),
                sourceLength: source.value.length,
                timestamp: Date.now(),
            },
        });
        updateRichTextDebugState({
            lastInsertDiagnostics: {
                editorId,
                imageAttributes,
                registryKeys: [...richTextEditorRegistry.keys()],
                timestamp: Date.now(),
            },
        }, { verbose: true });
    });

    imageInsertListenerRegistered = true;
}

export function initializeRichTextEditors() {
    registerImageInsertListener();

    document.querySelectorAll('[data-rich-text-editor]').forEach((element) => {
        if (! (element instanceof HTMLElement) || element.dataset.richTextEditorInitialized === 'true') {
            return;
        }

        const source = element.querySelector('[data-rich-text-editor-source]');
        const editorUi = element.querySelector('[data-rich-text-editor-ui]');
        const content = element.querySelector('[data-rich-text-editor-content]');
        const toolbar = element.querySelector('[data-rich-text-editor-toolbar]');

        if (
            ! (source instanceof HTMLTextAreaElement)
            || ! (editorUi instanceof HTMLElement)
            || ! (content instanceof HTMLElement)
            || ! (toolbar instanceof HTMLElement)
        ) {
            return;
        }

        const editor = new Editor({
            element: content,
            extensions: [
                StarterKit.configure({
                    link: false,
                    heading: {
                        levels: [2, 3],
                    },
                }),
                Link.configure({
                    autolink: true,
                    openOnClick: false,
                    enableClickSelection: true,
                    defaultProtocol: 'https',
                    HTMLAttributes: {
                        rel: 'noopener noreferrer',
                    },
                    isAllowedUri: (url, context) => {
                        if (url.startsWith('/') || url.startsWith('#')) {
                            return true;
                        }

                        if (url.startsWith('mailto:') || url.startsWith('tel:')) {
                            return true;
                        }

                        return context.defaultValidate(url);
                    },
                }),
                Image.configure({
                    allowBase64: false,
                }),
            ],
            content: source.value || '',
            onCreate: ({ editor: currentEditor }) => {
                syncEditorSource(currentEditor, source);
                updateToolbarState(toolbar, currentEditor);
            },
            onSelectionUpdate: ({ editor: currentEditor }) => updateToolbarState(toolbar, currentEditor),
            onTransaction: ({ editor: currentEditor }) => {
                syncEditorSource(currentEditor, source);
                updateToolbarState(toolbar, currentEditor);
            },
        });

        source.classList.add('hidden');
        editorUi.classList.remove('hidden');
        element.dataset.richTextEditorInitialized = 'true';
        richTextEditors.set(element, editor);
        registerRichTextEditor(source, editor, toolbar);

        toolbar.addEventListener('click', (event) => {
            const target = event.target;

            if (! (target instanceof HTMLElement)) {
                return;
            }

            const button = target.closest('[data-rich-text-editor-action]');

            if (! (button instanceof HTMLButtonElement)) {
                return;
            }

            event.preventDefault();
            handleRichTextToolbarAction(element, editor, button);
        });
    });
}

export function initializeGlobalRichTextEditorImageModal() {
    const element = document.querySelector('[data-editor-image-modal-root]');

    if (! (element instanceof HTMLElement)) {
        updateRichTextDebugState({
            modalMount: {
                status: 'root-not-found',
                timestamp: Date.now(),
            },
        });

        return;
    }

    element.dataset.vueMountAttempted = 'true';
    updateRichTextDebugState({
        modalMount: {
            status: 'root-found',
            timestamp: Date.now(),
        },
    });

    if (element.dataset.editorImageModalInitialized === 'true') {
        element.dataset.vueMountStatus = 'already-initialized';
        updateRichTextDebugState({
            modalMount: {
                status: 'already-initialized',
                timestamp: Date.now(),
            },
        });

        return;
    }

    const configScript = element.querySelector('[data-editor-image-modal-config]');
    const serializedConfig = configScript instanceof HTMLScriptElement
        ? configScript.textContent?.trim() ?? ''
        : '';

    if (serializedConfig === '') {
        element.dataset.vueMountStatus = 'missing-config';
        updateRichTextDebugState({
            modalMount: {
                status: 'missing-config',
                timestamp: Date.now(),
            },
        });

        return;
    }

    let config;

    try {
        config = JSON.parse(serializedConfig);
    } catch (error) {
        element.dataset.vueMountStatus = 'config-parse-failed';
        updateRichTextDebugState({
            modalMount: {
                status: 'config-parse-failed',
                error: error instanceof Error ? error.message : String(error),
                timestamp: Date.now(),
            },
        });

        return;
    }

    try {
        const app = createApp(RichTextEditorImageModal, config);

        element.__vue_app__ = app;
        app.mount(element);
        element.dataset.editorImageModalInitialized = 'true';
        element.dataset.vueMounted = 'true';
        element.dataset.vueMountStatus = 'mounted';

        updateRichTextDebugState({
            modalMount: {
                status: 'mounted',
                timestamp: Date.now(),
            },
        });
    } catch (error) {
        element.dataset.vueMountStatus = 'mount-failed';
        updateRichTextDebugState({
            modalMount: {
                status: 'mount-failed',
                error: error instanceof Error ? error.message : String(error),
                timestamp: Date.now(),
            },
        });
    }
}

export function syncRichTextEditorsInForm(form) {
    form.querySelectorAll('[data-rich-text-editor]').forEach((element) => {
        if (! (element instanceof HTMLElement)) {
            return;
        }

        const editor = richTextEditors.get(element);
        const source = element.querySelector('[data-rich-text-editor-source]');

        if (! editor || ! (source instanceof HTMLTextAreaElement)) {
            return;
        }

        syncEditorSource(editor, source);
    });
}

function handleRichTextToolbarAction(editorElement, editor, button) {
    const action = button.dataset.richTextEditorAction ?? '';

    switch (action) {
        case 'paragraph':
            editor.chain().focus().setParagraph().run();

            break;
        case 'heading':
            editor.chain().focus().toggleHeading({
                level: Number(button.dataset.richTextEditorLevel ?? 2),
            }).run();

            break;
        case 'bold':
            editor.chain().focus().toggleBold().run();

            break;
        case 'italic':
            editor.chain().focus().toggleItalic().run();

            break;
        case 'bullet-list':
            editor.chain().focus().toggleBulletList().run();

            break;
        case 'ordered-list':
            editor.chain().focus().toggleOrderedList().run();

            break;
        case 'blockquote':
            editor.chain().focus().toggleBlockquote().run();

            break;
        case 'link':
            handleLinkAction(editor);

            break;
        case 'image':
            openRichTextImagePicker(editorElement, editor);

            break;
        case 'clear-formatting':
            editor.chain().focus().clearNodes().unsetAllMarks().run();

            break;
        case 'undo':
            editor.chain().focus().undo().run();

            break;
        case 'redo':
            editor.chain().focus().redo().run();

            break;
        default:
            break;
    }
}

function openRichTextImagePicker(editorElement, editor) {
    const editorId = editorElement.querySelector('[data-rich-text-editor-source]')?.id ?? null;

    if (typeof editorId === 'string' && editorId !== '') {
        editor.commands.focus();
    }

    updateRichTextDebugState({
        lastOpenEvent: {
            editorId,
            timestamp: Date.now(),
        },
    });
    updateRichTextDebugState({
        lastOpenDiagnostics: {
            editorId,
            registryKeys: [...richTextEditorRegistry.keys()],
            timestamp: Date.now(),
        },
    }, { verbose: true });

    window.dispatchEvent(new CustomEvent('newstech:rich-text-image-picker:open', {
        detail: {
            editorId,
        },
    }));
}

function registerRichTextEditor(source, editor, toolbar) {
    if (! source.id) {
        return;
    }

    richTextEditorRegistry.set(source.id, {
        editor,
        source,
        toolbar,
    });

    updateRichTextDebugState({
        registryKeys: [...richTextEditorRegistry.keys()],
    });
}

function normalizeImageAttributes(attributes) {
    if (typeof attributes !== 'object' || attributes === null) {
        return null;
    }

    const src = typeof attributes.src === 'string' ? attributes.src.trim() : '';
    const alt = typeof attributes.alt === 'string' ? attributes.alt : '';
    const title = typeof attributes.title === 'string' ? attributes.title : '';

    if (src === '') {
        return null;
    }

    return {
        src,
        alt,
        ...(title !== ''
            ? { title }
            : {}),
    };
}

function handleLinkAction(editor) {
    const currentHref = editor.getAttributes('link').href ?? '';
    const selectionText = editor.state.doc.textBetween(editor.state.selection.from, editor.state.selection.to, ' ').trim();
    const urlInput = window.prompt('Enter a URL. Leave blank to remove the link.', currentHref);

    if (urlInput === null) {
        return;
    }

    const normalizedUrl = normalizeLinkUrl(urlInput);

    if (normalizedUrl === '') {
        editor.chain().focus().extendMarkRange('link').unsetLink().run();

        return;
    }

    if (! normalizedUrl) {
        window.alert('Enter a valid URL, mailto link, phone link, path, or anchor.');

        return;
    }

    const linkAttributes = resolveLinkAttributes(normalizedUrl);

    if (selectionText === '' && ! editor.isActive('link')) {
        editor.chain().focus().insertContent([
            {
                type: 'text',
                text: normalizedUrl,
                marks: [
                    {
                        type: 'link',
                        attrs: linkAttributes,
                    },
                ],
            },
        ]).run();

        return;
    }

    editor.chain().focus().extendMarkRange('link').setLink(linkAttributes).run();
}

function normalizeLinkUrl(url) {
    const trimmedUrl = url.trim();

    if (trimmedUrl === '') {
        return '';
    }

    const normalizedUrl = trimmedUrl.startsWith('www.')
        ? `https://${trimmedUrl}`
        : trimmedUrl;
    const lowercaseUrl = normalizedUrl.toLowerCase();

    if (
        lowercaseUrl.startsWith('javascript:')
        || lowercaseUrl.startsWith('data:')
        || lowercaseUrl.startsWith('vbscript:')
    ) {
        return null;
    }

    if (normalizedUrl.startsWith('/') || normalizedUrl.startsWith('#')) {
        return normalizedUrl;
    }

    if (normalizedUrl.startsWith('mailto:') || normalizedUrl.startsWith('tel:')) {
        return normalizedUrl;
    }

    try {
        const resolvedUrl = new URL(normalizedUrl);

        if (resolvedUrl.protocol !== 'http:' && resolvedUrl.protocol !== 'https:') {
            return null;
        }

        return resolvedUrl.toString();
    } catch {
        return null;
    }
}

function resolveLinkAttributes(url) {
    if (url.startsWith('http://') || url.startsWith('https://')) {
        return {
            href: url,
            target: '_blank',
            rel: 'noopener noreferrer',
        };
    }

    return {
        href: url,
        target: null,
        rel: null,
    };
}

function syncEditorSource(editor, source) {
    const nextValue = editor.isEmpty ? '' : editor.getHTML();

    if (source.value === nextValue) {
        return;
    }

    source.value = nextValue;
    source.dispatchEvent(new Event('input', { bubbles: true }));
    source.dispatchEvent(new Event('change', { bubbles: true }));
}

function updateToolbarState(toolbar, editor) {
    toolbar.querySelectorAll('[data-rich-text-editor-action]').forEach((button) => {
        if (! (button instanceof HTMLButtonElement)) {
            return;
        }

        const action = button.dataset.richTextEditorAction ?? '';
        const level = Number(button.dataset.richTextEditorLevel ?? 0);
        let isActive = false;
        let isDisabled = false;

        switch (action) {
            case 'paragraph':
                isActive = editor.isActive('paragraph');

                break;
            case 'heading':
                isActive = editor.isActive('heading', { level });

                break;
            case 'bold':
                isActive = editor.isActive('bold');

                break;
            case 'italic':
                isActive = editor.isActive('italic');

                break;
            case 'bullet-list':
                isActive = editor.isActive('bulletList');

                break;
            case 'ordered-list':
                isActive = editor.isActive('orderedList');

                break;
            case 'blockquote':
                isActive = editor.isActive('blockquote');

                break;
            case 'link':
                isActive = editor.isActive('link');

                break;
            case 'undo':
                isDisabled = ! editor.can().chain().focus().undo().run();

                break;
            case 'redo':
                isDisabled = ! editor.can().chain().focus().redo().run();

                break;
            default:
                break;
        }

        button.classList.toggle('is-active', isActive);
        button.classList.toggle('is-disabled', isDisabled);
        button.disabled = isDisabled;
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
}
