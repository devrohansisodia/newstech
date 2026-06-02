# Phase 5.3.4 Browser Inline Image Runtime Debug Implementation Plan

> **For agentic workers:** Execute this as a narrow runtime-debug and fix pass only. Do not expand scope into new editor features or structural rewrites.

**Goal:** Trace the real browser inline-image insert path end-to-end and fix the exact runtime break point so clicking a media image inserts it into the active Tiptap editor and syncs the hidden textarea.

**Architecture:** Keep the current global Admin-owned modal architecture. Use safe debug surfaces plus a stable editor-id-based event flow to verify `open -> modal -> media click -> insert event -> registry lookup -> setImage -> sync`.

**Tech Stack:** Laravel 12, Blade, Vue 3, Vite, existing Admin JS bundle, current Tiptap rich text editor, existing Media endpoints.

---

### Task 1: Add Safe Runtime Debug Surfaces

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/app.js`
- Modify: `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`
- Modify: `packages/NewsTech/Admin/Resources/views/components/layouts/app.blade.php` if DOM hooks are needed

- [ ] **Step 1: Expose guarded debug objects on `window`**

Provide:

```js
window.NewsTechRichTextEditors = richTextEditorRegistry;
window.NewsTechDebugRichText = {
    lastOpenEvent: null,
    lastInsertEvent: null,
    lastInsertAttempt: null,
    lastEditorHtml: null,
    lastTextareaValue: null,
};
```

Keep this safe and lightweight. No noisy logs by default.

- [ ] **Step 2: Add modal state hooks**

Expose runtime-visible markers such as:

```html
data-state="open"
data-active-editor-id="content"
```

These should make it obvious in DevTools whether the modal received the open event and which editor is active.

---

### Task 2: Trace The Open Event In Real Runtime Terms

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/app.js`
- Modify: `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`

- [ ] **Step 1: Record `Insert Image` open dispatch**

When the toolbar image action fires, store:

```js
window.NewsTechDebugRichText.lastOpenEvent = {
    editorId,
    timestamp: Date.now(),
};
```

- [ ] **Step 2: Record modal open receipt**

Inside the Vue modal open handler, store:

```js
window.NewsTechDebugRichText.lastModalOpen = {
    editorId: activeEditorId,
    itemCount: items.length,
    timestamp: Date.now(),
};
```

This proves whether the open event reaches the Vue layer.

---

### Task 3: Verify Media Click -> Insert Event Dispatch

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`

- [ ] **Step 1: Normalize media source fields**

Build one resolver for image attributes:

```js
const src = media.url || media.public_url || media.path || '';
```

If `src` is empty, mark debug state and do not close the modal.

- [ ] **Step 2: Record card click and insert dispatch**

On media click or insert button:

```js
window.NewsTechDebugRichText.lastInsertEvent = {
    editorId: activeEditorId,
    imageAttributes,
    timestamp: Date.now(),
};
```

- [ ] **Step 3: Keep or simplify selection flow only if needed**

Preferred:
- keep the current explicit insert path if it proves correct

Fallback if selected-state is the problem:
- clicking a media card immediately dispatches insert and closes the modal

Only adopt that fallback after confirming the break point is in selected-state handling.

---

### Task 4: Harden Registry Lookup And Tiptap Insert Execution

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/app.js`

- [ ] **Step 1: Record registry state on insert**

When the insert event is received:

```js
window.NewsTechDebugRichText.lastInsertAttempt = {
    editorId,
    registryKeys: [...richTextEditorRegistry.keys()],
    hasEditor: richTextEditorRegistry.has(editorId),
    imageAttributes,
    timestamp: Date.now(),
};
```

- [ ] **Step 2: Verify the editor instance supports image insertion**

Before calling `setImage()`, ensure:
- the registry entry exists
- the editor object exists
- the image extension is loaded on that instance
- `src` is non-empty

- [ ] **Step 3: Record post-insert HTML and textarea sync**

After `setImage()`:

```js
window.NewsTechDebugRichText.lastEditorHtml = editor.getHTML();
window.NewsTechDebugRichText.lastTextareaValue = source.value;
```

If the editor HTML changes but textarea does not, the fix must target sync.

---

### Task 5: Ensure Textarea Sync And Save Path Stay Correct

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/app.js`
- Modify tests only if server-visible hooks change

- [ ] **Step 1: Force sync immediately after insert**

Keep:

```js
syncEditorSource(editor, source);
```

after every successful insert.

- [ ] **Step 2: Ensure no modal-close timing issue drops the update**

If needed:
- close the modal only after successful dispatch and local debug confirmation

---

### Task 6: Build / Cache Verification

**Commands:**

```bash
npm run build
php artisan optimize:clear
```

- [ ] **Step 1: Confirm `build-admin` manifest points to the latest JS asset**

Inspect:

```bash
cat public/build-admin/manifest.json
```

- [ ] **Step 2: Note browser cache guidance**

Include in final verification:
- hard refresh `Ctrl+Shift+R`
- confirm page source references the latest `build-admin` asset

---

### Task 7: Focused Tests And Regression

**Commands:**

```bash
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
php artisan test --compact tests/Feature/NewsTechPageModuleTest.php
php artisan test --compact tests/Feature/NewsTechMediaLibraryTest.php
php artisan test --compact tests/Feature/NewsTechFrontendArticleDetailTest.php
php artisan test --compact
```

- [ ] **Step 1: Keep server-side coverage green**

Focus on:
- editor markup hooks
- stored `<img>` HTML
- frontend rendered `<img>` HTML
- existing Featured Image picker coverage

- [ ] **Step 2: Run full regression**

Expected:
- no unrelated regressions from runtime-debug hardening

---

## Debug Questions This Plan Must Answer

1. Does clicking `Insert Image` dispatch the open event?
2. Does the Vue modal receive it?
3. Does the modal visibly open?
4. Does the media list exist?
5. Does clicking a media item dispatch an insert event?
6. Does Admin JS receive that insert event?
7. Does the registry contain the exact active editor id?
8. Does `setImage()` run?
9. Does editor HTML change?
10. Does textarea HTML change?

## Completion Criteria

- exact browser/runtime break point is identified
- fix is applied to that break point only
- image appears in the editor immediately after selection
- hidden textarea contains `<img>`
- save path still works
- frontend article detail still renders inline image
- build and full PHPUnit regression pass
