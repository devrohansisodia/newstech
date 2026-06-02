# Phase 5.3.1 Tiptap Image Support Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add inline image insertion to the reusable Admin Tiptap editor using the Media Library flow, preserve featured image separation, and improve frontend rich-content spacing and image rendering.

**Architecture:** The reusable rich text editor stays Blade-owned for layout and field composition, while new interactive image insertion moves into a small Vue-powered Admin layer. Tiptap will gain the free Image extension, and a dedicated editor image modal will reuse the existing media endpoints and Media Library patterns to insert safe `<img>` tags into editor HTML. Frontend article and page content will continue to render through `RichTextContentRenderer`, which will be extended to preserve safe image tags and pair with scoped `.nt-prose` spacing/image rules.

**Tech Stack:** Laravel 12, Blade, Vue 3, Vite, `@vitejs/plugin-vue`, Tiptap OSS packages, `@tiptap/extension-image`, existing NewsTech Media Library endpoints.

---

### Task 1: Add Failing Tests For Inline Image Markup And Rendering

**Files:**
- Modify: `tests/Feature/NewsTechArticleModuleTest.php`
- Modify: `tests/Feature/NewsTechPageModuleTest.php`
- Modify: `tests/Feature/NewsTechFrontendArticleDetailTest.php`
- Modify: `tests/Feature/NewsTechFrontendDatabasePagesTest.php`
- Modify: `tests/Feature/NewsTechMediaLibraryTest.php`

- [ ] **Step 1: Add failing admin editor markup assertions**

Add tests/assertions for:
- `Insert Image` toolbar button text
- `data-rich-text-editor-action="image"`
- editor modal hook markup such as `data-editor-image-modal-open`
- editor media library hook markup such as `data-editor-image-library-item`
- featured image helper text clarifying separation from inline images

Example assertions to add:

```php
$response->assertSee('Insert Image');
$response->assertSee('data-rich-text-editor-action="image"', false);
$response->assertSee('data-editor-image-modal-open', false);
$response->assertSee('Main image used for article cards, hero sections, and SEO/social sharing.');
```

- [ ] **Step 2: Add failing storage assertions for multiple inline images**

Use HTML payloads that include more than one image:

```php
'content' => '<p>Intro</p><img src="/storage/newstech/media/top-story.webp" alt="Top story"><p>Middle</p><img src="/storage/newstech/media/end-story.webp" alt="End story">'
```

Assert the saved `content` column preserves the HTML string.

- [ ] **Step 3: Add failing frontend rendering assertions**

Add assertions for:
- rendered `<img>` tags
- responsive/content classes on the content wrapper
- preserved paragraph rhythm hooks

Example:

```php
$response->assertSee('<img src="/storage/newstech/media/top-story.webp" alt="Top story">', false);
$response->assertSee('nt-prose', false);
$response->assertSee('data-rich-content', false);
```

- [ ] **Step 4: Run targeted tests to verify RED**

Run:

```bash
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
php artisan test --compact tests/Feature/NewsTechPageModuleTest.php
php artisan test --compact tests/Feature/NewsTechFrontendArticleDetailTest.php
php artisan test --compact tests/Feature/NewsTechFrontendDatabasePagesTest.php
```

Expected:
- FAIL on missing `Insert Image` hooks and/or missing inline image rendering expectations.

### Task 2: Add Vue And Tiptap Image Dependencies

**Files:**
- Modify: `package.json`
- Modify: `package-lock.json`
- Modify: `packages/NewsTech/Admin/vite.config.js`
- Modify: `vite.config.js`

- [ ] **Step 1: Add required frontend dependencies**

Add:

```json
"dependencies": {
  "@tiptap/core": "...",
  "@tiptap/extension-image": "...",
  "@tiptap/extension-link": "...",
  "@tiptap/pm": "...",
  "@tiptap/starter-kit": "...",
  "vue": "^3.x"
},
"devDependencies": {
  "@vitejs/plugin-vue": "^5.x"
}
```

- [ ] **Step 2: Enable Vue in Vite config**

Update Admin and root Vite configs to include Vue plugin without changing route/view ownership.

Admin Vite shape:

