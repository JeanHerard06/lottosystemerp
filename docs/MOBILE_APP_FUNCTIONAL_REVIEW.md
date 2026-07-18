# Mobile App Functional Review

## Objectif
Cette version rend le dossier `mobile_app/` réellement exploitable comme MVP Agent Mobile connecté au backend PHP.

## Ce qui a été ajouté/corrigé

### Flutter
- `main.dart` démarre maintenant sur `LoginScreen` ou `DashboardScreen` selon le token local.
- `ApiClient` centralisé avec gestion d'erreurs, token Bearer et timeout.
- Login mobile réel via `api/mobile/login.php`.
- Dashboard agent connecté à `api/mobile/dashboard.php`.
- Cash session mobile: statut, ouverture, fermeture.
- Création fiche mobile avec plusieurs lignes + sélection lottery.
- Fallback offline: si l'API est indisponible, le ticket est sauvegardé en SQLite avec `pending_sync`.
- Sync manuel des tickets offline.
- Liste des fiches serveur + tickets locaux.
- Liste tirages.
- Balance + transactions agent.
- Vérification ticket par QR/code.

### Backend API mobile
- `api/mobile/lotteries_list.php`
- `api/mobile/cash/status.php`
- `api/mobile/cash/open.php`
- `api/mobile/cash/close.php`
- `api/mobile/tickets/verify.php`
- `api/mobile/fiches/store.php` accepte maintenant `local_uuid` et marque `sync_status='synced'`.

## Configuration mobile
Dans `mobile_app/lib/config.dart`, modifier l'URL selon l'environnement:

```dart
const String apiBaseUrl = 'http://10.0.2.2/lotto_system/api/mobile';
```

- Android emulator: `10.0.2.2`
- Téléphone physique: IP LAN de l'ordinateur, exemple `http://192.168.1.25:8081/api/mobile`

## Test rapide
1. Installer la base et lancer le backend.
2. Vérifier que `http://localhost:8081/api/mobile/login.php` existe.
3. Dans `mobile_app/`:

```bash
flutter pub get
flutter run
```

4. Login avec un utilisateur agent.
5. Ouvrir une cash session.
6. Créer une fiche.
7. Couper Internet ou changer l'URL API pour tester offline.
8. Créer une fiche offline.
9. Remettre l'API correcte et cliquer Sync.

## Limites restantes
- Impression Bluetooth réelle reste à connecter à un package matériel spécifique (`blue_thermal_printer`, `esc_pos_bluetooth`, etc.).
- Notifications push Firebase non activées.
- Le module Supervisor/Admin mobile reste à développer en Phase 2.
