# Browser Test Guide — Lotto ERP Enterprise RC1

## 1. Prepare environment

Use a test database, never production.

Suggested databases:

- `lotto_erp_dev`
- `lotto_erp_test`
- `lotto_erp_demo`

Enable debug only in local/dev.

## 2. Create test accounts

- Super Admin
- Tenant Admin A
- Tenant Admin B
- Supervisor A
- Agent A
- Agent B

## 3. Browser tests

Open:

```text
http://localhost:8081/admin/system/health.php
http://localhost:8081/admin/system/checklist.php
```

Use the checklist to verify login, roles, tenant isolation, lottery closing, ticket printing, gains, cash sessions, reports, notifications, API/mobile behavior.

## 4. DevTools checks

In Chrome/Edge:

- F12 → Console: no JS errors
- F12 → Network: no 500 errors
- Toggle Device Toolbar: test mobile sidebar and tables

## 5. CLI checks

```bash
php scripts/smoke_test.php
php cron/lottery_close.php
php scripts/backup_database.php
```

## 6. Tenant isolation test

Create data under Tenant A. Login as Tenant B and confirm Tenant A data does not appear in users, agencies, agents, tickets, reports, lotteries, notifications.

## 7. Release gate

Do not release RC1 until:

- Health page is green
- Browser checklist is complete
- Smoke test passes
- Tenant isolation tests pass
- Critical workflows pass