```js
import vue from '@vitejs/plugin-vue';

plugins: [
    laravel({...}),
    vue(),
    tailwindcss(),
]
```

- [ ] **Step 3: Install dependencies**

Run:

```bash
npm install
```

Expected:
- dependency install succeeds without vulnerability blockers.

### Task 3: Build Vue-Powered Editor Image Modal

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/views/components/form/rich-text-editor.blade.php`
- Create: `packages/NewsTech/Admin/Resources/assets/js/components/rich-text-editor-image-modal.js`
- Modify: `packages/NewsTech/Admin/Resources/assets/js/app.js`
- Modify: `packages/NewsTech/Admin/Resources/assets/css/app.css`

- [ ] **Step 1: Extend the Blade component with image UI hooks**

Add:
- `Insert Image` toolbar button
- modal root markup
- media/upload/details panel hooks specific to editor image insertion
- hidden JSON/config attributes if needed

Example toolbar button:

```blade
<button
    type="button"
    class="nt-rich-text-editor-button"
    data-rich-text-editor-action="image"
    data-editor-image-modal-open
    aria-pressed="false"
>
    Insert Image
</button>
```

- [ ] **Step 2: Create a focused Vue component/module for image modal state**

Responsibilities:
- track active editor instance ID
- open/close modal
- manage tabs (`media` / `upload`)
- select library items
- upload new files via existing media endpoint
- insert chosen image into active Tiptap editor

Pseudo-structure:

```js
import { createApp } from 'vue';

createApp({
    data() {
        return {
            activeEditorId: null,
            isOpen: false,
            currentTab: 'media',
            mediaItems: [],
            selectedMedia: null,
        };
    },
    methods: {
        open(editorId) {},
        close() {},
        selectMedia(media) {},
        async uploadMedia() {},
        insertSelectedImage() {},
    },
}).mount('[data-editor-image-modal-app]');
```

- [ ] **Step 3: Register the Tiptap Image extension in the editor**

Update editor initialization to include:

```js
import Image from '@tiptap/extension-image';

extensions: [
    StarterKit.configure({ heading: { levels: [2, 3] } }),
    Link.configure({...}),
    Image,
]
```

- [ ] **Step 4: Add image insertion behavior at cursor position**

Insertion shape:

```js
editor.chain().focus().setImage({
    src: selectedMedia.url,
    alt: selectedMedia.alt_text || '',
    title: selectedMedia.original_name || '',
}).run();
```

- [ ] **Step 5: Keep existing textarea sync**

After insertion and all editor updates:

```js
source.value = editor.isEmpty ? '' : editor.getHTML();
```

- [ ] **Step 6: Ensure existing vanilla media picker behavior is not expanded or broken**

Constraint:
- do not refactor the field-level picker
- keep new image modal logic isolated under editor-specific hooks

### Task 4: Clarify Featured Image Separation

**Files:**
- Modify: `packages/NewsTech/Admin/Resources/views/articles/_form.blade.php`
- Modify: `tests/Feature/NewsTechArticleModuleTest.php`

- [ ] **Step 1: Update featured image helper text**

Set helper text to:

```blade
hint="Main image used for article cards, hero sections, and SEO/social sharing."
```

- [ ] **Step 2: Add optional editor helper clarification**

If useful, extend editor hint copy for article content to mention inline body images are separate from featured image.

- [ ] **Step 3: Run the article module test**

Run:

```bash
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
```

Expected:
- PASS for featured-image wording and editor image button assertions.

### Task 5: Extend RichTextContentRenderer For Safe Image Tags

**Files:**
- Modify: `packages/NewsTech/Core/Support/RichTextContentRenderer.php`
- Modify: `tests/Feature/NewsTechFrontendArticleDetailTest.php`
- Modify: `tests/Feature/NewsTechFrontendDatabasePagesTest.php`

- [ ] **Step 1: Preserve safe image attributes**

Allow safe `img` attributes such as:
- `src`
- `alt`
- `title`
- `width`
- `height`

Keep stripping:
- `on*` attributes
- unsafe `src`/`href` protocols

- [ ] **Step 2: Keep link hardening intact**

Do not regress current link behavior:

```php
if ($this->isExternalHttpLink($href)) {
    $anchor->setAttribute('target', '_blank');
    $anchor->setAttribute('rel', 'noopener noreferrer');
}
```

- [ ] **Step 3: Optionally add image-specific sanitization helper**

Example structure:

```php
if ($tagName === 'img') {
    $this->sanitizeImage($node);
}
```

- [ ] **Step 4: Run focused frontend rendering tests**

Run:

```bash
php artisan test --compact tests/Feature/NewsTechFrontendArticleDetailTest.php
php artisan test --compact tests/Feature/NewsTechFrontendDatabasePagesTest.php
```

Expected:
- PASS with `<img>` rendering preserved and unsafe attributes removed.

### Task 6: Improve Frontend Rich Content Spacing And Image Styles

**Files:**
- Modify: `packages/NewsTech/Frontend/Resources/views/articles/show.blade.php`
- Modify: `packages/NewsTech/Frontend/Resources/views/pages/dynamic.blade.php`
- Modify: `packages/NewsTech/Frontend/Resources/assets/css/app.css`

- [ ] **Step 1: Add a stable content wrapper hook**

Wrap rendered content with a specific attribute/class:

```blade
<div class="nt-prose sm:text-lg" data-rich-content>
    {!! app(...)->render($article->content) !!}
