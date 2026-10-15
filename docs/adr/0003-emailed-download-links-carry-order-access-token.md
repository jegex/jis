# 3. Emailed download links carry the Order Access Token directly on the file URL

Date: 2026-10-16

Status: Accepted

Supersedes: [0002](0002-guest-order-access-token.md)

## Context

ADR 0002 established the per-Order access token (`orders.access_token_hash`, `/order/{token}` Order Access Page) but the token was never wired into the emailed link: `DownloadService::generateDownloadUrl` still produced either a 24-hour temporary signed URL, or — when `checkout.allow_guest` is enabled — a completely unsigned URL that `Payment\DownloadController` accepted from anyone who knew it, with no expiry. Email remained the only channel a Guest Order has, so the emailed link was the weakest link in the delivery chain.

The Order Access Page itself was deferred; the immediate goal is that only the recipient of the emailed link can download the purchased file.

## Decision

- Emailed download links point **directly at the file endpoint** with the Order Access Token as a query parameter: `GET /download/{order_number}/{product}?token={plaintext}`.
- This applies to **every** product, including external-file products. `DownloadService::generateDownloadUrl` no longer returns the external `url_external` directly; the emailed link is always the token-protected route, and the controller redirects to the external URL only after the access checks pass.
- `Payment\DownloadController` grants access when **either** the token resolves to the same Order (`OrderAccessService::resolve`) **or** the authenticated user owns the Order. The `allow_guest` bypass and the signed-URL path are removed; there is exactly one authorization model: *holder of the token, or the owner*.
- The token has **no default expiry** and is **rotated on every link issuance** (each confirmation/download-link email, and the admin "Resend Download Link" action). Only the sha256 hash is persisted; the plaintext exists only in the emailed URL, so re-sending a link always means rotating.
- The automatic DownloadLink email in `TriggerOrderEmails` stays disabled: sending two emails would rotate twice and kill the link in the first email. The Order Confirmation email carries the link; resends go through the admin action.
- The `/order/{token}` Order Access Page route remains available (its download buttons forward the same token), but is no longer the emailed target.

## Consequences

- A leaked or forwarded email link works until it is rotated — there is no per-download or per-email binding beyond the bearer token, and no expiry. Rotation (resend) is the revocation mechanism.
- Links in emails sent before this change (signed URLs, unsigned guest URLs) stop working; customers need a resent link. Orders without a token get one issued when the next link is generated.
- Resending a confirmation email also rotates the token, invalidating the previously emailed link — this must stay surfaced in the resend UI copy.
- Guest checkout no longer implies world-readable download URLs, so `checkout.allow_guest` only affects checkout, not delivery security.
- For external-file products the entry point is protected, but the browser is redirected to the external URL, so that URL becomes visible and can still be shared by whoever reaches it; the external host itself is outside our control. Proxying the file would hide it, but was rejected as added bandwidth and reliability cost.
