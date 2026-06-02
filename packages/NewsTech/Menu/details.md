# Menu Package

Owns backend navigation domain logic for menu groups and menu items, including persistence, repositories, requests, frontend menu resolution support, and ACL/menu configuration consumed by the admin layer.

## Responsibilities

- Store header, footer, and mobile menu structures
- Provide backend CRUD logic and menu-resolution support for frontend rendering
- Own menu migrations, factories, models, repositories, requests, support classes, and package config

## Ownership

- Routes/views/assets: does not own active admin or frontend route files, Blade views, or Vite assets
- UI surface: admin menu screens live under `packages/NewsTech/Admin`
- Dependencies: relies on `NewsTech/Admin` for admin presentation, `NewsTech/Frontend` for menu consumption, and `NewsTech/Core` for shared support

## Should Not Own

- Admin Blade views or admin assets
- Frontend Blade views or frontend assets
- Package-local active UI route files unless explicitly approved in a future phase
