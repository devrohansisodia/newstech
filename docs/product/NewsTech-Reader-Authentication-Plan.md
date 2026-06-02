# NewsTech Reader Authentication Plan

## Purpose

Reader Authentication adds frontend user accounts for normal website readers. This is separate from the admin authentication system.

Admins manage the site from `/admin`, while readers use frontend account features such as registration, login, profile, comments ownership, bookmarks, password reset, and email verification.

This foundation is required before building user-specific features like saved articles, personalized feeds, comment history, and newsletter preferences.

## Goals

* Add frontend reader registration.
* Add frontend reader login/logout.
* Add reader password reset foundation.
* Add reader account/profile page.
* Add reader email verification foundation.
* Keep admin users and reader users completely separate.
* Add admin reader CRUD without mixing readers into admin auth.
* Connect reader-based features like bookmarks, history, and comments.
* Follow NewsTech package-driven architecture.

## Package Ownership

Create a new package:

```txt
packages/NewsTech/Reader
```

Reader package should own:

```txt
- Reader model
- Reader repository
- migrations
- requests
- provider
- factories
- config if needed
- backend/domain logic
- details.md
```

Frontend package should own:

```txt
- frontend reader auth routes
- login/register/profile views
- account layout/views
```

Admin package owns reader-management routes and views for CRUD after the reader foundation is in place.

## Route Ownership

Frontend routes must be centralized under:

```txt
packages/NewsTech/Frontend/Routes
```

Possible route file:

```txt
packages/NewsTech/Frontend/Routes/reader.php
```

Suggested routes:

```txt
GET  /login
POST /login
GET  /register
POST /register
POST /logout
GET  /account
GET  /account/profile
POST /account/profile
GET  /forgot-password
POST /forgot-password
GET  /reset-password/{token}
POST /reset-password
```

Implemented routes also include email verification notice, resend, and signed verification handling.

## Database Design

Create readers table:

```txt
id
name
email
password
status: active/inactive
email_verified_at nullable
remember_token nullable
last_login_at nullable
timestamps
soft deletes if clean
```

Optional profile fields:

```txt
avatar nullable
bio nullable
website nullable
```

Password resets use Laravel's password broker and token storage. Email verification uses signed URLs and reader-specific notifications.

## Authentication Design

Reader auth should be separate from admin auth.

Use a dedicated guard/provider if clean:

```txt
guard: reader
provider: readers
model: NewsTech\Reader\Models\Reader
```

Do not mix readers with:

```txt
NewsTech\Admin\Models\AdminUser
```

Admin users are for backend/admin login only.

## Frontend Views

Frontend views should live under:

```txt
packages/NewsTech/Frontend/Resources/views/readers
packages/NewsTech/Frontend/Resources/views/account
```

Views needed:

```txt
login.blade.php
register.blade.php
forgot-password.blade.php
reset-password.blade.php
verify-email.blade.php
account/dashboard.blade.php
account/profile.blade.php
```

Design should match existing frontend layout.

## Validation Rules

Registration:

```txt
name required
email required, valid, unique readers.email
password required, confirmed, minimum length
```

Login:

```txt
email required
password required
status must be active
```

Profile update:

```txt
name required
email required, valid, unique except current reader
password optional confirmed
```

## Session / Middleware

Add reader auth middleware if clean:

```txt
reader.auth
reader.guest
```

Protected pages:

```txt
/account
/account/profile
/email/verify
```

Guest-only pages:

```txt
/login
/register
/forgot-password
```

Admin package also provides:

```txt
/admin/readers
/admin/readers/create
/admin/readers/{reader}/edit
```

## Comments Integration

Comments integration:

* Logged-in readers comment with stored reader identity and `reader_id`.
* Guest comments still remain enabled depending on settings.
* Admin comments screens show whether comment came from guest or reader.

Required schema addition if integrating:

```txt
comments.reader_id nullable
```

Existing guest comments remain valid.

## Security

* Passwords must be hashed.
* Login should regenerate session.
* Logout should invalidate reader session.
* Password reset and verification are isolated to the `reader` guard.
* Do not expose admin routes to readers.
* Do not allow inactive readers to login.
* Soft-deleted readers cannot login.

## Tests

Add tests for:

```txt
- reader can register
- duplicate email cannot register
- reader can login
- inactive reader cannot login
- reader can logout
- reader can request password reset
- reader can reset password with valid token
- reader verification email can be resent
- reader verification link marks the account verified
- reader can access account page after login
- guest cannot access account page
- reader can update profile
- admin can list, create, edit, deactivate, and delete readers
- admin auth remains separate
- reader cannot access admin panel
- logged-in reader comment stores reader_id if integrated
- existing guest comment flow still works
```

## README Updates

Update README with:

```txt
- Reader Authentication feature
- reader account routes
- reader password reset and email verification
- admin reader management
- package architecture note
```

## Implemented Status

- Reader registration, login, logout, dashboard, and profile
- Reader password reset request and reset flow
- Reader email verification notice, resend, and signed verification
- Admin reader CRUD with active/inactive control
- Separate reader guard, middleware, notifications, and broker

## Remaining Limitations

- No social login
- No reader roles or permissions
- No advanced account preference center
- No two-factor authentication

## Out of Scope

```txt
- social login
- two-factor authentication
- paid membership
- email verification enforcement
- full admin reader management
- reader roles/permissions
- personalized feed
```

## Future Enhancements

```txt
- email verification
- reader avatar/media picker
- reader comment history
- saved articles/bookmarks
- notification preferences
- newsletter preference center
- admin reader management
- reader ban/suspend tools
```

## Completion Criteria

* Reader registration/login/logout works.
* Reader account/profile page works.
* Reader auth is separate from admin auth.
* Optional reader-comment relation works without breaking guest comments.
* Existing admin/frontend/comment functionality remains stable.
* Full test suite passes.
