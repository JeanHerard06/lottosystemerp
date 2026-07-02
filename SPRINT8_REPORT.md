# Sprint 8 — API, PWA Agent, Sauvegardes

## Livrables
- API REST tokenisée : login, logout, dashboard, fiches list/store, tirages list.
- PWA Agent : login, dashboard, nouvelle fiche, service worker/cache, manifest.
- Sauvegardes SQL manuelles dans `storage/backups`.
- Permissions ajoutées : `api.use`, `settings.manage`.
- Migration : `database/migrations/007_sprint8_api_pwa_settings.sql`.

## Points de test
1. Importer la migration 007.
2. Login API avec `POST /api/login.php`.
3. Ouvrir `/public/agent/index.html` et connecter un agent.
4. Créer une fiche PWA et vérifier `fiches`, `fiche_details`, `agent_transactions`.
5. Créer une sauvegarde via `/views/settings/backups.php`.

## Note
Le mode offline garde l’interface en cache. La synchronisation complète des fiches hors-ligne sera finalisée au Sprint 9 Release/Quality.
