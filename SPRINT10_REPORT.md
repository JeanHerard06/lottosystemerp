# Sprint 10 — SaaS Multi-Tenant Foundation

## Livrables
- Table `tenants` pour banques/opérateurs.
- Table `tenant_settings`.
- Table `tenant_subscriptions`.
- Ajout de `tenant_id` aux tables principales.
- Helper `app/Helpers/tenant.php`.
- Gestion super admin des tenants.
- Permissions `tenants.manage` et `superadmin.view`.
- Migration `database/migrations/010_multi_tenant.sql`.

## Objectif
Préparer la plateforme pour gérer plusieurs banques bòlèt sur une même installation, avec isolation progressive par `tenant_id`.

## Notes
Cette Sprint introduit la fondation SaaS. Les Sprints suivants doivent appliquer le filtrage tenant sur tous les modules critiques.
