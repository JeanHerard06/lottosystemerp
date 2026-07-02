# Full Tenant Security Audit & Dashboard Fix

## Corrections principales

- Correction de l'erreur `Unknown column g.tenant_id` dans `views/dashboard.php`.
- Ajout de `tenant_id` dans la table `gains` au niveau du schéma principal.
- Ajout de `tenant_id` dans les migrations multi-tenant pour `gains`.
- Backfill automatique de `gains.tenant_id` depuis `fiches.tenant_id` via `fiche_details`.
- Correction du calcul des gains pour enregistrer `tenant_id` lors du calcul/recalcul des tirages.
- Dashboard rendu plus sûr: les gains sont filtrés via la fiche liée, ce qui évite les erreurs si une ancienne base n'a pas encore été migrée.
- Ajout de la migration `014_full_tenant_audit_fix.sql`.

## Règle de sécurité appliquée

- `super_admin`: vue globale plateforme.
- autres rôles: accès strictement limité à `current_tenant_id()`.
- les gains héritent le tenant de la fiche gagnante.

## À appliquer sur votre base existante

Exécuter:

```sql
source database/migrations/014_full_tenant_audit_fix.sql;
```

Puis recharger le dashboard.
