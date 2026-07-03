# Dashboard v2 + BI Monitor

## Objectif
Créer des dashboards par rôle pour Lotto ERP Enterprise v2.0.

## Dashboards

### Super Admin
- Tenants actifs
- Revenus SaaS
- Abonnements expirés
- API usage
- Santé système
- Erreurs / jobs échoués

### Tenant Admin
- Ventes aujourd’hui
- Gains payés
- Profit
- Lotteries ouvertes / fermées
- Cash sessions ouvertes
- Risk alerts
- Top agents
- Top numéros

### Superviseur
- Ventes par agent
- Sessions à approuver
- Claims en attente
- Alerts fraude
- Tickets récents

### Agent
- Mes ventes
- Ma commission
- Ma balance
- Tickets récents
- Tirages ouverts

## Data rules
Toutes les requêtes doivent respecter tenant_id sauf super_admin.

## BI widgets
- Sales trend
- Profit trend
- Top numbers
- Top agents
- Cash sessions monitor
- Risk monitor
- Lottery status monitor
