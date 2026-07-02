# Correction HY093 - fiche_store.php

## Problème
Lors de l'enregistrement d'une fiche, `actions/fiche_store.php` appelait `post_agent_transaction()` pour enregistrer la vente/commission dans `agent_transactions`.

Dans `app/Helpers/finance.php`, la requête SQL contenait 6 paramètres `?`, mais l'appel `execute()` envoyait 7 valeurs, incluant `cash_session_id`.

Erreur observée:

```text
SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens
```

## Correction
La fonction `post_agent_transaction()` enregistre maintenant explicitement `cash_session_id` dans la requête:

```sql
INSERT INTO agent_transactions(agent_id, cash_session_id, type, amount, description, reference_no, status, created_by)
VALUES (?, ?, ?, ?, ?, ?, 'posted', ?)
```

## Impact
- Vente fiche web corrigée.
- Commissions liées à la fiche corrigées.
- Paiement gains et transactions finances restent compatibles avec cash sessions.
- API/PWA/mobile qui utilisent `post_agent_transaction()` bénéficient aussi de la correction.

## Fichier modifié
- `app/Helpers/finance.php`
