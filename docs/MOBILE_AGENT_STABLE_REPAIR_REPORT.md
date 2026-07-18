# Mobile Agent Stable Repair Report

## Objectif
Stabiliser le module Agent Mobile après les régressions provoquées par des patchs successifs.

## Corrections appliquées

### 1. API JSON mobile
- `api/mobile/auth.php` nettoie désormais tout buffer avant chaque réponse JSON.
- Ajout d'un gestionnaire d'exceptions pour empêcher les warnings/HTML de casser Flutter.
- Ajout des helpers sûrs :
  - `mobile_api_has_table()`
  - `mobile_api_has_column()`
  - `mobile_api_columns()`

### 2. Commissions
- `api/mobile/commissions/details.php` corrigé.
- `api/mobile/commissions/dashboard.php` corrigé.
- `api/mobile/commissions/history.php` corrigé.
- Suppression des dépendances forcées à `reference_no`, `status`, `tenant_id` quand les colonnes ne sont pas présentes.
- Tenant filter appliqué quand la colonne existe.

### 3. Fiches / Tickets serveur
- `api/mobile/fiches/show.php` accepte maintenant :
  - `id`
  - `code` / `fiche_code`
  - `local_uuid`
- Flutter envoie `id + code + local_uuid` quand possible.
- Réponse JSON uniforme même quand la fiche est introuvable.

### 4. Gains
- Ajout de `mobile_app/lib/screens/gains_screen.dart`.
- Ajout du bouton **Gains** dans le dashboard Agent.
- Ajout des KPI :
  - Gains
  - Gains payés
- `api/mobile/gains/history.php` sécurisé.

### 5. Debug API Flutter
- `ApiClient` affiche maintenant un extrait de la réponse brute quand l'API retourne un body non JSON.
- Cela rend les futures erreurs beaucoup plus faciles à diagnostiquer.

## Fichiers modifiés
- `api/mobile/auth.php`
- `api/mobile/dashboard.php`
- `api/mobile/commissions/details.php`
- `api/mobile/commissions/dashboard.php`
- `api/mobile/commissions/history.php`
- `api/mobile/fiches/show.php`
- `api/mobile/gains/history.php`
- `mobile_app/lib/api_client.dart`
- `mobile_app/lib/screens/dashboard_screen.dart`
- `mobile_app/lib/screens/fiche_detail_screen.dart`
- `mobile_app/lib/screens/gains_screen.dart`

## Tests recommandés
1. Login Agent
2. Dashboard : vérifier Gains, Gains payés, Commissions, Notifications
3. Commissions > Détail jour
4. Mes fiches > Détail fiche serveur
5. Gains > Historique
6. Payer gain > vérifier un code ticket
7. Sync tickets offline

## Note
Le bouton **Payer gain** reste disponible, mais il doit être testé séparément car il dépend fortement de la présence d'une session de caisse ouverte et du schéma `gains` installé.
