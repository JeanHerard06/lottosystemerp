# Tenant Game Payout Rules

## Scope

The payout engine now supports tenant-configurable multipliers for:

- Borlette: first, second and third result positions.
- Mariage.
- Lotto 3.
- Lotto 4.

The lookup priority is:

1. Tenant + selected lottery.
2. Tenant default (all lotteries).
3. Global + selected lottery.
4. Global default.
5. Legacy `primes` / `rates` fallback when no setting exists.

## Settings keys

| Key | Default multiplier |
|---|---:|
| `payout_1` | 60 |
| `payout_2` | 20 |
| `payout_3` | 10 |
| `payout_mariage` | 500 |
| `payout_lotto3` | 1000 |
| `payout_lotto4` | 5000 |

The amount won is calculated as:

`amount_won = stake × resolved_multiplier`

## Tenant isolation

A tenant administrator can only save rules for its own tenant and its own lotteries. The platform super administrator may maintain global fallback rules.

## Auditability

The resolved multiplier is saved in `gains.payout_multiplier`, so later changes to settings do not alter historical gain calculations.

## Important

This change makes payout multipliers configurable. The existing matching logic for Mariage, Lotto 3 and Lotto 4 is preserved to avoid changing winning behavior unexpectedly during the stable release.
