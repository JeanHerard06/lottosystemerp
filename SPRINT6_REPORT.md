# Sprint 6 — Finances, Balances, Commissions

## Objectif
Mettre en place un module finance stable pour gérer les dépôts, retraits, commissions, paiements de gains et balances agents.

## Livrables
- Dashboard finances
- Ledger transactions agents
- Dépôts / retraits avec CSRF + permissions
- Annulation transaction avec recalcul de balance
- Balances agents détaillées
- Gestion commissions par agent et par type de jeu
- Helper finance centralisé
- Intégration automatique des ventes dans le ledger
- Intégration automatique des commissions lors de la vente d'une fiche
- Intégration paiement gain dans le ledger
- Migration SQL Sprint 6
- Audit logs sur transactions et commissions

## Nouvelles permissions
- finances.view
- commissions.manage
- transactions.void

## Fichiers ajoutés/modifiés
- app/Helpers/finance.php
- actions/finances/store.php
- actions/finances/void.php
- actions/commissions/update.php
- views/finances/index.php
- views/finances/create.php
- views/finances/transactions.php
- views/finances/agents.php
- views/commissions/index.php
- database/migrations/006_sprint6_finances.sql
- database.sql
- includes/sidebar.php
- actions/fiche_store.php
- actions/gains/pay.php

## Notes de test
- Syntaxe PHP validée avec `php -l` sur tous les fichiers PHP.
- Pour une installation existante, exécuter `database/migrations/006_sprint6_finances.sql`.
- Pour une nouvelle installation, importer `database.sql`.
