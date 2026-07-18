# Mobile Agent Financial Definitions

## Source of truth
All Agent Mobile financial screens now use `mobile_agent_financial_engine.php`.

## Definitions

- **Ventes**: valid fiche totals for the selected period.
- **Commission acquise**: commission calculated from game/agent rules.
- **Encaisse attendue**: opening cash + sales + deposits - paid gains - withdrawals.
- **À remettre**: sales + deposits - paid gains - withdrawals - earned commission.

Opening cash is excluded from **À remettre**, because it is the float assigned to the cash session.

## Compatibility
The API field `balance` remains available, but now aliases `amount_to_remit`.
New fields:

- `cash_on_hand`
- `amount_to_remit`
- `commission_earned`
- `opening_cash`
- `components`
- `definitions`

## Mobile UX
The dashboard displays **Encaisse** and **À remettre** separately. The old Balance screen is now the **Situation financière** screen with auditable components.
