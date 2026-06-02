# Comment Package

Owns backend article-comment domain logic for NewsTech, including guest and reader-linked comment persistence, threaded reply relationships, moderation statuses, anti-spam evaluation, validation requests, repositories, migrations, factories, and admin menu / ACL configuration.

## Responsibilities

- Store guest comments for published articles
- Store reader-linked comments for published articles
- Keep new comments pending by default
- Support one-level threaded replies through `parent_id`
- Support approve, reject, and delete moderation actions
- Expose repository methods for frontend-approved comment rendering and admin moderation

## Ownership

- Routes/views/assets: does not own active admin or frontend route files, Blade views, or Vite assets
- UI surface: admin moderation screens live under `packages/NewsTech/Admin`; article comment UI lives under `packages/NewsTech/Frontend`
- Dependencies: relies on `NewsTech/Article` for article ownership and `NewsTech/Core` for shared repository/model helpers

## Should Not Own

- Reader authentication
- Comment likes or reactions
- Admin/frontend package-local Blade views
