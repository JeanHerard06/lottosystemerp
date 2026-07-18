# Mobile Agent Pay Gain Report

## Ajouté
- API `api/mobile/gains/verify.php`
- API `api/mobile/gains/pay.php`
- API `api/mobile/gains/history.php`
- Écran Flutter `PayGainScreen`
- Bouton `Payer gain` dans le dashboard Agent
- Bouton `Payer gain` depuis l'écran QR Verify

## Règles métier
- Vérification du ticket par code/QR.
- Paiement autorisé seulement si le ticket est gagnant et non payé.
- Paiement exige une cash session ouverte pour l'agent connecté.
- Paiement filtré par tenant.
- Paiement marque les lignes `gains` comme payées quand les colonnes existent (`is_paid`, `paid_at`, `paid_by`).
- Une transaction agent de type `gain` est créée avec la cash session.
- Audit log créé sans bloquer le paiement si la table n'est pas disponible.

## Notes compatibilité
Les endpoints vérifient dynamiquement les colonnes optionnelles (`tenant_id`, `is_paid`, `paid_at`, `paid_by`, `paid_cash_session_id`, `reference_no`, `status`) pour éviter les erreurs SQL sur les installations existantes.
