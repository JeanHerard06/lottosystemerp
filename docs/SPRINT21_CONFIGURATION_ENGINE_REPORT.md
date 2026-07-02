# Sprint 21 — Configuration ERP & Business Rules

## Objectif
Rendre les règles du système configurables et préparer le moteur Lottery/Schedule/Cron.

## Livrables
- Table `system_settings` pour les règles globales.
- Table `lottery_schedules` pour gérer les horaires par lottery, tenant et jour.
- Table `cron_runs` pour journaliser les tâches automatiques.
- Page `/views/settings/system.php` pour les paramètres système.
- Page `/views/lotteries/schedules.php` pour les horaires lotteries.
- Cron `cron/auto_close_lotteries.php` pour fermer automatiquement les lotteries selon l'horaire.
- Page `/views/settings/health.php` pour le health dashboard.
- Service `LotteryScheduleService` pour centraliser la logique d'ouverture/fermeture.
- Helper `system_settings.php`.

## Sécurité
- `system.settings` réservé au super_admin.
- `lottery_schedules.manage` accessible au super_admin et tenant_admin/admin.
- Tenant isolation sur les horaires.
- CSRF sur les actions.

## Migration
`database/migrations/023_sprint21_configuration_engine.sql`

## Prochaine étape recommandée
Sprint 22 — QA & Code Audit complet: vérifier chaque CRUD, tenant isolation, permissions, API et workflows cash/lottery.
