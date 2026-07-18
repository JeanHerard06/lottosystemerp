# Lotto ERP Mobile App

MVP mobile pour les agents Lotto ERP Enterprise.

## Fonctionnalités MVP
- Login agent
- Dashboard agent
- Cash session open/close
- Création fiche multi-lignes
- Offline queue SQLite
- Sync manuel des tickets offline
- Liste fiches
- Liste tirages
- Balance agent
- Scan/Vérification QR ou code fiche

## Configuration API
Modifier `lib/config.dart`:

```dart
const String apiBaseUrl = 'http://10.0.2.2/lotto_system/api/mobile';
```

Pour un appareil physique, utilisez l'IP de la machine serveur.

## Lancer

```bash
flutter pub get
flutter run
```

## Prochaines étapes
- Bluetooth printing réel
- Notifications push
- Supervisor mobile
- Tenant admin mobile
- API v2 complète
