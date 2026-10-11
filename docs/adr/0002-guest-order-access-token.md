# 2. Digital delivery to guest orders uses a rotatable per-Order access token

Date: 2026-10-12

Status: Accepted

## Context

Customers can now buy digital products without registering (`config('checkout.allow_guest')`), and they receive their product through a link emailed to them. Email is the only access channel a Guest Order has, since there is no session and no `user_id`.

The existing download mechanism is a `URL::temporarySignedRoute` valid for 24 hours (`DownloadService::generateDownloadUrl`), guarded by `auth` middleware. Three options were considered for guest delivery: a temporary signed URL, an opaque per-Order token, or an email link that is exchanged once for a session.

## Decision

- A Guest Order's digital items are accessed through a **stable URL containing an opaque, per-Order access token**: `/order/{token}`.
- The token is **not temporary-signed** and has **no default expiry**; it is revocable and is **rotated on every "resend link"**.
- The token is stored **hashed** (sha256) on the Order (`access_token_hash`); only the emailed URL carries the plaintext.
- The URL resolves to an **Order Access Page** that lists the Order's digital items with their download and invoice actions, rather than downloading a file directly.

## Consequences

- A temporary signed URL was rejected because it expires in 24 hours; with email as the sole channel, an expired link means the customer permanently loses the product unless a separate recovery flow regenerates it.
- An exchange-once link that sets a session was rejected as added complexity with no benefit for a stable-URL mental model ("the product URL in your email").
- Requires a new column on `orders`, a public route for the Order Access Page that bypasses the `auth` middleware, and a public rate-limited "resend link" flow.
- Rotating the token on resend invalidates previously emailed links, which is intended but must be surfaced to the customer in the resend confirmation.
