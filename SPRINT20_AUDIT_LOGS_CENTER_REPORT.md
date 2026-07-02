# Sprint 20 — Audit Logs Center

## Objectif
Ajouter un centre d'audit exploitable par super_admin et tenant_admin pour suivre les actions sensibles sans mélanger les données des tenants.

## Ajouts
- Page `views/logs/index.php`
- Action `actions/logs/purge.php`
- Migration `022_sprint20_audit_logs_center.sql`
- Permissions `logs.view`, `logs.manage`
- Helper `audit_logs_query()`
- `audit_log()` enregistre maintenant `tenant_id`

## Sécurité
- Super admin voit tous les logs.
- Tenant admin voit uniquement les logs de son tenant.
- Purge limitée par tenant pour les non-super_admin.
- CSRF sur purge.

## Notes
Les anciens logs sans `tenant_id` restent visibles comme logs plateforme pour super_admin. Les nouveaux logs auront automatiquement le tenant de la session.
