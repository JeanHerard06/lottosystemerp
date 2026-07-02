# Sprint 17 — Lottery Closing & Draw Cutoff

## Objectif
Empêcher les agents de vendre sur une lottery au moment du tirage ou après le tirage.

## Fonctionnalités ajoutées
- Heure de tirage (`draw_time`) sur chaque lottery.
- Fermeture automatique avant tirage (`close_before_minutes`).
- Statut de vente: `open`, `closed`, `drawn`.
- Activation/désactivation fermeture automatique.
- Fermeture manuelle depuis le module Lotteries.
- Réouverture manuelle avec permission.
- Marquage manuel comme tirée.
- Blocage des ventes Web/API/Mobile si la lottery est fermée ou tirée.
- Auto-close si l’heure limite est dépassée au moment de la vente.
- Marquage `drawn` automatique lors de la création/calcul d’un tirage.

## Fichiers principaux
- `app/Helpers/lotteries.php`
- `database/migrations/020_sprint17_lottery_closing.sql`
- `actions/lotteries/status.php`
- `views/lotteries/index.php`
- `views/lotteries/create.php`
- `views/lotteries/edit.php`
- `actions/lotteries/store.php`
- `actions/lotteries/update.php`
- `actions/fiche_store.php`
- `api/fiches_store.php`
- `api/mobile/fiches/store.php`
- `actions/tirage_store.php`
- `actions/tirages/calculate.php`

## Règle de vente
Si une lottery tire à 12:30 et `close_before_minutes = 10`, les ventes ferment à 12:20. Après 12:20, toute création de fiche est refusée.

## Permission ajoutée
- `lotteries.close`

## Validation
- Syntaxe PHP validée sur tous les fichiers PHP.
