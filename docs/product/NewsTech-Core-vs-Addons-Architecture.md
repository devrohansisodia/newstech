# NewsTech Core vs Add-ons Architecture

## Purpose

This document defines the current NewsTech core CMS boundary and how future optional packages should extend the platform without breaking centralized route/view ownership.

## Core CMS Boundary

The repository currently includes the core NewsTech CMS foundation:

- centralized Admin and Frontend packages
- render-event foundation
- settings registry
- categories, tags, authors, articles, and pages
- media library and picker
- menus
- comments and moderation
- reader accounts, bookmarks, folders, and history
- advertisement manager
- newsletter foundation
- SEO toolkit
- installer package and demo content flow

## Extension Points

Optional packages should extend NewsTech through:

- package service providers
- package config
- settings groups and settings fields
- render events
- repositories, actions, jobs, notifications, or API/service layers

Optional packages should not override centralized Admin or Frontend Blade files unless explicitly approved for a specific release task.

## Future Optional Packages

Examples of clean add-on boundaries:

- Analytics Pro
- Notifications
- SEO Pro / SEO Reports
- Ads Pro rotation and reporting
- Newsletter Pro queueing, scheduling, templates, and segmentation
- Personalization and recommendations
- Deployment and Docker tooling

## Installation Expectations

Each optional package should:

- live under `packages/NewsTech/*`
- register its own provider
- own its own backend/domain logic
- rely on Admin and Frontend packages for active route/view ownership unless a new centralized surface is explicitly introduced

## Webkul Reference Policy

`packages/Webkul/*` remains reference-only. NewsTech packages may inspect Bagisto conventions there for structure and naming, but must not depend on direct modification of Webkul packages.
