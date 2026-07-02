# Sprint 0 — Refactoring & Fondation

## Objectif
Stabiliser le projet avant d'ajouter de nouvelles fonctionnalités bòlèt.

## Réalisé
- Création de la structure Enterprise: `app/`, `database/migrations`, `database/seeders`, `storage/`, `routes/`, `public/assets`.
- Correction de la configuration PDO: `utf8mb4`, exceptions, fetch assoc, emulate prepares désactivé.
- Ajout helpers sécurité: `e()`, `redirect()`, validation basique.
- Ajout CSRF: `csrf_token()`, `csrf_field()`, `verify_csrf()`.
- Ajout audit helper: `audit_log()`.
- Amélioration login/logout avec `session_regenerate_id()`.
- Correction `fotter.php` avec alias vers `footer.php`.
- Correction `print_tiket.php` avec alias vers `print_ticket.php`.
- Ajout `views/agents.php` manquant.
- Refactor `agent_store.php` avec transaction, validation, CSRF, audit log.
- Refactor `fiche_store.php` avec transaction propre, validation lignes, blocage, limite, transaction vente, audit log.
- Refonte `database.sql` avec clés étrangères, indexes, statuts et seed admin.

## Identifiants de test
- Username: `admin`
- Password: `admin123`

## À faire au Sprint 1
- Roles & permissions complets.
- Middleware par rôle sur toutes les pages/actions.
- CRUD Users.
- Dashboard plus propre avec filtres par jour/agence/agent.

## Notes
Importer `database.sql` dans MySQL pour repartir sur une base propre.
