# Media Package

Owns backend media-library domain logic for NewsTech, including the media table, model, repository, upload/update requests, library manager support classes, and ACL/menu configuration for admin access.

## Responsibilities

- Store and manage reusable media records
- Provide backend services used by admin media screens and picker workflows
- Own media migrations, factories, repositories, support classes, and package config

## Ownership

- Routes/views/assets: does not own active admin or frontend route files, Blade views, or Vite assets
- UI surface: admin media screens live under `packages/NewsTech/Admin`
- Dependencies: relies on `NewsTech/Admin` for admin presentation and `NewsTech/Core` for shared support

## Should Not Own

- Frontend pages or components
- Admin Blade views or assets
- Standalone package-local route files unless explicitly approved in a future phase
