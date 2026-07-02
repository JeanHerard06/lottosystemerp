# Sprint 5 — Risk Management

## Objectif
Renforcer le contrôle des risques avant validation des fiches: limites, blocages, mariages, primes/taux et dashboard d'exposition.

## Livrables réalisés

- Dashboard Risk Management (`views/risk/dashboard.php`)
- Limites boules CRUD complet avec scope:
  - numéro
  - type de jeu
  - lotterie
  - agence
  - seuil d'alerte
- Blocages CRUD complet avec scope:
  - numéro ou `*`
  - type de jeu
  - lotterie
  - agence
  - période début/fin
  - motif
- Mariages CRUD complet
- Primes/Taux modifiables
- Helper `app/Helpers/risk.php`
- Validation risk intégrée dans `actions/fiche_store.php`
- Audit logs sur création/modification/suppression
- Migration SQL `005_sprint5_risk_management.sql`
- Sidebar mise à jour

## Contrôle appliqué avant vente

Lorsqu'une fiche est créée, le système vérifie maintenant:

1. Lotterie active
2. Numéro bloqué
3. Jeu bloqué
4. Lotterie bloquée
5. Agence bloquée
6. Période de blocage active
7. Limite du numéro selon scope
8. Montant disponible avant validation

## Permissions

Nouvelle permission:

- `risk.view`

Les modules de configuration restent sous:

- `controls.manage`

## Fichiers importants

- `app/Helpers/risk.php`
- `views/risk/dashboard.php`
- `views/limites/index.php`
- `views/limites/create.php`
- `views/limites/edit.php`
- `views/blocages/index.php`
- `views/blocages/create.php`
- `views/blocages/edit.php`
- `views/marriages/index.php`
- `views/marriages/create.php`
- `views/marriages/edit.php`
- `views/primes/index.php`
- `actions/limites/*`
- `actions/blocages/*`
- `actions/marriages/*`
- `actions/primes/update.php`

## Vérification technique

Tous les fichiers PHP ont été vérifiés avec `php -l`.

## Prochain sprint recommandé

Sprint 6 — Finances Enterprise:

- Grand livre agent
- Dépôts / retraits avancés
- Commissions automatiques
- Balance recalculable
- Caisse centrale
- Clôture journalière
