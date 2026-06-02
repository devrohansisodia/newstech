# Phase 5.3.2 Tiptap Insert Image Runtime Fix Design

**Goal:** Fix the Phase 5.3.1 runtime bug where clicking `Insert Image` in the Admin rich text editor does nothing, while keeping the existing editor, Tiptap image insertion behavior, and field-level featured image picker unchanged.

**Problem Summary:** The current image modal wiring uses a Vue app mounted inside each rich text editor field. The toolbar click path dispatches the image-open event correctly, and the Admin bundle is loading, but the modal open behavior is still unreliable in the browser. The approved direction is to stop tying the image picker modal lifecycle to each field subtree and instead use one stable Admin-owned Vue mount in the shared layout.

**Approved Direction:** Move the editor image picker modal to a single global Vue mount inside the Admin layout, dispatch a global custom event from editor toolbars with active editor context, and let the global modal open and insert into the active editor instance.

## Scope

- Fix only the `Insert Image` runtime behavior.
- Replace per-editor Vue image modal mounts with one global Admin-owned modal mount.
- Keep the existing Tiptap editor and `@tiptap/extension-image`.
- Keep the existing field-level media picker untouched.
- Keep route ownership, media endpoints, and content storage format unchanged.
- Add a browser-visible modal state hook such as `data-editor-image-modal="open"` while the modal is open.

## Out Of Scope

- No new editor features.
- No refactor of the whole editor.
- No direct imperative vanilla-JS fallback modal.
- No new dependencies.
- No changes to Article/Page content schema or frontend rendering behavior beyond what is necessary for the modal bugfix.

## Root Cause Direction

The most likely failure point is not the toolbar click dispatch itself, but the lifecycle and placement of the Vue modal app:

- the modal app is mounted inside each rich text field rather than at a stable layout-level location
- the listener is attached per editor instance instead of through one stable global listener
- event target and listener lifetime are coupled to the same field subtree

Even without a visible console error, this can produce a silent failure mode where the toolbar dispatch succeeds but no active Vue listener updates modal state in the browser.

## Design

### 1. Global Admin Modal Mount

Render one global modal mount point in the shared Admin layout component under `packages/NewsTech/Admin/Resources/views/components/layouts/app.blade.php`.

Responsibilities:

- exist on all Admin pages that load the Admin JS bundle
- provide one stable Vue mount target
- expose initial image picker config from Blade:
  - media upload endpoint
  - CSRF token
  - initial image library list
- remain independent from any individual editor field

This replaces the current per-editor modal root in `x-newstech-admin::form.rich-text-editor`.

### 2. Global Event Contract

The toolbar `Insert Image` action will dispatch one global event on `window`:

- event name: `newstech:rich-text-image-picker:open`
- payload includes:
  - active editor element or unique editor id
  - insertion callback or resolvable editor lookup key

The global Vue modal listens once on mount using the exact same event name.

Why `window`:

- avoids element-specific listener registration mismatch
- removes any dependency on field subtree mount timing
- works reliably with multiple editor instances on one page

### 3. Editor Context Handling

Each editor instance still needs a way to insert the selected image back into the correct Tiptap instance.

Approved pattern:

- keep the existing editor instance registry in Admin JS
- on `Insert Image`, dispatch the global open event with the active editor context
- the global modal stores the active editor callback/context for the current open session
- on image selection, the modal calls that context to insert the image via `setImage`

This keeps insertion behavior unchanged once the modal opens.

### 4. Vue Modal Lifecycle

The Vue modal should:

- mount exactly once
- register exactly one global listener on `window`
- track:
  - `isOpen`
  - current tab (`media` or `upload`)
  - selected media item
  - active editor insertion callback/context
- unregister the global listener on unmount

Open-state visibility:

- when open, the modal root should expose `data-editor-image-modal="open"`
- when closed, the attribute may be absent or set to a closed state

This creates a reliable browser-visible hook for runtime verification and tests where practical.

### 5. Modal Placement And Visibility

The modal remains a fixed overlay teleported or rendered at layout level with:

- stable `fixed inset-0`
- high `z-index`
- no dependency on hidden form-section containers

Verification target:

- modal state change must be observable in DOM when `Insert Image` is clicked
- no CSS hidden class should remain on the open state path

### 6. Blade And JS Ownership

Ownership remains consistent with NewsTech rules:

- Admin Blade/layout/modal markup stays under `packages/NewsTech/Admin`
- Admin interactive behavior stays under `packages/NewsTech/Admin/Resources/assets/js`
- existing Media backend/domain logic remains under `packages/NewsTech/Media`

## Files Expected To Change

- `packages/NewsTech/Admin/Resources/views/components/layouts/app.blade.php`
- `packages/NewsTech/Admin/Resources/views/components/form/rich-text-editor.blade.php`
- `packages/NewsTech/Admin/Resources/assets/js/app.js`
- `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`
- `tests/Feature/NewsTechArticleModuleTest.php`
- `tests/Feature/NewsTechPageModuleTest.php`
- optionally other focused Admin/editor tests if a layout-level mount assertion is needed

## Testing Strategy

### Server-side / markup assertions

- Article form includes `Insert Image` button hook
- Article form no longer depends on per-editor Vue modal roots
- Admin layout or rendered page includes one global image modal mount root
- modal root includes the expected data hooks/config markers

### Build verification

- `npm run build`
- confirm updated `build-admin` assets are generated

### Focused PHPUnit

- Article module test
- Page module test
- any existing editor-related tests touched by the layout/root move

### Full regression

- full PHPUnit suite if focused verification is green

## Completion Criteria

- Clicking `Insert Image` opens the modal in browser runtime
- Modal exposes `Media` and `Upload` tabs
- Selected image inserts into the currently active editor
- Article save still works
- Existing field-level featured image picker still works
- No new dependencies or feature creep are introduced
