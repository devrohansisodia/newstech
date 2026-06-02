# Phase 5.3.1 Tiptap Image Support Design

**Goal:** Add inline image insertion to the reusable Tiptap editor using the existing Media Library flow while preserving the separate Article featured image field and improving frontend content spacing/rendering for rich article and page content.

**Architecture:** The reusable admin rich text editor remains Blade-owned for markup, but new interactive editor image behavior will move into a Bagisto-style Blade + Vue hybrid inside the Admin package. A dedicated editor image modal will reuse the existing media library and upload patterns, return a selected image URL and alt text to the active editor instance, and insert standard HTML image tags into editor content. Frontend article/page rendering will continue to pass through `RichTextContentRenderer`, which will be extended to allow safe image tags and preserve rich spacing through scoped frontend content styles.

**Tech Stack:** Laravel 12, Blade, Vue (Admin interactive layer), Vite, Tiptap OSS packages, `@tiptap/extension-image`, existing NewsTech Media library flow.

## Scope

- Add only the free/open-source `@tiptap/extension-image`.
- Keep Article featured image as a separate right-side field for card/hero/SEO usage.
- Add an `Insert Image` toolbar action to the reusable editor component.
- Use an editor-specific image modal built under `packages/NewsTech/Admin`.
- Reuse the existing media library listing, upload, and metadata patterns as much as practical.
- Support multiple inline images in one Article/Page content field.
- Improve frontend `.nt-prose` spacing and image rendering without adding the Tailwind Typography plugin.
- Extend `RichTextContentRenderer` to preserve safe image tags and safe attributes.

## Out Of Scope

- No paid/pro Tiptap features.
- No React.
- No new backend PHP dependencies.
- No full refactor of existing vanilla JS media picker behavior.
- No inline image captions, resizing UI, alignment controls, drag/drop upload, or page builder behavior.
- No change to database schema, route ownership, or existing featured image storage model.

## Approach Decision

### Recommended Approach

Use Blade + Vue for the new image insertion flow while keeping Tiptap as the editing engine. The editor will expose data hooks for a Vue-managed modal and editor instance registry. The modal will display the current media library, allow upload via the existing media endpoints, and insert a selected image into the active editor at the current cursor position as an HTML `<img>` tag with public URL and alt text.

### Alternatives Rejected

1. Extend the existing field-level media picker directly into the editor flow.
   This component is built around syncing one hidden field, not inserting multiple assets into rich content.

2. Use plain vanilla JS for the new image modal.
   Rejected to align with the corrected NewsTech standard for new interactive admin behavior.

3. Prompt for raw image URLs.
   Rejected because it bypasses the existing media library and does not satisfy the content workflow requirement.

## Design

### 1. Reusable Editor Component

The existing `x-newstech-admin::form.rich-text-editor` component remains the single entry point for Article and Page content editing.

Changes:
- Add an `Insert Image` toolbar button.
- Add component-level data hooks for:
  - editor instance identity
  - modal open action
  - image insertion target
- Keep the hidden/fallback textarea source field unchanged so server-side validation, old input, and storage remain compatible.
- Optionally accept a short helper message clarifying that inline images belong to article/page content while featured image remains separate.

### 2. Admin Interactive Layer

New interactivity will use Vue inside the Admin package asset bundle.

Planned structure:
- A small Vue app mounted only where editor image modal hooks exist.
- The Vue layer will:
  - track the currently active editor instance
  - open/close the editor image modal
  - switch between Media and Upload tabs
  - list/select images
  - upload new media through existing admin media endpoints
  - return selected `url` and `alt` back to the active Tiptap editor

Important constraint:
- Existing vanilla JS for media picker and library remains in place for current working features.
- This phase may share helper functions or endpoint formats conceptually, but should not attempt a broad rewrite.
- New editor image insertion behavior should be isolated rather than expanding the old vanilla pattern further.

### 3. Tiptap Image Insertion

The editor will add `@tiptap/extension-image`.

Behavior:
- Clicking `Insert Image` opens the editor image modal.
- Selecting an image inserts an `<img>` node at the current cursor position.
- Uploading a new image from the modal makes it available in the same modal flow, after which it can be selected and inserted.
- Inserted images use:
  - resolved public URL
  - media alt text if available
  - empty alt only if no alt text exists
- Multiple inline images are supported because each insertion targets the current editor selection rather than a hidden single-value field.

### 4. Article Featured Image Separation

The existing featured image field remains exactly separate from inline editor images.

UI clarification:
- Label remains `Featured Image`.
- Helper text becomes explicit:
  `Main image used for article cards, hero sections, and SEO/social sharing.`

