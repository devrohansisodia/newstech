# NewsTech Admin Interactive UI Blade + Vue Refactor Plan

## Phase Context

This planning pass follows Phase 5.3.x, where the Admin rich text editor, link support, and inline image insertion were completed and browser-verified. The Admin interactive stack is now functional, but it uses a mixed approach:

- Blade for layout and markup
- Vue for the global rich text image modal
- Vanilla DOM/event code in `packages/NewsTech/Admin/Resources/assets/js/app.js` for most other stateful behavior

The goal of future phases is to align Admin interactivity with the corrected NewsTech standard: Bagisto-style Blade + Vue hybrid, with Blade responsible for page structure and Vue responsible for stateful UI behavior.

## Current Interactive UI Inventory

### Already Blade + Vue

1. Global inline image modal for the rich text editor
   - Files:
     - `packages/NewsTech/Admin/Resources/assets/js/components/RichTextEditorImageModal.vue`
     - `packages/NewsTech/Admin/Resources/views/components/layouts/app.blade.php`
   - Current role:
     - receives open events from editor toolbars
     - manages modal state, media tab state, upload flow, detail editing, and image insert dispatch
   - Risk:
     - Medium
   - Notes:
     - This is the current reference implementation for future Admin interactive UI.

### Blade-Only and Safe to Leave Static

1. Admin shell layout
   - `packages/NewsTech/Admin/Resources/views/components/layouts/app.blade.php`
2. Core shell and Vite handoff
   - `packages/NewsTech/Core/Resources/views/components/layouts/app.blade.php`
3. Admin CRUD form sections and field wrappers
   - `packages/NewsTech/Admin/Resources/views/components/form/*.blade.php`
4. Datagrid presentation layer
   - `packages/NewsTech/Admin/Resources/views/components/datagrid*.blade.php`
5. Admin page composition views
   - article/page/settings/index views

These are primarily structural/presentational and do not need Vue by default unless they later gain real client-side state.

### Still Driven by Focused Bootstrap / Plain JavaScript

1. Rich text editor bootstrapping
   - file: `packages/NewsTech/Admin/Resources/assets/js/rich-text-editor.js`
   - responsibilities:
     - Tiptap editor creation
     - toolbar click handling
     - textarea sync on transaction and submit
     - editor registry management
   - risk:
     - Medium
   - note:
     - Tiptap itself is working, so this should be stabilized, not rewritten first.

2. File input preview helper
   - file: `packages/NewsTech/Admin/Resources/assets/js/admin-forms.js`
   - responsibilities:
     - local image preview for standard file inputs
   - risk:
     - Low
   - note:
     - This is small DOM glue and does not justify a Vue component yet.

3. Media picker field component
   - files:
     - `packages/NewsTech/Admin/Resources/views/components/form/media-picker.blade.php`
     - `packages/NewsTech/Admin/Resources/assets/js/components/MediaPicker.vue`
   - responsibilities:
     - modal open/close
     - tab switching
     - library item selection
     - details panel population
     - upload flow
     - hidden input + preview sync
   - risk:
     - High
   - note:
     - The Blade component carries a large amount of state via `data-*` attributes and paired DOM ids.

4. Media library page interactions
   - files:
     - `packages/NewsTech/Admin/Resources/views/media/index.blade.php`
     - `packages/NewsTech/Admin/Resources/assets/js/components/MediaLibrary.vue`
   - responsibilities:
     - upload modal
     - detail modal
     - dynamic upload results
     - library card hydration from AJAX responses
   - risk:
     - High
   - note:
     - This duplicates several media behaviors already present in the inline picker and rich text modal.

5. Vue mount bootstrap
   - files:
     - `packages/NewsTech/Admin/Resources/assets/js/app.js`
     - `packages/NewsTech/Admin/Resources/assets/js/vue-mount.js`
   - responsibilities:
     - mount per-page or per-field Admin Vue roots from Blade config
   - risk:
     - Low
   - note:
     - This is acceptable bootstrap glue and should remain small.

## Existing Vue Usage Review

### `RichTextEditorImageModal.vue`

This component already fits the target direction better than the rest of the Admin stack:

- Blade supplies mount point and JSON configuration
- Vue owns modal visibility and tab state
- Vue handles upload/update requests and dispatches a normalized insert event
- `app.js` stays responsible only for editor-instance commands and source syncing

This separation is the correct future pattern:

- Blade:
  - layout
  - mount root
  - route/config payload
  - static structure
- Vue:
  - modal state
  - async interactions
  - tab state
  - selected item state
- Admin JS bootstrap:
  - only shared low-level integrations that must touch third-party instances such as Tiptap

## Current Admin JS Risk Summary

### `packages/NewsTech/Admin/Resources/assets/js/app.js`

Current size after Phases 5.5.1 to 5.5.4:

- bootstrap/orchestrator only
- delegates rich text editor runtime, Vue mounts, and file preview helper to focused modules

Current mixed concerns:

- Vue mount orchestration
- submit-time rich text sync
- focused Admin bootstrap only

Main problem:

Most of the earlier high-risk concerns have now been moved out of `app.js`. The remaining risk is mainly around keeping bootstrap responsibilities small and preventing new stateful UI from being reintroduced there.

## Bagisto-Style Blade + Vue Cooperation Rules