</div>
```

- [ ] **Step 2: Improve paragraph rhythm**

Update CSS so paragraphs preserve visible spacing:

```css
.nt-prose p + p {
    margin-top: 1.25rem;
}
```

- [ ] **Step 3: Add responsive inline image styling**

Add:

```css
.nt-prose img {
    border-radius: 1.25rem;
    display: block;
    height: auto;
    margin: 1.5rem 0;
    max-width: 100%;
}
```

- [ ] **Step 4: Preserve lists, headings, blockquotes, and links**

Do not remove existing `.nt-prose` rules; extend them only where needed.

### Task 7: Verify Media Flow And Impacted Suites

**Files:**
- Test only

- [ ] **Step 1: Run media and editor-adjacent tests**

Run:

```bash
php artisan test --compact tests/Feature/NewsTechMediaLibraryTest.php
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
php artisan test --compact tests/Feature/NewsTechPageModuleTest.php
php artisan test --compact tests/Feature/NewsTechFrontendArticleDetailTest.php
php artisan test --compact tests/Feature/NewsTechFrontendDatabasePagesTest.php
```

Expected:
- PASS for image insertion markup/storage/rendering and no regression in media flows.

- [ ] **Step 2: Build the assets**

Run:

```bash
npm run build
```

Expected:
- Admin and Frontend Vite builds pass with Vue and Tiptap image support.

- [ ] **Step 3: Format changed PHP/test files**

Run:

```bash
vendor/bin/pint packages/NewsTech/Core/Support/RichTextContentRenderer.php tests/Feature/NewsTechArticleModuleTest.php tests/Feature/NewsTechPageModuleTest.php tests/Feature/NewsTechFrontendArticleDetailTest.php tests/Feature/NewsTechFrontendDatabasePagesTest.php --format agent
```

Expected:
- Pint passes.

### Task 8: Full Regression Verification

**Files:**
- Test only

- [ ] **Step 1: Run full PHPUnit suite**

Run:

```bash
php artisan test --compact
```

Expected:
- Full suite passes.

- [ ] **Step 2: Capture final verification summary**

Record:
- dependencies added
- image insertion support verified
- featured image separation preserved
- frontend paragraph/image spacing improved
- full suite result

### Task 9: Optional Small Documentation Touch

**Files:**
- Modify: `packages/NewsTech/Admin/details.md`

- [ ] **Step 1: Update Admin package note if needed**

Add one concise note that the Admin bundle now includes Vue-powered rich text image insertion and requires rebuilding assets after dependency changes.

- [ ] **Step 2: Re-run the impacted article/page/editor tests if docs are bundled into build-only changes**

Run:

```bash
php artisan test --compact tests/Feature/NewsTechArticleModuleTest.php
php artisan test --compact tests/Feature/NewsTechPageModuleTest.php
```

Expected:
- PASS.
