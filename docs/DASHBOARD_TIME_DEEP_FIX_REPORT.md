# Dashboard Time Deep Fix

## Root hardening

- Dashboard SQL day boundaries now come from `TimeService::sqlDayBounds()`.
- The bounds are plain SQL strings and do not rely on chained `->format()` calls.
- `today()`, `todayStart()`, `todayEnd()`, and `sqlNow()` now have deterministic string semantics.
- Dashboard API returns exception file and line in `debug` when a calculation still fails, allowing the exact remaining call site to be identified.

## Deployment note

Replace the full project files, restart Apache/PHP, and clear OPcache if enabled. A stale PHP opcode cache can continue executing an older `TimeService.php` after files are replaced.
