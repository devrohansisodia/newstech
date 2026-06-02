# Bookmark Package

## Purpose

Owns saved article persistence for authenticated readers, including bookmark folders and reader reading history.

## Responsibilities

- Bookmark model and repository
- Bookmark migrations and factories
- Reader-to-article saved relationship persistence
- Bookmark folder persistence
- Reader article history persistence
- Frontend bookmark controller domain behavior

## Ownership

- Backend/domain logic: `packages/NewsTech/Bookmark`
- Frontend routes: `packages/NewsTech/Frontend/Routes/bookmarks.php`
- Frontend views: `packages/NewsTech/Frontend/Resources/views/account`

## Dependencies

- `NewsTech\Reader` for bookmark ownership
- `NewsTech\Article` for published article resolution
- `NewsTech\Frontend` for route and view ownership

## Should Not Own

- Admin UI
- Reader authentication
- Frontend article page layout
