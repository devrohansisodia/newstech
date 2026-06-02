# NewsTech GitHub Release Checklist

Use this checklist immediately before the first public GitHub publish or release tag.

## Repository

- Confirm `LICENSE` is present and matches the root `composer.json` license value.
- Confirm `README.md` includes install guidance, environment defaults, and the screenshot capture list.
- Capture and commit final screenshots when they are available.

## Environment Defaults

- Keep `FILESYSTEM_DISK=public` as the recommended local default.
- Replace the default `MAIL_MAILER=log` settings with a real mail provider before testing password reset or newsletter delivery outside a local dry run.
- Keep `QUEUE_CONNECTION=database` for simple installs, but run a queue worker for newsletter sends and other queued jobs.

## Verification

- Run `composer dump-autoload`.
- Run `php artisan route:list --except-vendor --path=admin`.
- Run `php artisan route:list --except-vendor --name=newstech`.
- Run `php artisan test --compact tests/Feature/NewsTechInstallerCommandTest.php`.
- Run `php artisan test --compact`.

## Manual Release Tasks

- Perform one final fresh database install rehearsal before tagging.
- Capture and commit the final screenshot set:
  - homepage
  - article detail
  - admin dashboard
  - article editor with SEO panel
  - media library
  - advertisement manager
  - newsletter campaigns
- Review production mail, queue worker, and cron/supervisor setup for the target host.
