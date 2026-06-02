# NewsTech Installer

`php artisan newstech:install` prepares a local or fresh NewsTech instance through the dedicated installer package at `packages/NewsTech/Installer`.

For normal installation, this is the only NewsTech command the user should need after `composer create-project`.

## Fresh Install Flow

```bash
composer create-project <vendor>/newstech
```

Expected Composer behavior for a fresh NewsTech install:

- dependencies are installed
- `.env` is created from `.env.example` when supported
- `APP_KEY` is generated
- no database migration is run
- no database connection is attempted

After that:

1. run `php artisan newstech:install`
2. confirm the fresh install reset
3. provide database credentials only if the current connection is invalid
4. choose whether demo content should be installed

## What It Does

- ensures `.env` exists before configuration
- confirms that the install will reset existing application tables/data
- interactively asks for database host, port, database name, username, and password only when the current connection is invalid
- writes installer-provided values into `.env`
- tests the database connection and allows retry on failure
- runs a fresh migration reset with `migrate:fresh`
- creates the public storage link when needed
- creates the default admin user automatically
- asks whether demo content should be installed
- seeds required default site settings and branding defaults
- publishes package-owned local demo assets
- optionally installs a full demo newsroom dataset
- prints frontend/admin URLs, default admin credentials, and asset-manifest guidance at the end

## Main Options

```bash
php artisan newstech:install
php artisan newstech:install --without-demo-content
php artisan newstech:install --with-demo-content
php artisan newstech:install --admin-email=admin@example.com --admin-password=secret123
php artisan newstech:install --with-demo-content --force
php artisan newstech:install --with-demo-content --admin-email=admin@example.com --admin-password=secret123 --no-interaction
```

- `--force`
  Required for non-interactive destructive fresh installs.
- `--with-demo-content`
  Seeds the full demo newsroom dataset with local images, menus, pages, articles, and optional sample engagement records.
- `--without-demo-content`
  Skips demo content and only prepares the required baseline setup.
- `--admin-email=`
  Overrides the default admin login email.
- `--admin-password=`
  Overrides the default admin login password.

Without `--no-interaction`, the installer behaves like a guided Bagisto-style fresh install flow.

With `--no-interaction`, use `--force`. The installer uses the existing `.env` values, runs the destructive fresh install flow, and exits with a friendly message if the database connection fails.

## Safety Notes

- The installer is destructive by design and resets existing application tables/data.
- Use it carefully on databases that already contain content you want to keep.
- The command does not rely on remote image URLs or external APIs.
- The installer only checks for missing build manifests and warns if they are absent. It does not run npm commands.
- Public uploads such as logos require the `public/storage` symlink. The installer checks this and will try to create it. If that step cannot complete, run `php artisan storage:link`.
- `FILESYSTEM_DISK=public` remains the recommended default for local NewsTech installs.
- Default admin credentials are `admin@newstech.test` / `password`; change them before production use.

## Demo Content Scope

Demo content is designed to make the homepage and article detail pages look complete immediately after install:

- 10 editorial categories
- 20 tags
- multiple authors with local avatars
- category/article imagery stored locally under `storage/app/public/newstech/demo`
- published articles with SEO fields, featured images, inline images, headings, and internal links
- static pages for About, Contact, Privacy Policy, Terms, Editorial Policy, and Advertise With Us
- header, footer, and mobile menus linked to real routes
- sample comments, reader bookmarks/history, newsletter subscribers, and one managed advertisement

In the test environment the installer seeds a smaller article count per category to keep the suite fast, while the real demo install remains rich enough for homepage, listing, and detail-page QA.
