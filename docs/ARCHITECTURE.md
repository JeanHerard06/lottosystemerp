# Lotto ERP Enterprise — Architecture

## Objectif
Lotto ERP Enterprise est une plateforme SaaS multi-tenant pour la gestion de loteries, agents, tickets, tirages, gains, cash sessions, notifications et rapports.

## Architecture cible

```text
Request
  ↓
Middleware: Auth → Subscription → Tenant → Permission
  ↓
Controller
  ↓
Service
  ↓
Repository
  ↓
PDO / MySQL
```

## Règles fondamentales

1. Aucun accès direct aux données d’un autre tenant.
2. Seul `super_admin` contrôle les tenants.
3. Un tenant ne peut jamais créer ou attribuer le rôle `super_admin`.
4. Les vues ne doivent pas contenir de logique SQL lourde.
5. Les actions doivent déléguer la logique métier aux Services.
6. Les Repositories centralisent les requêtes SQL.
7. Toutes les actions sensibles doivent produire un audit log.

## Modules clés

- Tenants & subscriptions
- Users / Roles / Permissions
- Agencies / Agents / Supervisors
- Lotteries / Schedules / Tirages
- Fiches / Tickets / Gains
- Cash Sessions / Ledger
- Risk management
- Notifications
- Reports
- Settings / Branding
- API / Mobile

## Convention recommandée

```text
views/{module}/index.php
views/{module}/create.php
views/{module}/edit.php
views/{module}/show.php

actions/{module}/store.php
actions/{module}/update.php
actions/{module}/delete.php

app/Services/{Module}Service.php
app/Repositories/{Module}Repository.php
```
