# Sprint 16 — Operations & Cash Sessions

## Objectif
Mettre en place un contrôle opérationnel de caisse afin qu'un agent ne puisse pas vendre ou payer un gain sans session de caisse ouverte.

## Livrables
- Module `cash_sessions`
  - Liste des sessions
  - Ouverture de session
  - Détail / rapport de session
  - Fermeture avec montant réel
  - Calcul automatique du cash attendu
  - Différence de caisse
  - Approbation / rejet par responsable
- Helper `app/Helpers/cash_sessions.php`
- Actions:
  - `actions/cash_sessions/open.php`
  - `actions/cash_sessions/close.php`
  - `actions/cash_sessions/approve.php`
- Views:
  - `views/cash_sessions/index.php`
  - `views/cash_sessions/open.php`
  - `views/cash_sessions/show.php`
- Migration `019_sprint16_cash_sessions.sql`
- Sidebar mise à jour

## Règles métier ajoutées
- Un agent doit avoir une session ouverte pour créer une fiche.
- L'API web/PWA/mobile ne peut plus créer une fiche sans session ouverte.
- Un paiement de gain exige une session de caisse ouverte pour l'agent concerné.
- Les transactions financières sont rattachées à la session ouverte quand elle existe.
- Les fiches sont rattachées à `cash_session_id`.

## Sécurité tenant/role
- Sessions filtrées par tenant.
- Agent voit seulement ses sessions.
- Superviseur voit les sessions de son agence.
- Tenant admin voit les sessions de son tenant.
- Super admin voit tout.

## Validation
- Syntaxe PHP validée sur tout le projet avec `php -l`.
