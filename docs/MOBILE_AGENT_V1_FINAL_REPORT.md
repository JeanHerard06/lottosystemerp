# Mobile Agent v1.0 Final Stabilization

## Objectif
Rendre le workflow Agent Mobile plus fiable pour la vente de fiche, le chargement des lotteries, la validation des lignes et la sauvegarde offline.

## Corrections principales

### 1. NewFicheScreen refactorisé
- Ajout de modèles typés: `Lottery`, `Tirage`, `TicketPlay`.
- Ajout de repositories: `LotteryRepository`, `TicketRepository`.
- Ajout d'un service de validation: `TicketValidationService`.
- Ajout d'un dialog de prévisualisation avant enregistrement.
- Chargement des lotteries avec erreurs visibles au lieu d'un `catch` silencieux.
- Chargement des tirages automatiquement après choix de lottery.
- Désactivation du bouton si la lottery est fermée.
- Calcul total en temps réel.
- Nettoyage des controllers avec `dispose()`.

### 2. Validation des jeux
- Borlette: 2 chiffres.
- Mariage: format `12-45`.
- Lotto3: 3 chiffres.
- Lotto4: 4 chiffres.
- Détection des doublons dans une même fiche.

### 3. API Mobile Lotteries
- `api/mobile/lotteries_list.php` corrige le filtrage tenant.
- Auto-close est appliqué sans casser la réponse JSON.
- Option `open_only=0` disponible pour récupérer aussi les lotteries fermées.

### 4. API Mobile Tirages
- `api/mobile/tirages_list.php` accepte `?lottery_id=ID`.
- Filtrage tenant renforcé.

## Fichiers modifiés / ajoutés
- `mobile_app/lib/screens/new_fiche_screen.dart`
- `mobile_app/lib/models/lottery.dart`
- `mobile_app/lib/models/tirage.dart`
- `mobile_app/lib/models/ticket_play.dart`
- `mobile_app/lib/repositories/lottery_repository.dart`
- `mobile_app/lib/repositories/ticket_repository.dart`
- `mobile_app/lib/services/ticket_validation_service.dart`
- `mobile_app/lib/widgets/ticket_preview_dialog.dart`
- `api/mobile/lotteries_list.php`
- `api/mobile/tirages_list.php`

## Tests recommandés
1. Login mobile agent.
2. Ouvrir cash session.
3. Aller dans Nouvelle fiche.
4. Vérifier que le dropdown Lottery charge les lotteries ouvertes du tenant.
5. Choisir une lottery, vérifier que les tirages se chargent.
6. Tester validation Borlette / Mariage / Lotto3 / Lotto4.
7. Tester preview ticket.
8. Sauvegarder avec serveur disponible.
9. Couper le serveur et tester sauvegarde offline.
10. Relancer serveur et synchroniser.

## Notes
Si vous testez sur un téléphone physique, changez `mobile_app/lib/config.dart` pour utiliser l'adresse IP LAN de l'ordinateur, par exemple `http://192.168.1.25:8081/api/mobile`.
