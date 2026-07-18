# Web Responsive UI — Phase 2

## Objectif
Renforcer la première passe responsive sans modifier la logique métier ni les permissions.

## Améliorations
- Formulaires multi-colonnes convertis en une colonne sur mobile.
- Champs tactiles avec hauteur minimale et taille de police évitant le zoom iOS.
- Barres de filtres GET/recherche repliables sur mobile.
- Actions des tableaux plus lisibles et pleine largeur sur écrans étroits.
- Cartes de tableaux améliorées pour 320–420 px.
- Modales adaptées en panneaux bas sur mobile.
- Pagination et onglets défilables horizontalement.
- Support des safe areas iOS.
- Focus visible, lien d’évitement et libellés ARIA.
- Menu mobile avec état `aria-expanded` et focus initial.
- Respect de `prefers-reduced-motion`.
- Corrections globales contre les débordements horizontaux.

## Fichiers principaux
- `public/assets/css/app.css`
- `public/assets/js/app.js`
- `includes/header.php`
- `includes/topbar.php`
- `includes/sidebar.php`

## Test recommandé
Tester au minimum les largeurs 320, 360, 390, 430, 768 et 1024 px sur :
- Dashboard
- Fiches
- Gains
- Commissions
- Cash sessions
- Agents / Agences
- Lotteries / Tirages
- Rapports
- Paramètres