Use these rules for future Admin interactive UI work:

1. Blade owns page layout, slots, field composition, and server-rendered fallbacks.
2. Vue owns modal visibility, tabs, selected-item state, async upload/update flows, and dynamic details panels.
3. Shared low-level bridges may stay in Admin JS only when they must talk directly to third-party instances such as Tiptap.
4. Do not add new stateful DOM controllers in plain JS when the interaction is modal-based, tabbed, async, or selection-driven.
5. Prefer one stable Vue mount per interaction domain instead of many ad hoc per-field controllers.
6. Keep Admin interactivity under `packages/NewsTech/Admin`.
7. Keep frontend public pages Blade-first unless a later approved phase requires frontend interactivity.

## Recommended Refactor Order

### Completed: Media Picker Vue Refactor

Why first:

- It has the highest concentration of stateful behavior.
- It is reused by articles, settings, and future modules.
- It duplicates patterns already solved once in the rich text image modal.
- It is the clearest place to reduce `data-*` and DOM-id coupling.

Target outcome:

- Blade keeps the field shell, hidden input, label, error, and preview container.
- Vue owns the modal, tabs, selected media, upload form, details panel, and selection commit.

### Completed: Media Library Vue Refactor

Why second:

- It overlaps heavily with picker behavior.
- It can likely reuse the same media item, upload, and details-panel logic extracted from the picker refactor.
- It will reduce duplicated modal/update/upload logic across Admin.

Target outcome:

- Blade keeps page framing and server-rendered initial data.
- Vue owns upload modal, detail modal, selected media state, and optimistic DOM updates.

### Completed: Rich Text Editor Vue Stabilization

Why third:

- The editor already works and has real browser validation.
- It still relies on DOM-managed toolbar and instance registry logic.
- It is lower risk to revisit only after media components are consistent.

Target outcome:

- keep Tiptap instance bridging in JS where necessary
- consider moving toolbar state and image-open actions into a dedicated Vue wrapper only if the gains are clear
- avoid destabilizing the current editor foundation during media refactors

### Completed: Admin Form Dynamic UI Cleanup

Why fourth:

- Most current forms are static and server-rendered.
- Dynamic cleanup should happen after shared interactive primitives are standardized.

Target outcome:

- identify remaining dynamic widgets, tabsets, modal triggers, previews, or conditional sections
- migrate only the parts that still benefit from Vue

## Proposed Implementation Phases

### Phase 5.5.1: Media Picker Vue Refactor

- replace DOM-driven picker modal logic with a dedicated Vue component
- preserve current hidden input API so consuming forms do not change
- preserve existing routes, upload endpoints, and saved values

Status: completed

### Phase 5.5.2: Media Library Vue Refactor

- convert upload/detail modal workflow to Vue
- extract reusable media list/detail/upload subcomponents if justified
- reduce duplicate rendering helpers in `app.js`

Status: completed

### Phase 5.5.3: Rich Text Editor Vue Stabilization

- review whether toolbar state should stay in JS or move to a Vue wrapper
- keep the current working Tiptap foundation unless there is a clear reliability benefit
- reduce coupling between modal events and raw DOM selectors where practical

Status: completed

### Phase 5.5.4: Admin Form Dynamic UI Cleanup

- audit remaining Admin dynamic interactions
- move only stateful controls into Vue
- keep simple form controls and datagrid presentation Blade-only

Status: completed

## Current End State

1. Stateful Admin interactions now use Vue where appropriate:
   - `MediaPicker.vue`
   - `MediaLibrary.vue`
   - `RichTextEditorImageModal.vue`
2. Rich text editor instance management lives in a focused JS module:
   - `rich-text-editor.js`
3. `app.js` is now primarily orchestration:
   - bootstrap calls
   - Vue root mounting via `vue-mount.js`
   - submit-time rich text sync
4. Remaining plain JS is intentionally narrow:
   - file input preview glue in `admin-forms.js`
   - third-party editor bridge logic in `rich-text-editor.js`

## Remaining Limitations

1. Rich text editor toolbar behavior still depends on Tiptap DOM integration rather than a dedicated Vue wrapper.
2. File input preview remains plain JS because it is lightweight and local.
3. Pagination in the media library remains Blade-rendered HTML passed into Vue rather than a fully client-driven paginator.

## What Should Remain As-Is For Now

1. Datagrid components
   - currently presentational and stable
2. Static Blade form components
   - input, textarea, select, toggle, actions, section
3. Core/Admin layout ownership
   - already aligned with package ownership rules
4. Working Tiptap runtime behavior
   - do not rewrite first just because it uses JS under the hood
5. Existing field-level API for media-backed form inputs
   - keep stable until the Vue picker replacement is ready

## Deferred / Lower Priority Items

1. File preview-only micro-interactions
2. Any cosmetic Vue migration that does not reduce state complexity
3. Frontend interaction refactors
   - not part of this Admin plan

## Summary Recommendation

NewsTech should not do a broad “convert all Admin JS to Vue” rewrite. The safer path is:

1. standardize the media picker first
2. refactor the media library second
3. stabilize the rich text editor third
4. clean up residual Admin dynamic UI last

That order follows current risk, reuse, and package ownership boundaries while preserving the working editor/media foundation already verified in the browser.
