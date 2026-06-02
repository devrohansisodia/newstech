# NewsTech Advanced Advertisement Manager

## Purpose

Phase 6.1 turns the placeholder-only advertisement package into a managed slot-based advertisement system.

The manager is built on top of:

- NewsTech render events
- registry-driven admin settings groups
- package-owned backend domain logic
- centralized admin routes and admin views

## Slot Architecture

Advertisement slots remain config-driven in `packages/NewsTech/Advertisement/Config/config.php`.

Current managed slots:

- `header_leaderboard`
- `homepage_top`
- `homepage_sidebar`
- `article_top`
- `article_inline`
- `article_sidebar`
- `listing_top`
- `footer_banner`

Each slot defines:

- key
- label
- description
- suggested size
- render event bindings
- enabled flag

## Render Event Integration

The Advertisement package does not inject new component tags into frontend blades.

Instead it listens to existing render events and renders by slot:

1. A render hook fires.
2. The advertisement renderer checks the slot config.
3. If managed ads are globally enabled and an active ad exists for the slot, it renders that ad.
4. If no managed ad exists and placeholder fallback is enabled, it renders the placeholder.
5. If ads are globally disabled, it renders nothing.

## Admin Management

Admin routes stay centralized in `packages/NewsTech/Admin/Routes/advertisements.php`.

Admin views stay centralized in:

- `packages/NewsTech/Admin/Resources/views/advertisements`

Supported management features:

- list ads
- create ads
- edit ads
- delete ads
- image and HTML ad types
- slot assignment
- status active/inactive
- start and end scheduling
- priority ordering
- media picker integration for image ads
- target URL and link attributes
- impression and click counters

## Settings

The Advertisement package now registers a functional settings group:

- advertisements enabled
- placeholder fallback enabled
- impression tracking enabled
- click tracking enabled
- default open in new tab
- default nofollow
- default sponsored

These settings are persisted through the shared `SystemSettingsManager`.

## Tracking Foundation

The current tracking foundation is aggregate-count based:

- impressions increment when an active managed ad is rendered and tracking is enabled
- clicks increment through the advertisement click redirect route and then redirect to the stored target URL

No per-visitor analytics, campaign attribution, or event log tables were added in this phase.

## Content Types

### Image Ads

Image ads support:

- media picker image selection
- optional target URL
- click tracking redirect
- open in new tab
- rel attribute control with `nofollow` and `sponsored`

### HTML Ads

HTML ads render trusted admin-managed markup directly.

This is intentionally documented as a trusted-admin feature. No external sanitizer was added in this phase.

## Limitations

- one highest-priority active ad wins per slot at render time
- no rotation engine, A/B testing, or weighted delivery yet
- no per-impression event log table yet
- no third-party ad SDK integration
- HTML ads are trusted admin content, not end-user content

## Future Improvements

- campaign grouping
- slot rotation / weighted delivery
- CTR and trend reporting
- impression event logs
- per-device or per-page targeting rules
- approval workflow for ad content
