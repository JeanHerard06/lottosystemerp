# Rapport — Intégration du barème 60 / 20 / 10

## Modifications

- Ajout de la table `game_settings`.
- Valeurs par défaut `payout_1=60`, `payout_2=20`, `payout_3=10`.
- Ajout de `winning_position` et `payout_multiplier` dans `gains`.
- Ajout d'un moteur de calcul centralisé dans `app/Helpers/gains.php`.
- Ajout d'une interface de configuration dans `Primes / Taux`.
- Paramètres isolés par tenant et extensibles par lottery.
- Conservation du moteur existant pour Mariage, Lotto 3 et Lotto 4.

## Compatibilité

- MySQL 8+
- MariaDB
- Multi-tenant
- Recalcul refusé quand un gain du tirage est déjà payé
