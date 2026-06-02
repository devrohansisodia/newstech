# NewsTech Comments Foundation

## Purpose

Add a moderation-first comment system for published articles so guests and readers can submit comments, admins can review them, and only approved comments appear publicly.

## User Flow

1. A visitor opens a published article detail page.
2. The page shows approved comments and a guest/reader comment form.
3. The visitor submits `name`, `email`, optional `website`, and `content`.
4. The submission is stored as `pending` or `approved` depending on settings and spam checks.
5. The visitor sees a moderation or published success message.

## Admin Moderation Flow

1. Admin opens the Comments screen from the admin sidebar.
2. The index lists article, commenter identity, status, submitted date, and moderation actions.
3. Admin can open a comment detail page, approve it, reject it, or delete it.
4. Approved comments become visible on the related article detail page.
5. Approved replies render nested under their parent comment.

## Package Ownership

- Backend/domain logic: `packages/NewsTech/Comment`
- Admin routes and views: `packages/NewsTech/Admin`
- Frontend routes and views: `packages/NewsTech/Frontend`
- Shared rendering/support remains in existing Core/Admin/Frontend foundations only where already appropriate.

## Database Fields

`comments` table:

- `id`
- `article_id`
- `parent_id` nullable
- `reader_id` nullable
- `name`
- `email`
- `website` nullable
- `content`
- `status` (`pending`, `approved`, `rejected`)
- `is_spam`
- `spam_reason` nullable
- `ip_address` nullable
- `user_agent` nullable
- `approved_at` nullable
- `moderated_at` nullable
- `moderated_by` nullable
- timestamps
- soft deletes

## Routes

Frontend:

- `POST /news/{slug}/comments`
- supports optional `parent_id` for replies

Admin:

- `GET /admin/comments`
- `GET /admin/comments/{comment}`
- `PUT /admin/comments/{comment}/approve`
- `PUT /admin/comments/{comment}/reject`
- `DELETE /admin/comments/{comment}`

## Views

Frontend:

- Article detail comments list and submission form under `packages/NewsTech/Frontend/Resources/views/articles`

Admin:

- Comments index and detail pages under `packages/NewsTech/Admin/Resources/views/comments`

## Tests

- Guest can submit a comment for a published article
- Logged-in reader comments store `reader_id`
- Submitted guest comment defaults to `pending`
- Validation errors are shown for invalid input
- Unpublished article comment submission is not allowed
- Approved comments render publicly
- Pending and rejected comments stay hidden publicly
- Approved replies render nested under their parent
- Cross-article or rejected-parent replies are blocked
- Admin can view comments index
- Admin can approve, reject, and delete comments
- Admin sidebar includes comments navigation
- Article / Comment relationship works

## Related Follow-Up

- Comment settings and anti-spam controls are extended in `docs/product/NewsTech-Comment-Settings-Anti-Spam.md`.
- Reader identity, verification, and bookmark integration are extended in the reader/bookmark planning documents.

## Implemented Status

- Guest and logged-in reader comments
- Comment moderation, settings, and anti-spam rules
- Reader-linked admin moderation context
- One-level nested replies on article detail pages

## Limitations / Future Improvements

- No reactions, likes, or bookmarks
- No CAPTCHA or third-party spam service integration
- No frontend comment editing or deletion
