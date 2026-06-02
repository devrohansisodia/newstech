# Phase 5.3.4 Browser Inline Image Runtime Debug Design

**Goal:** Find the exact browser/runtime break point in the Tiptap inline image insertion flow and fix it so `Insert Image` works in the real browser, not just in tests or inferred code-path review.

**Problem Summary:** Previous phases established a global Admin-owned Vue modal, open/insert events, an editor registry, and passing PHPUnit/build verification. However, the user’s real browser still reports that inline image insertion does not work on the Article editor. That browser result is the source of truth for this phase.

**Approved Direction:** Keep the current global modal architecture, add safe runtime instrumentation, trace the actual browser path end-to-end, and fix only the broken link in that path. Do not broaden scope into feature work or editor rewrites.

## Scope

- Diagnose the real browser insert flow for the Article rich text editor.
- Add safe, temporary or guarded debug instrumentation where needed.
- Verify and fix the full path:
  1. `Insert Image` click dispatches the open event
  2. modal receives the open event
  3. modal opens
  4. media list is available
  5. media click dispatches insert event
  6. insert event is received by Admin JS
  7. editor registry contains the active editor id
  8. `setImage()` executes on the correct editor
  9. editor HTML updates
  10. hidden textarea sync updates with `<img>`
- Keep the existing field-level Featured Image picker untouched.

## Out Of Scope

- No new editor features.
- No schema changes.
- No dependency changes.
- No broad editor refactor.
- No `packages/Webkul/*` changes.

## Runtime Debug Strategy

### 1. Safe Debug Surfaces

The runtime debug pass may expose the following safe surfaces:

- `window.NewsTechRichTextEditors`
- `window.NewsTechDebugRichText`
- modal root attributes such as:
  - `data-state`
  - `data-active-editor-id`
- editor registry snapshot keyed by textarea/editor id

Any console logging must be:

- guarded behind an explicit debug flag, or
- removed before finalizing if too noisy

The preferred final state is data hooks and lightweight `window` debug objects rather than noisy console output.

### 2. Browser Truth Over Inference

This phase should not stop at “the code appears correct”.

The implementation must answer, in the actual runtime path:

- whether `newstech:rich-text-image-picker:open` fires
- whether the Vue modal receives it
- whether the modal becomes visibly open
- whether clicking a media item dispatches `newstech:rich-text-image-picker:insert`
- whether the editor registry contains the exact active editor id at that moment
- whether image attributes contain a non-empty resolved `src`
- whether `editor.chain().focus().setImage(...)` actually runs
- whether the editor DOM changes
- whether the hidden textarea gets updated HTML

### 3. Stable Editor Registry

The current approved direction remains valid:

- keep a stable editor registry in Admin JS
- key it by editor/textarea id
- use modal state to store `activeEditorId`
- avoid relying on reactive callback storage if that proves unstable

If the current modal state is still too indirect, the fix should harden the flow around:

- `editorId`
- normalized image attributes
- one insert event channel
- one sync step after image insertion

### 4. Media Object Normalization

The modal must support image source resolution robustly.

Priority:

1. `url`
2. `public_url` if present
3. derived URL from `path` only if needed and safe

The final insert payload must not pass an empty or undefined `src`.

### 5. Fallback Simplification

If debugging proves that selected-media state is the unstable point, an approved fallback simplification is:

- clicking a media card directly inserts the image into the active editor
- modal closes immediately after insertion

This is allowed only after proving the current selection-state path is the failure point.

## Likely Failure Points To Prove Or Eliminate

- stale browser cache still loading an old `build-admin` asset
- page referencing an outdated admin asset file despite a rebuilt manifest
- open or insert event never reaching its listener
- modal open state changing internally but not visibly rendering
- media card click selecting only, not inserting
- mismatched image fields (`url` vs `public_url` vs `path`)
- wrong editor id sent to the registry lookup
- registry not populated or overwritten at runtime
- `setImage()` firing against the wrong editor or before focus/selection is valid
- textarea sync not running after image insertion

## Files Expected To Change

- `packages/NewsTech/Admin/Resources/assets/js/app.js`
- `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`
- `packages/NewsTech/Admin/Resources/views/components/layouts/app.blade.php`
- possibly `packages/NewsTech/Admin/Resources/views/components/form/rich-text-editor.blade.php` only if stronger runtime ids/hooks are needed
- targeted Article/Page/Frontend tests if server-visible hooks are updated

## Verification Strategy

### Build / cache verification

- `npm run build`
- inspect `public/build-admin/manifest.json`
- if needed run `php artisan optimize:clear`
- if build cache is suspected, instruct the user to hard refresh:
  - `Ctrl+Shift+R`

### Focused tests

- Article module
- Page module
- Media library
- Frontend article detail

### Full regression

- full PHPUnit suite

### Browser/runtime verification

If real browser automation is not available in this environment, the fix must still leave exact DevTools checks for the user:

- check `window.NewsTechDebugRichText`
- check modal root `data-state`
- check `data-active-editor-id`
- check registry contents
- check hidden textarea value after insertion

## Completion Criteria

- `Insert Image` opens the modal in the real browser
- clicking a media image inserts it into the active editor
- inserted image is visible in the editor immediately
- hidden textarea contains `<img>` before submit
- Article save stores the inline image
- frontend article detail renders the inline image
- Featured Image remains separate and working
