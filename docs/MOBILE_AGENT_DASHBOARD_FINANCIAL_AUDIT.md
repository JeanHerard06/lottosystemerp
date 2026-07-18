# Mobile Agent Dashboard — Financial Audit

## Corrections

- One central calculation service now powers Dashboard and Balance APIs.
- Day boundaries use the tenant timezone through `TimeService` instead of `CURDATE()`.
- Cancelled tickets are excluded from sales and ticket counts.
- Gains are calculated from `gains.amount_won` when the gains table is available.
- Paid gains come from posted `agent_transactions` of type `gain`.
- Commission uses posted ledger entries first; old tickets fall back to a calculation from ticket lines and the agent's configured rates.
- Balance is recomputed from the ledger instead of trusting `agents.balance` blindly.
- The API exposes stored/computed balance variance for diagnostics.
- Tenant filters and posted-status filters are consistently enforced.

## Current ledger convention

```
balance = ventes + commissions + depots - gains_payes - retraits
```

This matches the existing `app/Helpers/finance.php` convention. The stored `agents.balance` remains a cache and is not the Dashboard source of truth.

## Dashboard diagnostics

The `dashboard.php` response now includes `data.diagnostics`:

- `commission_source`
- `commission_posted`
- `commission_calculated`
- `stored_balance`
- `computed_balance`
- `balance_variance`
- ledger totals by transaction type
- tenant timezone and exact daily range

## Expected result for the screenshot example

If today's sales are 2,160 HTG and the effective commission is 6%, the commission fallback is 129.60 HTG. Under the project's current ledger convention, and with no gains/deposits/withdrawals, balance is 2,289.60 HTG.
