# Enterprise Time Hardening — Phase 2

## Objective

Propagate the tenant timezone into critical operational workflows instead of relying on the operating-system or hosting-provider timezone.

## Updated critical paths

- Mobile cash-session opening and closing timestamps.
- Mobile ticket synchronization timestamp.
- Mobile gain-payment timestamp and transaction references.
- Mobile commission date filters.
- Mobile notification read timestamps.
- Ticket, finance, ledger, and payment reference generation.
- Risk windows and daily exposure calculations.
- Draw gain-processing timestamp.
- Automatic lottery-closing cron, evaluated separately in each tenant timezone.

## Time source

`TimeService` is now the source of truth for business timestamps. The default remains `America/Port-au-Prince`, while each tenant may override it through `tenant_settings.timezone` or `tenants.timezone`.

## New helpers

- `TimeService::todayStart()`
- `TimeService::todayEnd()`
- `TimeService::monthStart()`
- `TimeService::normalizeDate()`

## QA tools

```bash
php scripts/timezone_smoke_test.php
php scripts/timezone_audit.php
```

The audit is informational because some legacy administration pages still use direct date functions. Critical Mobile Agent and lottery-closing workflows have priority and were migrated in this phase.

## Required browser/mobile check

After login, call:

```text
/api/mobile/time.php
```

The response must show the tenant IANA timezone and its current UTC offset.
