# Correctif Tenant Roles & Dashboard

## Décision sécurité
Un tenant ne doit jamais pouvoir attribuer `super_admin` à un utilisateur. Ce rôle est réservé au compte plateforme qui contrôle tous les tenants.

## Corrections appliquées
- Ajout des fonctions de sécurité RBAC dans `app/Helpers/permissions.php`:
  - `assignable_role_slugs()`
  - `can_assign_role_slug()`
  - `normalize_system_role()`
  - `filter_allowed_role_ids()`
  - `assert_user_mutable()`
- `actions/users/store.php` bloque toute attribution de `super_admin`.
- `actions/users/update.php` empêche un tenant de modifier un super admin ou d’attribuer ce rôle.
- `views/users/create.php` et `views/users/edit.php` retirent `super_admin` des choix tenant.
- `views/users/index.php` filtre les utilisateurs par tenant pour les non super_admin.
- `views/dashboard.php` est maintenant tenant-aware:
  - Super admin voit les statistiques globales.
  - Tenant voit uniquement ses ventes, fiches, gains, agents, utilisateurs et dernières fiches.

## Règle finale
- `super_admin`: plateforme globale, tous tenants.
- `tenant_admin/admin`: tenant courant uniquement.
- `superviseur`: tenant courant uniquement.
- `agent`: données de vente liées à son accès.
