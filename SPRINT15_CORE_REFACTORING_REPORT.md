# Sprint 15 — Core Refactoring & Stabilization

## Objectif
Stabiliser la base du projet avant d'ajouter les modules opérations/caisse. Le but est de réduire la duplication, déplacer progressivement la logique SQL vers des classes dédiées, et préparer l'architecture MVC/Service/Repository.

## Modifications réalisées

### 1. Core Layer
Ajout de classes de base:

- `app/Core/Autoload.php`
- `app/Core/Repository.php`
- `app/Core/Service.php`
- `app/Core/Response.php`

Ces classes préparent le projet à une architecture plus propre sans casser les anciens fichiers `views/` et `actions/`.

### 2. Repository Layer
Ajout de repositories:

- `app/Repositories/DashboardRepository.php`
- `app/Repositories/FicheRepository.php`

Ils centralisent les accès SQL critiques utilisés par le dashboard et l'impression ticket.

### 3. Service Layer
Ajout de services:

- `app/Services/DashboardService.php`
- `app/Services/TicketService.php`

Le dashboard et l'impression ticket n'ont plus toute la logique métier directement dans le fichier PHP principal.

### 4. Middleware/Guard
Ajout de:

- `app/Middleware/Guard.php`

Ce fichier standardise l'appel aux protections `auth`, `tenant active`, `permission`, `super_admin`.

### 5. Helpers
Ajout de:

- `app/Helpers/path.php`

Pour préparer les chemins standards du projet (`base_path`, `app_path`, `view_path`).

### 6. Dashboard refactorisé
`views/dashboard.php` utilise maintenant `DashboardService`.

Avantages:

- moins de SQL directement dans la vue;
- tenant scope conservé;
- dashboard selon rôle conservé;
- plus facile à maintenir.

### 7. Ticket refactorisé
`actions/print_ticket.php` utilise maintenant `TicketService` + `FicheRepository`.

Avantages:

- correction durable du scope tenant;
- backfill tenant centralisé;
- audit centralisé;
- logique d'impression plus propre.

## Compatibilité
Les anciens chemins restent compatibles:

- `actions/print_tiket.php` redirige vers `print_ticket.php`;
- `actions/finance_store.php` redirige vers `actions/finances/store.php`;
- `includes/fotter.php` redirige vers `footer.php`.

## Vérification
Validation syntaxe PHP effectuée sur tout le projet:

```bash
php -l <tous les fichiers .php>
```

Résultat: aucune erreur de syntaxe détectée.

## Prochaine étape recommandée
Sprint 16 — Cash Sessions & Operations:

- ouverture session caisse;
- fermeture session caisse;
- ventes liées à session;
- paiements gains liés à session;
- rapport fermeture;
- validation superviseur.
