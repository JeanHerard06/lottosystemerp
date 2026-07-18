# Mobile Agent Dashboard v2 Patch

## Objectif
Restaurer **Commissions** dans le dashboard Agent tout en ajoutant **Notifications** sans conflit de merge.

## Corrections réalisées
- Ajout d'un écran `NotificationsScreen` dans l'application Flutter.
- Ajout des endpoints API mobile notifications :
  - `api/mobile/notifications/index.php`
  - `api/mobile/notifications/mark_read.php`
  - `api/mobile/notifications/mark_all_read.php`
  - `api/mobile/notifications/delete.php`
- Ajout du compteur `unread_notifications` dans `api/mobile/dashboard.php`.
- Refonte du dashboard Agent :
  - KPI cards : Fiches, Ventes, Commission, Balance.
  - Grille d'opérations : Nouvelle fiche, Mes fiches, Cash session, QR.
  - Grille Finance & Communication : Commissions, Notifications, Claims, Balance, Tirages.
  - Badge notifications dans l'AppBar et dans la carte Notifications.
  - Bottom navigation : Home, Tickets, Commission, Notifications, Profil.
- Commissions et Notifications coexistent maintenant dans le dashboard Agent.

## Fichiers modifiés / ajoutés
- `mobile_app/lib/screens/dashboard_screen.dart`
- `mobile_app/lib/screens/notifications_screen.dart`
- `api/mobile/dashboard.php`
- `api/mobile/notifications/index.php`
- `api/mobile/notifications/mark_read.php`
- `api/mobile/notifications/mark_all_read.php`
- `api/mobile/notifications/delete.php`

## QA recommandé
1. Login Agent.
2. Vérifier que le dashboard affiche la carte **Commission**.
3. Vérifier que le dashboard affiche la carte **Notifications**.
4. Créer une notification en base pour l'utilisateur agent.
5. Vérifier le badge unread.
6. Ouvrir Notifications.
7. Marquer une notification comme lue.
8. Marquer toutes comme lues.
9. Supprimer une notification.
10. Retourner au dashboard et vérifier que le badge se met à jour.

## Note technique
Le dashboard ancien `features/dashboard/dashboard_page.dart` n'est pas utilisé par `main.dart`. Le dashboard officiel de l'Agent reste `screens/dashboard_screen.dart`.
