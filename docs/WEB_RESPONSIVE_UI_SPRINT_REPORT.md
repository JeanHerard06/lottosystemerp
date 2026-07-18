# Web Responsive UI Sprint

## Base
Travail applique sur le ZIP stable fourni par le proprietaire du projet.

## Changements
- Sidebar mobile conservee comme drawer, avec fermeture automatique apres navigation.
- Topbar reduite sur petits ecrans afin d'eviter le debordement horizontal.
- En-tetes de pages avec bouton d'action empiles automatiquement sur mobile.
- Toutes les tables HTML sont enrichies automatiquement avec les titres de colonnes.
- En dessous de 768 px, chaque ligne de table devient une carte lisible.
- Les cellules Actions deviennent des boutons tactiles regroupes.
- Les lignes vides avec `colspan` conservent un affichage centre.
- Le dashboard utilise une grille KPI de deux colonnes sur telephone et une colonne sur les tres petits ecrans.
- Les tableaux Dashboard, Fiches, Gains, Cash Sessions, Agents, Agences, Lotteries, Utilisateurs et autres CRUD beneficient du meme moteur global.

## Fichiers principaux
- `public/assets/js/app.js`
- `public/assets/css/app.css`
- `views/dashboard.php`

## Verification navigateur
1. Ouvrir Chrome DevTools.
2. Activer Device Toolbar.
3. Tester 360 px, 390 px, 768 px et 1024 px.
4. Verifier absence de scroll horizontal.
5. Verifier les actions Modifier, Voir, Imprimer, Fermer/Rouvrir.
6. Verifier sidebar drawer et notification dans topbar.

## Notes
Une table peut conserver son format classique avec `data-no-responsive="1"` lorsqu'une vue matricielle ou tres dense exige un scroll horizontal.
