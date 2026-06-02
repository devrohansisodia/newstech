# Phase 5.3.2 Tiptap Insert Image Runtime Fix Implementation Plan

> **For agentic workers:** Execute this as a narrow runtime-fix pass only. Do not broaden scope into editor refactors or new features.

**Goal:** Fix the silent browser bug where clicking `Insert Image` in the Admin rich text editor does not open the image modal, by moving the image modal to one stable global Admin-owned Vue mount and routing editor context through a single global event.

**Architecture:** The Tiptap editor remains unchanged as the editing engine. The runtime fix removes per-editor Vue modal mounts from the rich text component, adds one global modal mount in the shared Admin layout, and switches the open event/listener contract to a single `window`-level channel carrying the active editor insertion context.

**Tech Stack:** Laravel 12, Blade, Vue 3, Vite, existing Admin JS bundle, existing `@tiptap/extension-image`, existing Media endpoints.

---

### Task 1: Add Failing Markup Assertions For Global Modal Wiring

**Files:**
- Modify: `tests/Feature/NewsTechArticleModuleTest.php`
- Modify: `tests/Feature/NewsTechPageModuleTest.php`

- [ ] **Step 1: Assert the editor button hook still exists**

Keep or add assertions for:

```php
$response->assertSee('data-rich-text-editor-action="image"', false);
$response->assertSee('data-rich-text-editor-image-picker-open', false);
```

- [ ] **Step 2: Add assertions for one global modal root**

Assert rendered Admin pages include a stable layout-level modal root hook, for example:

```php
$response->assertSee('data-editor-image-modal-root', false);
```

Also assert the old per-editor modal root hook is gone if you rename/remove it:

```php
$response->assertDontSee('data-rich-text-editor-image-picker-root', false);
```

- [ ] **Step 3: Run focused tests to confirm RED if markup is not yet aligned**

Run:

```bash
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
php artisan test --compact tests/Feature/NewsTechPageModuleTest.php
```

---

### Task 2: Move The Modal Mount To The Shared Admin Layout

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/views/components/layouts/app.blade.php`
- Modify: `packages/NewsTech/Admin/Resources/views/components/form/rich-text-editor.blade.php`

- [ ] **Step 1: Add one global modal root to the Admin layout**

Render one stable root near the end of the Admin layout body content.

Expected responsibilities:
- one mount point only
- data hooks for Vue bootstrapping
- serialized config payload for:
  - upload endpoint
  - CSRF token
  - initial image library items

- [ ] **Step 2: Remove per-editor Vue modal roots from the editor component**

The rich text field should keep:
- the toolbar button
- editor surface
- textarea fallback/source

It should no longer render a dedicated Vue modal app root per field.

---

### Task 3: Switch From Local Listener Wiring To One Global Event Channel

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/app.js`
- Modify: `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`

- [ ] **Step 1: Keep the editor registry but change the open dispatch target**

On `Insert Image`, dispatch:

```js
window.dispatchEvent(new CustomEvent('newstech:rich-text-image-picker:open', {
    detail: {
        insertImage: (attributes) => {
            editor.chain().focus().setImage(attributes).run();
        },
    },
}));
```

This removes dependency on dispatching to a field-local element.

- [ ] **Step 2: Mount the Vue modal exactly once**

In `app.js`:
- query the layout-level modal root
- guard against missing root
- guard against double-mount
- mount the Vue component once

- [ ] **Step 3: Register one `window` listener inside the Vue component**

In `RichTextEditorImageModal.vue`:
- listen for `newstech:rich-text-image-picker:open` on `window`
- store the active insertion callback/context from `event.detail`
- set `isOpen = true`
- reset tabs/status state

- [ ] **Step 4: Remove old owner-element listener assumptions**

Eliminate or replace:
- `ownerElement` prop dependency
- per-editor `addEventListener` / `removeEventListener` calls

The component should no longer depend on being mounted inside an editor field subtree.

---

### Task 4: Add A Browser-Visible Open-State Hook

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`

- [ ] **Step 1: Expose open-state in DOM**

When the modal is open, render a stable runtime hook such as:

```html
<div data-editor-image-modal="open" ...>
```

When closed:
- either remove the element with `v-if`
- or omit/change the attribute

- [ ] **Step 2: Keep visibility CSS straightforward**

Ensure the open-state path does not keep any `hidden` class on the modal container and that the overlay remains:
- `fixed`
- full-screen
- above the page UI

---

### Task 5: Verify Image Insertion Still Uses The Active Editor

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/assets/js/app.js`
- Modify: `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`

- [ ] **Step 1: Preserve current insertion behavior**

When an image is selected:

```js
insertImage({
    src: selectedMedia.url,
    alt: selectedMedia.alt_text || selectedMedia.original_name || selectedMedia.filename,
    title: selectedMedia.caption || undefined,
});
```

- [ ] **Step 2: Confirm multiple editors remain safe**

The currently active editor must be whichever toolbar last opened the modal. No global shared editor mutation beyond the open-session callback/context.

---

### Task 6: Build And Focused Verification

**Commands:**

```bash
npm run build
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
php artisan test --compact tests/Feature/NewsTechPageModuleTest.php
```

- [ ] **Step 1: Confirm build-admin output refreshes**

Check:
- `public/build-admin/manifest.json`
- updated Admin JS asset name if changed

- [ ] **Step 2: Confirm focused tests pass**

Expected:
- image button markup still present
- global modal root present
- no per-editor modal dependency remains

---

### Task 7: Impacted Suite And Regression

**Commands:**

```bash
php artisan test --compact tests/Feature/NewsTechMediaLibraryTest.php
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
php artisan test --compact tests/Feature/NewsTechPageModuleTest.php
php artisan test --compact
```

- [ ] **Step 1: Verify existing field-level media picker remains intact**

Media library and featured image picker tests must still pass.

- [ ] **Step 2: Verify full suite if focused pass is green**

Expected:
- no regressions from the layout-level mount move

---

## Expected Root Cause To Confirm During Implementation

The original runtime bug is expected to come from the per-editor Vue modal mount/listener lifecycle, not from the toolbar click dispatch itself, build output, or media endpoints. The implementation should explicitly verify that the final fix works because:

- the Vue modal mounts once at layout level
- the listener is registered once on `window`
- the open event is dispatched to that same global channel
- modal visibility state changes in the DOM when the button is clicked

## Completion Criteria

- `Insert Image` opens the modal reliably on Article create/update
- modal shows `Media` and `Upload`
- selected image inserts into the active editor
- Article save still works
- field-level featured image picker still works
- `npm run build` passes
- focused tests pass
- full PHPUnit suite passes
