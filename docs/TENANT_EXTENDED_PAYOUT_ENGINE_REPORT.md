# Tenant Extended Payout Engine Report

Implemented on top of the stable Haiti 60/20/10 payout package.

## Completed

- Added tenant/lottery-scoped multipliers for Mariage, Lotto 3 and Lotto 4.
- Added migration `033_tenant_extended_game_payouts.sql`.
- Added secure update action with CSRF, permission and tenant/lottery validation.
- Extended the gain engine to resolve scoped settings before legacy rates.
- Added configuration UI under Primes / Taux.
- Preserved historical audit through `gains.payout_multiplier`.
- Preserved current matching rules to prevent regression.

## Defaults

- Mariage: ×500
- Lotto 3: ×1000
- Lotto 4: ×5000

Each tenant can replace these defaults globally or for a selected lottery.
