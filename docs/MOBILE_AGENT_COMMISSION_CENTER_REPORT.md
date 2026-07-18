# Mobile Agent — Commission Center

## Objectif
Ajouter un module Commission dans l'application mobile Agent afin que l'agent puisse suivre ses commissions quotidiennes, hebdomadaires et mensuelles.

## Ajouts backend

### Endpoints API mobile
- `api/mobile/commissions/dashboard.php`
- `api/mobile/commissions/history.php`
- `api/mobile/commissions/details.php`
- `api/mobile/commissions/request.php`

### Correction workflow vente mobile
`api/mobile/fiches/store.php` publie désormais les commissions après la vente mobile, selon la même logique que la vente web:
- taux spécifique dans `commissions`
- fallback sur les taux de l'agent (`borlette_rate`, `mariage_rate`, `lotto3_rate`, `lotto4_rate`)
- fallback final sur `agents.commission`

## Ajouts Flutter

### Nouvel écran
- `mobile_app/lib/screens/commissions_screen.dart`

Fonctions:
- KPI commission aujourd'hui
- ventes aujourd'hui
- commission semaine
- commission mois
- historique jour/semaine/mois
- détail par jour
- détail par type de jeu
- demande de paiement commission

### Dashboard mobile
- Ajout d'une carte `Commission`
- Ajout du menu `Commissions`

## Tests recommandés
1. Login agent mobile.
2. Ouvrir une cash session.
3. Créer une fiche mobile avec plusieurs lignes.
4. Vérifier qu'une transaction `commission` est créée.
5. Ouvrir `Commissions` dans l'app.
6. Vérifier les KPI et l'historique.
7. Ouvrir le détail d'une journée.
8. Envoyer une demande de paiement.

## Notes
La demande de paiement commission est enregistrée côté serveur via audit log pour cette version. Le workflow d'approbation complet peut être connecté ensuite au module financier/superviseur.
