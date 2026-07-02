# Sprint 4 — Tirages + Calcul automatique des gains

## Objectif
Permettre à l'administration de saisir les résultats de tirages, calculer automatiquement les fiches gagnantes, suivre les montants gagnés et marquer les gains comme payés.

## Livrables
- Refonte `views/tirages.php` avec statistiques par tirage.
- Refonte `views/tirage_create.php` avec lotterie, CSRF et layout global.
- Nouvelle page `views/tirages/show.php` pour détail tirage + calcul/recalcul des gains.
- Nouveau moteur `app/Helpers/gains.php`.
- Action `actions/tirages/calculate.php`.
- Action de compatibilité `actions/check_winners.php`.
- Refonte `views/gagnants.php` avec filtres tous/non payés/payés.
- Paiement de gain via `actions/gains/pay.php`.
- Mise à jour `database.sql`.
- Migration `database/migrations/004_sprint4_tirages_gains.sql`.

## Règles de calcul incluses
- `borlette`: gagne si le numéro joué correspond à l'un des lots du tirage.
- `mariage`: gagne si les deux numéros joués sont présents dans les lots du tirage.
- `lotto3`: gagne si le numéro joué correspond à la concaténation des 3 lots.
- `lotto4`: gagne si le numéro joué correspond au 1er lot lorsque celui-ci est un numéro à 4 chiffres.
- Le montant gagné = mise × taux actif dans `primes`.

## Permissions ajoutées
- `gains.calculate`
- `gains.pay`

## Notes de test
1. Créer une fiche pour la date du jour.
2. Créer un tirage avec les numéros correspondants.
3. Ouvrir le tirage puis cliquer sur Calculer/Recalculer les gains.
4. Vérifier la page Gagnants.
5. Tester le paiement d'un gain.
