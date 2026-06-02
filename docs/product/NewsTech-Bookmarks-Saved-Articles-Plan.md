# NewsTech Bookmarks / Saved Articles Plan

## Purpose

Bookmarks allow logged-in readers to save articles for later reading.

This feature depends on Reader Authentication because saved articles should belong to a reader account and persist across devices.

## Goals

* Logged-in readers can bookmark articles.
* Logged-in readers can remove bookmarks.
* Readers can view saved articles in their account area.
* Readers can organize saved articles into folders.
* Readers can review recent reading history.
* Duplicate bookmarks should be prevented.
* Public article pages should show bookmark state for logged-in readers.
* Guest users should be asked to login/register before saving.
* Follow package-driven NewsTech architecture.

## Dependency

Required previous feature:

```txt
Reader Authentication Foundation
```

Bookmarks should not be built as session/localStorage-only for this project. Persistent account-based bookmarks are the preferred architecture.

## Package Ownership

Create a new package:

```txt
packages/NewsTech/Bookmark
```

Bookmark package should own:

```txt
- Bookmark model
- Bookmark repository
- migration
- provider
- factory
- details.md
- backend/domain logic
```

Frontend package should own:

```txt
- frontend bookmark routes
- account saved articles view
- article detail bookmark button UI
```

Admin package still does not need bookmark management UI in this phase. Analytics/reporting can remain future scope.

## Route Ownership

Frontend routes must live under:

```txt
packages/NewsTech/Frontend/Routes
```

Possible route file:

```txt
packages/NewsTech/Frontend/Routes/bookmarks.php
```

Suggested routes:

```txt
POST   /articles/{article}/bookmark
DELETE /articles/{article}/bookmark
GET    /account/bookmarks
POST   /account/bookmark-folders
PUT    /bookmarks/{bookmark}/folder
GET    /account/history
```

Alternative route names can use slug if existing frontend article routing prefers slug.

Protected by reader auth:

```txt
reader.auth
```

## Database Design

Create bookmarks table:

```txt
id
reader_id
article_id
timestamps
```

Constraints:

```txt
unique(reader_id, article_id)
foreign key reader_id references readers
foreign key article_id references articles
```

Optional:

```txt
deleted_at
```

Soft deletes are optional. A simple delete is enough for foundation.

Additional tables:

```txt
bookmark_folders
- id
- reader_id
- name
- slug
- timestamps

reader_article_histories
- id
- reader_id
- article_id
- last_viewed_at
- view_count
- timestamps
```

## Model Relationships

Reader model:

```txt
hasMany bookmarks
belongsToMany bookmarkedArticles through bookmarks if clean
```

Article model:

```txt
hasMany bookmarks
```

Bookmark model:

```txt
belongsTo reader
belongsTo article
```

## Frontend UI

Article detail page:

```txt
- Show “Save Article” button for logged-in readers if not saved.
- Show “Saved” / “Remove Bookmark” button if saved.
- Allow a saved article to be assigned to a reader-owned folder.
- For guests, show “Login to save this article” or redirect to login.
```

Article cards can optionally show bookmark icon, but first foundation can keep button only on article detail.

Account area:

```txt
/account/bookmarks
```

Shows:

```txt
- list/grid of saved articles
- title
- category
- publish date
- excerpt
- current folder
- move-to-folder control
- remove bookmark action
- empty state
```

History page:

```txt
/account/history
```

Shows recent published articles with last viewed timestamps and view counts.

## Behavior

Logged-in reader:

```txt
- can save published article
- can remove saved article
- cannot duplicate bookmark
- can view saved articles
- can create folders
- can move bookmarks between owned folders
- can view reading history for published articles
```

Guest:

```txt
- cannot save
- redirected to login or sees friendly message
- does not create reader history
```

Article rules:

```txt
- only published articles can be bookmarked
- draft/review/scheduled/archived articles should not be bookmarkable publicly
```

## Controllers / Services

Frontend BookmarkController responsibilities:

```txt
store bookmark
delete bookmark
list account bookmarks
create bookmark folder
move bookmark to folder
list reading history
```

Repository/service responsibilities:

```txt
find existing bookmark
create bookmark safely
delete bookmark safely
fetch paginated saved articles
```

## UX Messages

Examples:

```txt
Article saved.
Article removed from saved articles.
Please login to save articles.
This article is already saved.
```

## Testing

Add tests for:

```txt
- guest cannot bookmark article
- reader can bookmark published article
- reader cannot bookmark unpublished article
- duplicate bookmark is not created
- reader can remove bookmark
- reader can create bookmark folder
- reader can move bookmark between folders
- reader cannot access another reader's folder
- saved article appears in account bookmarks
- removed article no longer appears
- reading history records published article views for logged-in readers only
- bookmark button state appears on article detail for saved article
- guest sees login prompt or redirect
- reader cannot access another reader's bookmark removal if direct id route is used
- full suite remains green
```

## README Updates

Update README with:

```txt
- Bookmarks / Saved Articles feature
- dependency on Reader Authentication
- account saved articles route
- bookmark folders and reading history
```

## Implemented Status

```txt
- saved articles
- account bookmarks page
- bookmark folders with filtering and reassignment
- reading history for logged-in readers
```

## Remaining Limitations

```txt
- offline reading
- share saved list
- public collections
- bookmark analytics
- bookmark icons on every article card unless simple
- guest localStorage bookmarks
```

## Future Enhancements

```txt
- folders/collections
- reading history
- recommended articles based on bookmarks
- bookmark counts in admin analytics
- “Most saved articles” widget
- newsletter based on saved interests
```

## Completion Criteria

* Logged-in readers can save/remove articles.
* Saved articles page works.
* Guest users cannot bookmark without login.
* Duplicate bookmarks are prevented.
* Only published articles can be bookmarked.
* Existing article/comment/frontend flows remain stable.
* Full test suite passes.
