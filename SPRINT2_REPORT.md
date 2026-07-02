# Sprint 2 — Agences, Superviseurs, Agents

## Objectif
Structurer la gestion réseau: agences, superviseurs et agents rattachés à une agence.

## Livré
- CRUD Agences: liste, création, modification.
- CRUD Superviseurs: liste, création, modification.
- Agents rattachés à une agence.
- Superviseur limité à son agence pour créer/voir les agents.
- Permissions ajoutées: `agencies.manage`, `supervisors.manage`.
- Sidebar mise à jour selon permissions.
- Audit logs sur création/modification agences, superviseurs et agents.
- Migration Sprint 2 ajoutée.

## Fichiers principaux
- `views/agencies/*`
- `actions/agencies/*`
- `views/supervisors/*`
- `actions/supervisors/*`
- `views/agents.php`
- `views/agent_create.php`
- `actions/agent_store.php`
- `includes/sidebar.php`
- `database.sql`
- `database/migrations/002_sprint2_agencies_supervisors.sql`

## Login test
- admin / admin123

## Prochain Sprint
Sprint 3 — Fiches, ventes, tickets 80mm et contrôle de base avant vente.
