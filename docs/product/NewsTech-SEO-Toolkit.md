# NewsTech SEO Toolkit

## Purpose

Phase 6.3 adds a rule-based SEO toolkit foundation for NewsTech article and page editing. The goal is to help admins catch SEO issues while drafting instead of after publishing.

## Real-time Analysis Behavior

- The admin article and page forms now mount a Vue SEO panel in the SEO section.
- The panel watches title, slug, excerpt, rich text content, meta fields, featured image, publishing state, category, author, tags, and focus keyword.
- Changes are debounced and sent to a protected admin endpoint: `POST /admin/seo/analyze`.
- The endpoint runs the PHP analyzer so the backend stays the source of truth.

## SEO Checks

Current foundation checks:

- Meta title presence and recommended length
- Meta description presence and recommended length
- Slug presence, format, and length
- Content depth, heading usage, and empty paragraph cleanup
- Featured image presence
- Inline image alt coverage
- Internal and external link presence plus external `rel` safety
- Focus keyword placement in title, slug, meta description, and content
- Canonical URL validity when a custom value is supplied
- Structured-data readiness signals for article author/category/publish time
- Publication visibility note for sitemap/feed expectations

## Scoring Method

- Score range: `0-100`
- Errors reduce more points than warnings
- Suggestions are informational and do not lower the score directly
- Grades:
  - `80-100` Good
  - `50-79` Needs improvement
  - `0-49` Poor

## Admin UI Behavior

- The panel shows:
  - current score
  - grade
  - blocking errors
  - warnings
  - suggestions
  - checklist items
  - search snippet preview
  - social preview card
- Rich text and media-picker changes update the panel without a page reload.

## Backend Analyzer Design

- Package: `packages/NewsTech/Seo`
- Core classes:
  - `SeoAnalyzer`
  - `SeoScoreResult`
  - `SeoIssue`
  - `SeoPreviewBuilder`
- Input stays array-based so article/page forms can share one analyzer.
- HTML is parsed locally with PHP DOM utilities only. No external APIs are used.

## Article And Page Integration

- Article admin form:
  - adds persistent `focus_keyword`
  - sends article-specific context such as category, author, tags, and publish state
- Page admin form:
  - adds persistent `focus_keyword`
  - sends page title, slug, body, status, and meta fields
- Frontend rendering was intentionally left stable. The toolkit focuses on analysis and editor guidance, not a frontend SEO rewrite.

## Settings

Optional SEO settings are registered through the existing settings registry:

- site title suffix
- default meta description
- enable real-time checks
- score threshold warning
- enable social preview

## Limitations And Future Improvements

- No bulk SEO audit dashboard was added in this phase.
- No paid SEO APIs or third-party scoring services are used.
- Canonical override persistence was not added.
- Readability analysis is intentionally lightweight and rule-based.
- Future phases can add:
  - bulk SEO reporting
  - stored historical scores
  - more advanced readability metrics
  - canonical override fields
  - per-template schema customization
