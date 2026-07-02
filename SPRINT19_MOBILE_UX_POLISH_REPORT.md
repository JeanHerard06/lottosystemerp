# Sprint 19 — Mobile UX Polish

## Objectif
Améliorer l'expérience mobile après la correction du sidebar.

## Réalisé
- Ajout de `public/assets/css/app.css` pour les styles globaux.
- Ajout de `public/assets/js/app.js` pour les comportements globaux.
- Tables automatiquement enveloppées dans un conteneur responsive horizontal.
- Lien actif dans le sidebar détecté automatiquement.
- Support global `data-confirm` pour les actions sensibles.
- Support global `data-auto-dismiss` pour alertes/notifications.
- Classes utilitaires standardisées: `btn`, `badge`, `form-control`, `table-responsive`.
- Ajustements mobile pour cartes, formulaires, tables et impressions.

## Fichiers modifiés
- `includes/header.php`
- `includes/footer.php`

## Fichiers ajoutés
- `public/assets/css/app.css`
- `public/assets/js/app.js`
- `SPRINT19_MOBILE_UX_POLISH_REPORT.md`

## Notes
Cette amélioration ne change pas les permissions ni le tenant scope. Elle rend l'interface plus stable sur téléphone/tablette sans réécrire toutes les vues.
