# Sprint 1 — Core System

## Objectif
Mettre en place le noyau administratif du système: rôles, permissions, utilisateurs et dashboard propre.

## Livrables
- Tables RBAC ajoutées dans `database.sql`:
  - `roles`
  - `permissions`
  - `user_roles`
  - `role_permissions`
- Seed initial:
  - Rôle Admin
  - Rôle Superviseur
  - Rôle Agent
  - Permissions principales par module
  - Admin `admin / admin123`
- Helper permissions:
  - `app/Helpers/permissions.php`
  - `has_permission()`
  - `require_permission()`
- Module Utilisateurs:
  - `views/users/index.php`
  - `views/users/create.php`
  - `views/users/edit.php`
  - `actions/users/store.php`
  - `actions/users/update.php`
- Module Rôles:
  - `views/roles/index.php`
  - `views/roles/create.php`
  - `views/roles/edit.php`
  - `actions/roles/store.php`
  - `actions/roles/update.php`
- Module Permissions:
  - `views/permissions/index.php`
- Sidebar dynamique selon permissions.
- Dashboard amélioré:
  - Ventes
  - Fiches
  - Gains
  - Balance
  - Agents actifs
  - Utilisateurs
  - Rôles
  - Top agents

## Notes techniques
Le champ `users.role` reste présent pour compatibilité avec les anciens modules, mais les nouvelles règles d'accès se basent sur les tables RBAC.

## Login test
- Username: `admin`
- Password: `admin123`

## Prochain sprint recommandé
Sprint 2 — Agencies, Supervisors, Agents CRUD complet avec permissions et affectation agence.
