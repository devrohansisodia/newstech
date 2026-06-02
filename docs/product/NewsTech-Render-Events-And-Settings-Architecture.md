# NewsTech Render Events And Settings Architecture

## Purpose

Phase 6.0 adds two extension foundations before the advanced advertisement manager:

- Render events for frontend and admin blades
- Registry-driven admin settings groups/cards

The goal is to let future packages inject UI and settings without overriding core Blade files.

## Render Event Foundation

NewsTech now exposes a shared render event manager in `NewsTech\Core\Support\RenderEventManager`.

Use either of these APIs:

```php
newstech_view_render_event('frontend.homepage.top.before');
app(\NewsTech\Core\Support\RenderEventManager::class)->render('frontend.homepage.top.before');
```

Packages register output in their service providers:

```php
$renderEvents->register('frontend.homepage.top.before', fn (): string => '<div>...</div>');

$renderEvents->registerView('frontend.homepage.top.before', 'package::view', [
    'key' => 'value',
]);
```

If nothing is registered, render output is an empty safe string.

## Hook Naming Convention

Use dot-notated keys grouped by surface and page:

- `frontend.layout.*`
- `frontend.homepage.*`
- `frontend.article.show.*`
- `frontend.category.show.*`
- `frontend.tag.show.*`
- `frontend.author.show.*`
- `frontend.search.show.*`
- `frontend.listing.*`
- `frontend.account.dashboard.*`
- `admin.layout.*`
- `admin.sidebar.*`
- `admin.dashboard.*`
- `admin.settings.*`
- `admin.datagrid.*`

Prefer stable structural names such as `before`, `after`, `top`, `bottom`, `form.before`, `form.after`.

## Current Frontend Hooks

Key public hooks added in this phase:

- `frontend.layout.head.after`
- `frontend.layout.header.before|after`
- `frontend.layout.navigation.after`
- `frontend.layout.main.before|after`
- `frontend.layout.footer.before|after`
- `frontend.homepage.top.before|after`
- `frontend.homepage.sidebar.top|bottom|inline`
- `frontend.homepage.bottom`
- `frontend.article.show.top.before`
- `frontend.article.show.meta.after`
- `frontend.article.show.content.before|after`
- `frontend.article.show.comments.after`
- `frontend.article.show.reader_tools.before|after`
- `frontend.article.show.sidebar.top|bottom`
- `frontend.article.show.bottom`
- `frontend.listing.top|bottom`
- page-specific listing hooks for category, tag, author, and search
- `frontend.account.dashboard.top|bottom`

## Current Admin Hooks

Key admin hooks added in this phase:

- `admin.layout.head.after`
- `admin.layout.sidebar.before|after`
- `admin.layout.topbar.before|after`
- `admin.layout.main.before|after`
- `admin.sidebar.navigation.before|after`
- `admin.sidebar.group.before|after`
- `admin.dashboard.cards.before|after`
- `admin.settings.index.cards.before|after`
- `admin.settings.group.before|after`
- `admin.settings.{group}.before|after`
- `admin.settings.group.form.before|after`
- `admin.settings.{group}.form.before|after`
- `admin.datagrid.before|after`
- `admin.datagrid.{name}.before|after`

## Advertisement Package Usage

The frontend no longer depends on hard-coded ad slot Blade tags in layout and page templates.

Instead, `packages/NewsTech/Advertisement` now:

- registers placeholder views through render events
- owns the placeholder rendering view
- registers an `Advertisement Settings` card in admin settings

Current placeholder mappings:

- `header_leaderboard`
- `footer_banner`
- `homepage_top`
- `homepage_sidebar`
- `article_top`
- `article_inline`
- `article_sidebar`
- `listing_top`

This keeps placeholder ads working while leaving the frontend blades ready for a future advanced advertisement manager.

## Settings Group Registry

Admin settings are now managed through `NewsTech\Admin\Support\SettingsGroupManager`.

Each group can register:

- key
- title
- description
- icon
- sort order
- sections/fields
- validation rules/messages/attributes
- save callback
- summary text callback
- empty-state content

Current groups:

- `branding`
- `homepage`
- `comments`
- `advertisement`

Routes:

- `/admin/settings`
- `/admin/settings/{group}`

## How Packages Register Settings Groups

Packages can register cards/pages during provider boot:

```php
app(\NewsTech\Admin\Support\SettingsGroupManager::class)->register([
    'key' => 'advertisement',
    'title' => 'Advertisement Settings',
    'description' => '...',
    'sections' => [],
    'summary' => fn (): string => 'Placeholder slots enabled',
]);
```

If a package has no editable fields yet, it can still register a card and show an empty-state message.

## What Changed In Phase 6.0

- Added render event helper + manager
- Added frontend and admin hook points
- Moved placeholder advertisement injection behind render events
- Reworked admin settings into registry-driven cards and group pages
- Cleaned admin shell copy and visible topbar headings

## Remaining Limitations

- No advanced advertisement CRUD or slot targeting yet
- No advertisement database tables were added
- Settings save handling is registry-driven, but only current built-in groups define real persistence callbacks
- Render events are server-side Blade injection points, not a JS plugin system

## Future Improvements

- Add richer package-specific settings save handlers and custom form views
- Add per-hook documentation pages or a generated hook index
- Add priority conventions for package conflicts
- Build the advanced advertisement manager on top of these foundations
