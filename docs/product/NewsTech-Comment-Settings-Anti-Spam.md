# NewsTech Comment Settings + Anti-Spam

## Purpose

Extend the comment foundation with admin-controlled behavior and practical built-in anti-spam rules without adding third-party services or reader authentication.

## Settings

- `comments.enabled`
- `comments.require_moderation`
- `comments.guest_comments_enabled`
- `comments.website_field_enabled`
- `comments.min_comment_length`
- `comments.max_comment_length`
- `comments.max_links_per_comment`
- `comments.blocked_words`
- `comments.blocked_emails`
- `comments.blocked_ips`
- `comments.auto_reject_spam`
- `comments.throttle_seconds`

## Spam Rules

- Honeypot field handling
- Minimum and maximum content length
- Maximum link count per comment
- Blocked words match
- Blocked email and domain match
- Blocked IP match
- Per-article throttle by IP or email

## Admin Moderation Behavior

- New comments become `pending` or `approved` based on settings and spam evaluation.
- Spam-marked comments can remain `pending` or become `rejected` based on `auto_reject_spam`.
- Admin can still approve, reject, and delete comments.
- Admin views show spam metadata, moderation metadata, and request context.

## Frontend Behavior

- Hide the form and show a closed-state message when comments are disabled.
- Hide the guest form and show a guest-disabled message when guest comments are disabled.
- Hide the website field when disabled.
- Use moderation-aware success messaging.
- Keep only approved comments public.

## Package Ownership

- Domain logic and spam rules: `packages/NewsTech/Comment`
- Admin settings and moderation views: `packages/NewsTech/Admin`
- Frontend article comment UI: `packages/NewsTech/Frontend`
- Shared persisted settings: existing Core system settings foundation

## Tests

- Settings visibility and persistence
- Comment submission behavior for enabled / disabled / moderation modes
- Spam and throttle rules
- Admin moderation metadata and status handling
- Public visibility rules for approved vs pending / rejected / spam comments

## Limitations / Future Improvements

- No CAPTCHA or external spam service
- No reader accounts or ownership-aware comment editing
- No nested reply UI
- No reputation scoring or trust levels
- No dedicated spam review queue beyond current moderation filters
