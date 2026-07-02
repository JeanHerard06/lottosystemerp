# Ticket + Password Management Fix

## Corrections réalisées

### 1. Impression ticket
- Correction du cas `Fiche introuvable` lorsque les anciennes fiches n'avaient pas encore `tenant_id`.
- Le ticket utilise maintenant `COALESCE(f.tenant_id, a.tenant_id)` pour retrouver le tenant réel via l'agent.
- Backfill automatique de `fiches.tenant_id` pendant l'impression si la fiche est ancienne et que l'agent a un tenant.
- Contrôle d'accès renforcé :
  - super_admin : accès global ;
  - tenant_admin/admin : accès tenant uniquement ;
  - superviseur : accès agence uniquement ;
  - agent : accès fiches de son compte uniquement.
- Audit log pour impression autorisée et tentative refusée.

### 2. Gestion mot de passe
- Ajout page `views/users/change_password.php`.
- Ajout action `actions/users/change_password.php`.
- Chaque utilisateur connecté peut changer son propre mot de passe avec :
  - mot de passe actuel obligatoire ;
  - nouveau mot de passe minimum 8 caractères ;
  - confirmation obligatoire ;
  - nouveau mot de passe différent de l'ancien.
- Ajout lien sidebar `Changer mot de passe`.
- Admin/tenant admin garde la possibilité de réinitialiser le mot de passe via edit user.
- Validation minimum 8 caractères dans create/edit user.
- Vérification doublon username dans create/update user.
- Audit log changement mot de passe et tentative échouée.

## Validation
- Syntaxe PHP validée sur tout le projet avec `php -l`.
