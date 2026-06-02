# Reader Package

## Purpose

Owns frontend reader account domain logic, including the reader model, authentication foundation, profile persistence, password reset, email verification, admin reader CRUD support, and account relationships used by comments and bookmarks.

## Responsibilities

- Reader model and repository
- Reader migrations and factories
- Frontend reader auth controllers and requests
- Reader auth middleware
- Reader password reset and verification notifications/controllers
- Admin reader CRUD controllers and requests
- Reader-to-comment and reader-to-bookmark relationships
- Reader-to-folder and reader-to-history relationships

## Ownership

- Backend/domain logic: `packages/NewsTech/Reader`
- Frontend routes: `packages/NewsTech/Frontend/Routes/reader.php`
- Frontend views: `packages/NewsTech/Frontend/Resources/views/readers` and `.../account`
- Admin routes: `packages/NewsTech/Admin/Routes/readers.php`
- Admin views: `packages/NewsTech/Admin/Resources/views/readers`

## Dependencies

- `NewsTech\Comment` for optional reader-owned comments
- `NewsTech\Bookmark` for saved articles
- `NewsTech\Frontend` for route and view ownership

## Should Not Own

- Admin authentication
- Admin views
- Frontend article rendering
- Bookmark storage logic
