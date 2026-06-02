# NewsTech Coding Standard Review

## Phase

Phase 5.2 review and safe-fix pass focused on package ownership, route ownership, view ownership, component ownership, asset ownership, and Bagisto-inspired modular structure.

## Reviewed Areas

- Package boundaries across `packages/NewsTech/*`
- Admin and frontend route registration ownership
- Blade view and anonymous component namespace ownership
- Vite asset/config ownership for Admin and Frontend
- Provider registration and high-level Bagisto-style package organization
- Selected controllers, repositories, requests, and providers for low-risk coding-standard inconsistencies

## Confirmed Rules

- Active admin routes are registered from `packages/NewsTech/Admin/Routes/*` through `NewsTech\Admin\Providers\AdminServiceProvider`.
- Active frontend routes are registered from `packages/NewsTech/Frontend/Routes/*` through `NewsTech\Frontend\Providers\FrontendServiceProvider`.
- Admin Blade views and components resolve from `packages/NewsTech/Admin/Resources/views`.
- Frontend Blade views and components resolve from `packages/NewsTech/Frontend/Resources/views`.
- Shared Core Blade components currently in use are generic (`panel`, `brand-mark`, SEO helpers) and are safe for both Admin and Frontend.
- Feature packages currently own backend/domain logic, config, requests, controllers, repositories, models, and migrations without registering their own active admin/frontend route files.

## Issues Found

1. Admin and Frontend Vite package configs were both refreshing on every `packages/NewsTech/**/Resources/views/**` and `packages/NewsTech/**/Routes/**` change. This did not break runtime behavior, but it weakened package ownership boundaries during development.
2. `packages/NewsTech/Media/Resources/views` exists as an empty package-local views directory. It is not active, but it is inconsistent with the rule that feature packages should not own active UI views unless explicitly approved.
3. Several feature packages still contain empty `Routes` directories. They are inactive and harmless, but they blur the intended rule that Admin and Frontend own active UI route files.
4. Package documentation coverage is incomplete. At minimum, `Media` and `Menu` do not currently expose a `details.md` package note alongside the other modules.

## Safe Fixes Applied

1. Restricted `packages/NewsTech/Admin/vite.config.js` refresh paths to Admin-owned views/routes plus Core shared views.
2. Restricted `packages/NewsTech/Frontend/vite.config.js` refresh paths to Frontend-owned views/routes plus Core shared views.
3. Removed empty feature-package `Routes` directories that were not used by active Admin or Frontend route registration.
4. Removed the unused `packages/NewsTech/Media/Resources/views` directory.
5. Added missing `details.md` files for the `Media` and `Menu` packages.

## Deferred Refactors

- Consider reducing broad Tailwind source scanning only after verifying no class generation depends on package-wide PHP scanning.

## Next Recommended Phase

Proceed to a dedicated cleanup/documentation alignment pass or the next approved product phase. Do not start Rich Text Editor work until separately approved.
