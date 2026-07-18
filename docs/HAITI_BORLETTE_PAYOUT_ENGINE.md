# Moteur de paiement Bòlèt Ayiti — 60 / 20 / 10

## Règle intégrée

Le multiplicateur dépend de la position du numéro tiré:

- `payout_1 = 60` pour le premier lot;
- `payout_2 = 20` pour le deuxième lot;
- `payout_3 = 10` pour le troisième lot.

Formule:

```text
gain = mise × multiplicateur
```

Exemple avec une mise de 50 HTG:

- premier lot: `50 × 60 = 3 000 HTG`;
- deuxième lot: `50 × 20 = 1 000 HTG`;
- troisième lot: `50 × 10 = 500 HTG`.

## Portée des paramètres

Le moteur cherche les réglages dans cet ordre:

1. tenant + lottery;
2. tenant, toutes lotteries;
3. global + lottery;
4. global, toutes lotteries.

Les valeurs globales par défaut sont 60 / 20 / 10.

## Traçabilité

Chaque gain enregistre:

- `winning_position`;
- `payout_multiplier`;
- `amount_played`;
- `amount_won`.

Cela permet de vérifier exactement pourquoi et comment un gain a été calculé.
