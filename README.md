# MCS Lotto Enterprise

Système de gestion bòlèt/lotto développé en PHP 8, MySQL, Tailwind CSS.

## Installation rapide

1. Copier le dossier dans votre serveur local (`htdocs`, `www`, etc.).
2. Ouvrir `install.php` dans le navigateur.
3. Entrer les accès MySQL.
4. Se connecter avec `admin / admin123`.
5. Supprimer ou protéger `install.php` après installation.

## Mise à jour

Ouvrir `upgrade.php` pour appliquer les migrations SQL non exécutées.

## Modules inclus v1.0

- Login sécurisé
- Roles & Permissions
- Agences, superviseurs, agents
- Fiches / tickets 80mm
- Tirages / calcul gagnants / paiement gains
- Limites, blocages, mariages, primes
- Finances: dépôts, retraits, ledger, commissions
- API REST + PWA agent
- Sauvegardes SQL
- Audit logs

## Sécurité recommandée en production

- Changer le mot de passe admin après installation.
- Mettre HTTPS.
- Protéger `install.php` et `upgrade.php`.
- Faire des backups réguliers.
- Limiter l'accès au dossier `database/` via serveur web.

## Sprint 12 — Mobile Agent
Une application Flutter de base est disponible dans `/mobile_app` avec API mobile dans `/api/mobile`.
Configurer `mobile_app/lib/config.dart` avant lancement.
