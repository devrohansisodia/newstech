# NewsTech Newsletter Campaigns Foundation

## Purpose

Phase 6.2 turns the original newsletter signup foundation into a package-owned campaign and delivery system.

The foundation now covers:

- subscriber state management
- campaign creation
- recipient tracking rows
- Laravel mail delivery
- unsubscribe links
- newsletter settings

## Subscriber Flow

Frontend newsletter forms still post into the Newsletter package.

Current behavior:

1. Validate and normalize the email address.
2. Block new subscriptions when newsletters are disabled.
3. Detect duplicate active subscribers cleanly.
4. Allow resubscribe for previously unsubscribed addresses when the setting is enabled.
5. Store source, IP address, user agent, subscribed timestamp, and unsubscribe token.

Subscriber statuses currently supported:

- `active`
- `unsubscribed`
- `inactive`

## Campaign Flow

Campaigns are stored in `newsletter_campaigns`.

Each campaign supports:

- name
- subject
- preheader
- HTML content
- draft or scheduled status before sending
- aggregate recipient and delivery counters

When an admin sends a campaign:

1. Active subscribers are selected.
2. Recipient rows are created in `newsletter_campaign_recipients` if missing.
3. The campaign is marked sending.
4. Laravel mail sends a campaign mailable to each active recipient.
5. Recipient rows are marked `sent`, `failed`, or `skipped`.
6. Campaign counters are refreshed and the campaign is marked sent.

## Sending Behavior

This phase uses synchronous Laravel mail delivery.

Rules:

- only active subscribers receive mail
- unsubscribed or inactive subscribers are skipped
- campaigns cannot be sent twice accidentally
- subject, preheader, campaign content, and unsubscribe link are included
- sender name and sender email come from newsletter settings with mail config fallbacks

## Unsubscribe Behavior

Each subscriber now has a unique `unsubscribe_token`.

The frontend unsubscribe route:

- looks up the token
- marks the subscriber unsubscribed
- stores `unsubscribed_at`
- renders a friendly confirmation page

Invalid tokens return a safe `404`.

## Settings

The Newsletter package now registers a settings group for:

- newsletters enabled
- double opt in placeholder setting
- allow resubscribe
- sender name
- sender email
- footer unsubscribe text

## Tracking Fields

Subscriber tracking currently stores:

- source
- IP address
- user agent
- subscribed at
- unsubscribed at
- unsubscribe token

Campaign tracking currently stores:

- recipients count
- delivered count
- failed count
- recipient status rows
- sent and failed timestamps
- failure reason text

## Limitations

- no scheduled automation runner yet
- no queue job layer yet
- no open or click pixel tracking
- no email template library
- no bounce processing
- no double-opt-in confirmation flow yet

## Future Improvements

- queued campaign delivery
- scheduler-driven sends for `scheduled_at`
- reusable templates and campaign duplication
- open tracking and richer delivery analytics
- bounce and complaint handling
- confirmation emails for double opt in
