# Sprint 3 — Fiches / Ventes / Ticket POS

## Objectif
Rendre le module de vente utilisable: création de fiches, lignes multiples, contrôle limites/blocages, détail fiche, ticket 80mm, reprint et annulation.

## Livrables
- Formulaire nouvelle fiche amélioré avec calcul total côté interface.
- Sélection de lotterie/tirage actif.
- Validation serveur des lignes: numéro, type de jeu, montant.
- Contrôle automatique des numéros bloqués.
- Contrôle automatique des limites par numéro sur la journée courante.
- Génération de code fiche unique.
- Page liste fiches avec filtres par date et statut.
- Page détail fiche.
- Ticket POS 80mm amélioré.
- Reprint ticket avec audit log.
- Annulation fiche avec permission `fiches.cancel`.
- Restriction d’accès:
  - Admin: toutes les fiches.
  - Superviseur: fiches de son agence.
  - Agent: seulement ses fiches.

## Fichiers principaux modifiés
- `actions/fiche_store.php`
- `actions/print_ticket.php`
- `actions/print_tiket.php`
- `actions/fiches/cancel.php`
- `views/fiche_create.php`
- `views/fiches.php`
- `views/fiches/show.php`
- `views/ticket.php`
- `app/Helpers/fiches.php`
- `database.sql`
- `database/migrations/003_sprint3_fiches.sql`

## Notes de test
1. Importer `database.sql` ou appliquer `database/migrations/003_sprint3_fiches.sql` si la base existe déjà.
2. Se connecter avec `admin / admin123`.
3. Créer un utilisateur agent et rattacher un agent.
4. Se connecter comme agent.
5. Créer une nouvelle fiche.
6. Ouvrir le détail et tester impression/reprint.
7. Tester annulation avec un rôle ayant `fiches.cancel`.

## Prochain Sprint
Sprint 4 — Tirages, résultats, calcul automatique des gains, fiches gagnantes et paiements.
