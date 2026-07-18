# Web / Mobile Agent Dashboard Alignment

## Objective
Align the Web Agent dashboard with the already validated Mobile Agent dashboard without changing the mobile application.

## Single source of truth
Both dashboards now use:

- `mobile_agent_dashboard_metrics()`
- `mobile_agent_financial_summary()`
- `mobile_agent_financial_balance()`
- tenant timezone through `TimeService`
- the same tenant and agent scope
- the same commission rules and valid fiche filters

## Aligned KPIs

- Fiches today
- Sales today
- Gains
- Gains paid
- Earned commission
- Expected cash on hand
- Amount to remit
- Unread notifications
- Open cash-session status and expected cash

## Financial definitions

- **Expected cash on hand** = opening cash + sales + deposits - paid gains - withdrawals
- **Earned commission** = commission calculated from active game/agent rules
- **Amount to remit** = sales + deposits - paid gains - withdrawals - commission

## Web-specific work

- Agent role receives a dedicated dashboard layout.
- Non-agent dashboards preserve their previous global/tenant/supervisor behavior.
- Agent KPI cards are responsive.
- Tables continue transforming to cards on mobile.
- Financial cards link to the corresponding Web modules.

## Compatibility

Schema-inspection helpers are now available when the shared financial engine is called from Web requests, without loading Mobile bearer-token authentication.
