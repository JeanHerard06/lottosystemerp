# Sprint 9 — Quality & Release

## Objectif
Préparer une version v1.0 plus stable, installable et maintenable.

## Réalisé
- Correction `database.sql` : suppression du doublon `api_token` dans `users`.
- Ajout d'index SQL pour améliorer les rapports, recherches et calculs.
- Ajout migration `008_sprint9_quality_release.sql`.
- Ajout `install.php` pour installation guidée.
- Ajout `upgrade.php` pour exécuter les migrations.
- Ajout `config/config.sample.php`.
- Ajout `README.md` avec consignes d'installation et sécurité.
- Validation syntaxe PHP sur tout le projet.

## Tests effectués
- `php -l` exécuté sur tous les fichiers PHP: OK.
- Vérification du packaging ZIP: OK.

## Notes production
- Supprimer ou protéger `install.php` après installation.
- Changer `admin/admin123` immédiatement.
- Activer HTTPS.
- Configurer backups automatiques côté serveur.

## Prochaine étape proposée
Sprint 10 — SaaS/Multi-Tenant ou Sprint Hardening sécurité avancée.