Inline image separation:
- The editor toolbar button is labeled `Insert Image`.
- If additional helper text is needed, it should explain that this adds images inside the content body only.

### 5. Frontend Rendering And Spacing

Article and database-backed page content will continue to render through the scoped `.nt-prose` wrapper.

Spacing and rendering changes:
- Improve paragraph rhythm so separate paragraphs retain visible spacing.
- Preserve heading/list/blockquote spacing already introduced in Phase 5.3.
- Add responsive image styling:
  - `max-width: 100%`
  - `height: auto`
  - vertical spacing
  - subtle radius and border treatment consistent with the current visual language
- Ensure images never overflow the content column.

This remains scoped to:
- `packages/NewsTech/Frontend/Resources/views/articles/show.blade.php`
- `packages/NewsTech/Frontend/Resources/views/pages/dynamic.blade.php`
- frontend content-specific CSS only

### 6. RichTextContentRenderer Safety

`RichTextContentRenderer` will be extended, not replaced.

Allowed behavior:
- Keep safe `img` tags
- Preserve safe attributes such as:
  - `src`
  - `alt`
  - `title`
  - `width`
  - `height`

Blocked behavior remains:
- event handler attributes
- unsafe protocols such as `javascript:` and `data:`
- blocked tags like `script`, `iframe`, `object`, `embed`, `form`

Link safety remains unchanged:
- external links retain `target="_blank"` and `rel="noopener noreferrer"`
- unsafe links lose hazardous attributes

### 7. Files Expected To Change

Admin:
- `package.json`
- `package-lock.json`
- `packages/NewsTech/Admin/Resources/views/components/form/rich-text-editor.blade.php`
- `packages/NewsTech/Admin/Resources/assets/js/app.js`
- possibly new Vue editor image modal/component support files under `packages/NewsTech/Admin/Resources/assets/js/`
- `packages/NewsTech/Admin/Resources/assets/css/app.css`
- `packages/NewsTech/Admin/Resources/views/articles/_form.blade.php`
- `packages/NewsTech/Admin/Resources/views/pages/_form.blade.php`

Core / rendering:
- `packages/NewsTech/Core/Support/RichTextContentRenderer.php`

Frontend:
- `packages/NewsTech/Frontend/Resources/views/articles/show.blade.php`
- `packages/NewsTech/Frontend/Resources/views/pages/dynamic.blade.php`
- `packages/NewsTech/Frontend/Resources/assets/css/app.css`

Tests:
- `tests/Feature/NewsTechArticleModuleTest.php`
- `tests/Feature/NewsTechPageModuleTest.php`
- `tests/Feature/NewsTechFrontendArticleDetailTest.php`
- `tests/Feature/NewsTechFrontendDatabasePagesTest.php`
- plus any existing media/editor-related feature tests that need assertions extended

## Testing Strategy

Server-side assertions:
- Article editor toolbar renders `Insert Image`
- Page editor toolbar renders `Insert Image`
- Editor markup exposes modal/data hooks for inline image insertion
- Article and Page save HTML containing multiple `<img>` tags
- Frontend article/page output renders stored inline image tags
- Frontend article/page output includes scoped content classes for spacing and image presentation
- Featured image remains separately rendered in article views/cards where already covered by tests

Client/build verification:
- `npm run build`
- verify Admin bundle still compiles with Vue + Tiptap image extension

Focused test targets:
- Article module
- Page module
- Frontend article detail
- Frontend database pages
- Media library / picker tests that cover existing image flows

Full regression:
- full PHPUnit suite

## Risks And Mitigations

1. Risk: New editor modal conflicts with existing media picker behavior.
   Mitigation: keep the editor image modal isolated and scoped by dedicated hooks; do not retrofit the field-level media picker directly.

2. Risk: Rich content images render unsafely.
   Mitigation: extend `RichTextContentRenderer` conservatively and continue stripping unsafe protocols and event attributes.

3. Risk: Paragraph spacing still feels collapsed on frontend.
   Mitigation: adjust `.nt-prose` rules specifically for paragraph-to-paragraph spacing and image block spacing, then verify through frontend rendering tests.

4. Risk: Featured image vs inline image intent becomes unclear.
   Mitigation: update helper copy on the featured image field and clearly label the toolbar action as `Insert Image`.

## Completion Criteria

- Editor supports inserting multiple inline images from the media library
- Upload-and-then-select flow works inside the editor image modal
- Article featured image remains separate and unchanged in function
- Frontend article/page content displays paragraphs and inline images with improved spacing
- Safe image rendering is preserved through `RichTextContentRenderer`
- No new paid/pro features, React, or backend dependencies are introduced
- Full test suite remains green
