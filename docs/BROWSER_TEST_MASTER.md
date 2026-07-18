# Browser Test Guide — Lotto ERP Enterprise Master Functional

## 1) Installation locale
1. Place the project folder in your web root, for example `C:\web_dev\lotto_system`.
2. Open `http://localhost:8081/install.php`.
3. Use a clean development database, for example `lotto_system`.
4. Set the Super Admin login. Recommended test login: `admin / admin12345`.
5. After install, open `http://localhost:8081/views/login.php`.

> Important: use a test database. The installer executes migrations and is intended for a clean local environment.

## 2) First login checks
- Log in as Super Admin.
- Open Dashboard.
- Open Settings > Santé système.
- Confirm database status is OK.

## 3) SaaS / Tenant workflow
- Create a tenant/bank.
- Create one tenant admin user.
- Log out and log in as the tenant admin.
- Confirm the tenant admin cannot see other tenants.

## 4) Agency / Agent workflow
- Create one agency for the tenant.
- Create one agent attached to that agency.
- Give the agent the needed role/permissions.
- Log in as the agent.

## 5) Lottery workflow
- Create a lottery.
- Set draw time and close-before minutes.
- Confirm the lottery is open before cutoff.
- Confirm ticket sale is blocked after close time.

## 6) Cash session workflow
- Log in as agent.
- Open a cash session.
- Create a ticket/fiche.
- Print ticket.
- Close cash session.
- Log in as supervisor/admin and approve/reject the session.

## 7) Fiches / gains workflow
- Create fiche with multiple lines.
- Enter tirage/result.
- Run gain calculation.
- Pay a winning gain.
- Confirm paid gain appears in ledger/transactions.

## 8) Tenant isolation test
- Tenant A creates agency, agent, lottery, fiche.
- Tenant B logs in.
- Tenant B must not see Tenant A resources.

## 9) Common browser checks
Open DevTools:
- Console must have no JavaScript fatal errors.
- Network must not show 500 errors.
- Forms must show user-friendly errors, not raw SQL, whenever possible.

## 10) CLI checks
From the project root:

```bash
php scripts/smoke_test.php
php scripts/health_check.php
php cron/lottery_auto_close.php
php scripts/backup_database.php
```

Some scripts require a configured MySQL database and `.env`.
