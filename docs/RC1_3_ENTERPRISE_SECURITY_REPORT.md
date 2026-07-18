# RC1.3 — Enterprise Security Review

## Scope

This phase hardens the stable RC1.2 web codebase without changing business calculations or the responsive layout.

## Changes

- Added conservative HTTP security headers for authenticated pages and login.
- Added no-store headers for authenticated web screens.
- Added `csrf_verify()` as a backward-compatible alias for legacy actions.
- Enforced POST method checks on state-changing actions that were missing them.
- Replaced two unsafe legacy create endpoints with delegates to their tenant-aware, permission-aware canonical actions.
- Added reusable strict integer and enum input helpers.
- Added `scripts/security_audit.php` to detect state-changing actions missing CSRF protection.

## Headers

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy`
- `Cross-Origin-Opener-Policy`
- `Cross-Origin-Resource-Policy`
- authenticated pages: `Cache-Control: no-store`

A strict Content-Security-Policy was intentionally not enabled in this phase because the current UI still uses the Tailwind CDN and inline scripts. CSP should be introduced after those assets are bundled locally.

## Verification

Run:

```bash
php scripts/security_audit.php
```

The command exits non-zero if a state-changing PHP action appears to lack CSRF verification.
