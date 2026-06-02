# NewsTech MVP Functional Review

## Reviewed Areas

- Admin sidebar, topbar, CRUD index/create/edit/delete links, and package-owned route naming
- Category create/edit discoverability and update flow
- Frontend homepage, article detail, taxonomy pages, search, static pages, database pages, and SEO feed routes
- Settings persistence from admin to frontend branding and homepage layout output
- Image preview behavior for branding uploads and stored editorial image paths
- Package-driven structure against the NewsTech modular architecture rule

## Issues Found

- Branding and homepage settings fields rendered incorrect form names, so saved values did not map cleanly to the settings update request.
- Category edit flow existed, but the primary datagrid cell did not link to edit, which made update actions less discoverable.
- Article breadcrumb data linked the category step back to the homepage instead of the public category page.
- The installer command still lived in the root app layer instead of a NewsTech package.
- Shared admin file inputs did not preview newly selected images before submit.

## Fixes Applied

- Corrected admin settings field name mapping for branding and homepage settings.
- Added preview-aware shared file input behavior for branding uploads.
- Added stored image previews for article featured image paths and author avatar paths.
- Linked the primary admin datagrid value to the first non-form row action to improve edit discoverability.
- Corrected article breadcrumb category URLs.
- Moved `newstech:install` into the Core package and registered it from the Core service provider.

## Remaining Known Limitations

- Article featured image and author avatar fields still store paths/URLs rather than using a full media picker.
- Admin previews for path-based image fields show current stored assets only; live preview before submit is available on actual file inputs.
- NewsTech still intentionally omits comments, bookmarks, user auth, advanced ads, advanced newsletter campaigns, and richer analytics.

## Next Recommended Phase

- Phase 5.1, after this MVP review pass is approved.
