# Database Migration Refactor Report

## Objectif
Stabiliser l’installation sur une base MySQL/MariaDB propre malgré les migrations historiques issues des sprints, RC et v2.

## Corrections appliquées
- Correction de `023_performance_indexes.sql`: l’index invalide `gains(fiche_id)` devient `gains(fiche_detail_id)` car la table `gains` référence `fiche_details` via `fiche_detail_id`.
- Renforcement de `app/Helpers/migrations.php` pour vérifier les `CREATE INDEX` / `ALTER TABLE ADD INDEX` avant exécution.
- Si une table ou colonne ciblée par un index n’existe pas, l’index est ignoré avec un avertissement non bloquant au lieu d’un échec `SQLSTATE 1072`.
- Les erreurs de doublons table/column/index/FK restent traitées comme avertissements non bloquants.

## Résultat attendu
`install.php` doit pouvoir continuer même si une migration ancienne tente de créer un index déjà existant ou incompatible avec le schéma actuel. Les vrais problèmes bloquants restent visibles.

## Recommandation
À moyen terme, fusionner les migrations historiques en une migration baseline propre pour v1.0 stable, puis garder uniquement les migrations incrémentales futures.
