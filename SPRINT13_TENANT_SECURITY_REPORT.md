# Sprint 13 — Tenant Security & Data Isolation

## Objectif
Renforcer l’isolation des données par tenant et par rôle avant de continuer les modules fonctionnels.

## Réalisé
- Ajout de helpers de visibilité tenant/agency/agent dans `app/Helpers/tenant.php`.
- Ajout de `app/Helpers/Middleware.php` pour centraliser auth/tenant/permission.
- Dashboard refait avec vues séparées:
  - `super_admin`: vision globale plateforme.
  - `tenant_admin/admin`: uniquement le tenant courant.
  - `superviseur`: uniquement l’agence assignée.
  - `agent`: uniquement ses propres fiches, ventes, gains, balance et commissions.
- Rôles sécurisés:
  - `super_admin` est protégé.
  - Aucun tenant ne peut créer/attribuer/modifier un rôle `super_admin`.
  - Permissions SaaS/système filtrées pour les tenants.
- Rôles UI:
  - Liste et formulaires de rôles masquent les permissions plateforme hors super_admin.
- Migration `017_sprint13_tenant_security.sql`:
  - permissions système,
  - defaults rôles/permissions,
  - indexes tenant-scoped.

## Règles validées
- Super admin contrôle les tenants et la plateforme.
- Tenant admin/admin ne voit que son tenant.
- Superviseur voit seulement l’agence liée.
- Agent voit seulement ses propres données.
- Les permissions SaaS restent réservées à la plateforme.

## Note
Cette version consolide la sécurité centrale. Les prochains modules doivent utiliser les helpers de scope au lieu d’écrire manuellement des filtres `tenant_id` dispersés.
