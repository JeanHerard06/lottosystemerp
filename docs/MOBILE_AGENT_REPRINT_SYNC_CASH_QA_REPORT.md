# Mobile Agent v1 — Reprint, Sync & Cash Close QA Patch

## Objectif
Stabiliser le workflow mobile agent après création de fiche: consultation des fiches, détail, prévisualisation/reprint, sync queue et fermeture de caisse.

## Changements backend
- Ajout `api/mobile/fiches/show.php`.
- Le endpoint retourne la fiche de l'agent connecté avec détails, lottery et tirage.
- Scope agent + tenant appliqué.
- `api/mobile/fiches/list.php` enrichi avec `lottery_name`.

## Changements Flutter
- `FichesScreen` amélioré:
  - Liste tickets serveur.
  - Liste tickets locaux/offline.
  - Badge statut `pending_sync`, `synced`, `failed`.
  - Bouton `Sync now`.
  - Logs de synchronisation récents.
  - Navigation vers détail fiche.
- Ajout `FicheDetailScreen`:
  - Détail ticket serveur ou local.
  - Preview ticket.
  - Bouton réimpression.
- Ajout `MobileTicketView`:
  - Affichage ticket réutilisable pour preview/reprint.
- `LocalDatabase`:
  - Ajout `sync_logs` helpers.
- `SyncQueueService`:
  - Journalisation des succès/échecs de synchronisation.
- `CashSessionScreen`:
  - Résumé avant fermeture.
  - Cash attendu, cash réel, différence.
  - Confirmation avant fermeture.

## Tests manuels recommandés
1. Login agent.
2. Open cash session.
3. Créer fiche online.
4. Voir fiche dans `Mes fiches`.
5. Ouvrir détail fiche.
6. Prévisualiser et réimprimer.
7. Couper internet, créer fiche offline.
8. Vérifier statut `pending_sync`.
9. Remettre internet, cliquer `Sync now`.
10. Vérifier statut `synced` ou message `failed`.
11. Fermer cash session et vérifier résumé.

## Notes
Le service Bluetooth reste un service d'intégration: les commandes réelles dépendront du package final et du modèle d'imprimante utilisé.
