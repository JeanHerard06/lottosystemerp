# Mobile Agent Financial Engine Unification

## Objective
Unify Dashboard, Commission Center and Balance calculations so every screen uses the same source and the tenant timezone.

## Corrected behavior

- Sales are calculated from non-cancelled `fiches` scoped to the authenticated agent and tenant.
- Commission is calculated from the same fiches, grouped by game, using `commissions.percentage` first and the agent game rate as fallback.
- Today, week, month, custom-date and all-time periods use `TimeService` with the tenant timezone.
- Paid gains, deposits and withdrawals are read from posted `agent_transactions`.
- Balance follows the existing project convention:

  `sales + commission + deposits - withdrawals - gains paid`

- Legacy fiches without commission transaction rows are included correctly.
- Dashboard, Commission Center, detail/history endpoints and Balance now call one financial engine.
- MariaDB-safe alias `play_lines` replaces the problematic `lines` alias, with backward-compatible JSON.

## Main files

- `app/Helpers/mobile_agent_financial_engine.php`
- `app/Helpers/mobile_dashboard_metrics.php`
- `api/mobile/dashboard.php`
- `api/mobile/balance.php`
- `api/mobile/commissions/dashboard.php`
- `api/mobile/commissions/details.php`
- `api/mobile/commissions/history.php`
- `mobile_app/lib/screens/commissions_screen.dart`

## Expected test
For two valid fiches totalling 350 HTG with a 10% commission and no other transaction:

- Sales: 350.00 HTG
- Commission today/week/month: 35.00 HTG
- Balance: 385.00 HTG

All three screens must display matching values.
