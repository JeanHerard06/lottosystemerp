# Sprint Sécurité Tenant + Tenant Register

## Réalisé
- Helper central `app/Helpers/tenant.php` renforcé.
- `super_admin` séparé des admins tenant.
- Contrôle tenant actif/suspendu/expiré au login et pendant la session.
- Correction permission globale: un admin tenant ne reçoit plus automatiquement tous les droits super_admin.
- Module public `views/tenant_register.php`.
- Enregistrement demande: `actions/tenant_register_store.php`.
- Back-office super_admin demandes tenant: `views/tenant_registrations/*`.
- Approbation/rejet: `actions/tenant_registrations/approve.php`, `reject.php`.
- Création automatique tenant + tenant_admin + subscription trial après approbation.
- Migration `013_tenant_security_register.sql`.

## Règles de sécurité
- `super_admin`: contrôle tous les tenants et demandes.
- `tenant_admin`: voit uniquement son `tenant_id`.
- Tenant suspendu/expiré: login bloqué.
- Nouveau tenant: statut `pending` jusqu'à approbation super_admin.

## Test rapide
1. Aller sur `/views/tenant_register.php`.
2. Soumettre une demande.
3. Se connecter comme `admin / admin123`.
4. Aller sur `SaaS > Demandes tenant`.
5. Approuver et créer un tenant_admin.
6. Tester login tenant_admin; il ne doit pas être super_admin.
