# Module Lotteries CRUD

## Fait

- Ajout module `views/lotteries/` avec index, create, edit.
- Ajout actions `actions/lotteries/store.php`, `actions/lotteries/update.php` et `actions/lotteries/delete.php`.
- Ajout permission `lotteries.manage`.
- Ajout lien Sidebar dans la section Jeux.
- Filtrage tenant automatique avec `tenant_scope_clause()`.
- Super admin peut choisir le tenant lors de la création/modification.
- Tenant admin/superviseur restent limités à leur tenant.
- Audit logs sur création/modification.

## Sécurité

- Un tenant ne peut pas créer/modifier une lottery pour un autre tenant.
- `ensure_record_tenant()` protège l'édition.
- Les listes sont filtrées par `tenant_id` pour tous les rôles hors super_admin.

- Suppression protégée: impossible si des tirages ou fiches existent.
