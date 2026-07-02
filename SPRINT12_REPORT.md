# Sprint 12 — Application Mobile Agent / Flutter

## Objectif
Mettre en place la fondation mobile pour les agents: login, dashboard, création fiche, liste fiches, tirages et balance.

## Livrables
- API mobile sous `/api/mobile/`
- Authentification mobile par `mobile_token`
- Création de fiche via JSON API
- Liste des fiches agent
- Dashboard agent mobile
- Balance + dernières transactions
- Liste des tirages
- Skeleton Flutter dans `/mobile_app/`
- Migration `012_sprint12_mobile_agent.sql`

## Fichiers importants
- `api/mobile/login.php`
- `api/mobile/dashboard.php`
- `api/mobile/fiches/store.php`
- `api/mobile/fiches/list.php`
- `api/mobile/tirages_list.php`
- `api/mobile/balance.php`
- `mobile_app/lib/main.dart`
- `mobile_app/lib/screens/*`

## Base de données
Champs ajoutés:
- `users.mobile_token`
- `fiches.sync_source`
- `fiches.device_id`

Permissions ajoutées:
- `mobile.api.use`
- `mobile.fiches.create`
- `mobile.dashboard.view`

## Notes de test
1. Exécuter migration Sprint 12.
2. Configurer `mobile_app/lib/config.dart` avec l'URL backend.
3. Lancer `flutter pub get` puis `flutter run`.
4. Tester login agent.
5. Créer une fiche depuis mobile et vérifier qu'elle apparaît dans le back-office avec `sync_source = mobile`.

## Prochain Sprint
Sprint 13 — IA / Détection de risques et comportements suspects.
