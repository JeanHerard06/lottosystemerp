# RC1.4 — Production Hardening

## Objectif

Renforcer l’exploitation de Lotto ERP avant la mise en production, sans modifier les règles financières ni le moteur de jeu.

## Livrables

- `HealthService` : contrôles base de données, stockage, PHP, extensions, fuseau horaire, espace disque et planificateur.
- `SystemDiagnosticService` : diagnostic consolidé, tests de tables critiques et inventaire des migrations.
- `BackupService` : sauvegarde SQL en flux, téléchargement sécurisé, métadonnées et empreinte SHA-256.
- `VersionService` : version applicative, environnement, PHP et inventaire des migrations.
- Centre de santé système modernisé.
- Centre de diagnostics avec exécution POST + CSRF et rapports JSON dans `storage/diagnostics`.
- Centre de sauvegarde avec création POST + CSRF et téléchargement contrôlé.
- Page Version & déploiement.
- Fichier `VERSION` défini à `1.0.0-rc1.4`.

## Sécurité

- Les sauvegardes ne peuvent plus être lancées par une requête GET.
- Vérification CSRF sur les opérations de création et de diagnostic.
- Validation stricte des noms de fichiers téléchargés.
- Aucune restauration automatique en production dans cette release.
- Les erreurs techniques sont journalisées côté serveur et un message neutre est présenté à l’utilisateur.

## Limites volontaires

- La restauration et le rollback restent hors de RC1.4 jusqu’à l’ajout d’un workflow avec confirmation forte, sauvegarde préalable et environnement de test.
- Les métriques CPU/RAM détaillées nécessitent un agent serveur ou une intégration d’observabilité dédiée.
