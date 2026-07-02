# Sprint 18 — Notifications & Workflow

## Objectif
Ajouter un centre de notifications multi-tenant pour améliorer le suivi opérationnel du système Lotto ERP.

## Réalisé
- Table `notifications` avec tenant scope et notifications ciblées par utilisateur.
- Permissions `notifications.view` et `notifications.manage`.
- Helper `app/Helpers/notifications.php`.
- Page notifications avec filtres: toutes, lues, non lues.
- Création de notifications manuelles.
- Marquer une notification comme lue.
- Marquer toutes les notifications visibles comme lues.
- Suppression sécurisée des notifications.
- Badge notifications non lues dans la topbar.
- Lien notifications dans la sidebar.
- Notifications automatiques lors des changements de statut lottery:
  - vente fermée,
  - vente réouverte,
  - lottery tirée.

## Sécurité
- Tenant isolation sur les notifications.
- Un utilisateur tenant ne peut pas lire/supprimer une notification hors tenant.
- Les notifications user-specific ne sont visibles que par l'utilisateur ciblé.
- Super admin peut gérer les notifications globales et tenant.
- CSRF appliqué sur les actions POST.
- Audit logs pour création/suppression.

## Fichiers ajoutés
- `database/migrations/021_sprint18_notifications_workflow.sql`
- `app/Helpers/notifications.php`
- `views/notifications/index.php`
- `views/notifications/create.php`
- `actions/notifications/store.php`
- `actions/notifications/mark_read.php`
- `actions/notifications/mark_all_read.php`
- `actions/notifications/delete.php`

## Fichiers modifiés
- `includes/topbar.php`
- `includes/sidebar.php`
- `actions/lotteries/status.php`

## Prochain Sprint recommandé
Sprint 19 — Sécurité Enterprise:
- Login history
- Device/session management
- 2FA pour super_admin / tenant_admin
- Password policy avancée
- Remote logout / force password reset
