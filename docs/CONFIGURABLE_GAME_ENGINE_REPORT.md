# Configurable Game Engine — Rapport

## Ajouts
- `game_types`: catalogue de jeux global ou spécifique au tenant.
- `game_payout_rules`: multiplicateurs par tenant, lottery, jeu et niveau de correspondance.
- Règles par défaut conservées: Bòlèt, Mariage, Lotto 3, Lotto 4.
- Moteurs disponibles: `borlette_position`, `marriage_any`, `exact_sequence3`, `exact_first`, `any_draw`.
- Validation configurable par expression régulière.
- Interface Web: `views/games/index.php`.
- API Mobile: `api/mobile/game_types_list.php`.
- Flutter charge désormais les jeux depuis l'API; aucune liste de jeux n'est codée en dur dans l'écran Nouvelle fiche.
- Le endpoint de vente mobile valide chaque ligne avec le Game Engine.
- Le calcul des gains utilise le moteur et les règles de paiement configurables, avec fallback sur les anciens paramètres.

## Migration
Exécuter `database/migrations/034_configurable_game_engine.sql` via `upgrade.php`.

## Compatibilité
Les anciens types `borlette`, `mariage`, `lotto3`, `lotto4` et leurs multiplicateurs restent compatibles.
