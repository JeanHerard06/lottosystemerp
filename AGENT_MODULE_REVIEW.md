# Revizyon Modil Agents

## Koreksyon prensipal
- Korije fatal error `Call to undefined function require_auth()` nan `app/Helpers/permissions.php`.
- Rann `permissions.php` endepandan pou li kapab mache nan `actions/*` san `includes/auth.php`.
- Ajoute menm pwoteksyon an nan `app/Helpers/tenant.php`.

## CRUD Agents
- Liste agents ak aksyon.
- Create agent ak validation.
- Edit agent.
- Update agent.
- Toggle actif/inactif.
- Delete agent: si agent lan deja gen fiches, sistèm nan dezaktive li olye li kraze istorik la.

## Sekirite
- CSRF sou tout aksyon agents.
- Permission `agents.manage` sou create/update/toggle/delete.
- Permission `agents.view` sou listing.
- Superviseur limite sou agence li.
- Validation username unique.

## Fichye ajoute/modifye
- `app/Helpers/permissions.php`
- `app/Helpers/tenant.php`
- `views/agents.php`
- `views/agent_edit.php`
- `actions/agent_store.php`
- `actions/agent_update.php`
- `actions/agent_toggle.php`
- `actions/agent_delete.php`

## Test
- `php -l` pase sou tout fichye PHP yo.
