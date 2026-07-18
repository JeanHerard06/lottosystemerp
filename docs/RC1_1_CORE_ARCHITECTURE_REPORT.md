# RC1.1 — Core Architecture Cleanup

## Scope

This release focuses on the Web dashboard architecture without changing the
validated Mobile Agent business rules or the responsive layout.

## Changes

- Moved all Web dashboard SQL into `DashboardRepository`.
- Reduced `DashboardService` to role/scope orchestration and read-model assembly.
- Preserved Mobile Agent's shared financial engine as the single source of truth
  for Agent Web KPIs.
- Added explicit tenant, agency and agent scope builders.
- Replaced `CURDATE()` subscription logic with tenant-aware `TimeService` dates.
- Centralized dashboard view helpers in
  `views/components/dashboard_components.php`.
- Added bounded limits for dashboard lists to avoid accidental oversized queries.
- Kept Super Admin, Tenant, Supervisor and Agent outputs backward compatible.

## Source-of-truth rule

Agent financial values displayed on Web and Mobile are both produced by:

`mobile_agent_dashboard_metrics()` → shared financial engine.

The Web dashboard does not recalculate Agent sales, commission, gains, cash or
amount-to-remit independently.

## QA performed

- PHP syntax validation on the full project.
- Static verification that `DashboardService` contains no `SELECT` statement.
- Static verification that dashboard view helpers exist in a reusable component.
- Responsive CSS and JavaScript left intact.
