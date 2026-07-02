# Sprint 14 — Tenant Branding & System Settings

## Objectif
Consolider les paramètres tenant pour que chaque banque/tenant puisse personnaliser son identité, ses tickets et ses paramètres système sans voir ni modifier les données des autres tenants.

## Livrables
- Module Paramètres tenant: `/views/settings/tenant.php`
- Action de mise à jour: `/actions/settings/tenant_update.php`
- Helper paramètres: `/app/Helpers/settings.php`
- Migration: `/database/migrations/018_sprint14_tenant_branding_settings.sql`
- Ticket 80mm connecté aux paramètres tenant
- Topbar connecté au branding tenant
- Upload logo tenant sécurisé

## Paramètres ajoutés
- Nom commercial
- Téléphone
- Adresse
- Logo
- Devise
- Fuseau horaire
- Sous-titre ticket
- Footer ticket
- Couleur principale
- Couleur accent
- Paramètres SMTP de base

## Sécurité
- `tenant_admin` modifie uniquement les paramètres de son tenant.
- `super_admin` peut sélectionner un tenant et gérer ses paramètres.
- Upload logo limité à PNG/JPG/WebP, maximum 2MB.
- Audit log sur toute modification de paramètres.
- Protection CSRF sur le formulaire.

## Validation
- PHP syntax check exécuté sur tous les fichiers PHP: OK.
