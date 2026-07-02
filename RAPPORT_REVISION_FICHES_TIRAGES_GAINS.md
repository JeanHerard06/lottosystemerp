# Rapport de révision — Fiches, Tirages, Gains

## Portée
Révision approfondie des modules Fiches, Tirages, Gains/Paiements, Tickets et API/PWA associés.

## Corrections appliquées

### Fiches
- Ajout de `tenant_id` lors de la création web/API/mobile.
- Validation stricte de la loterie selon le tenant.
- Génération de code fiche centralisée et unique.
- Filtrage des listes fiches par tenant, agent ou superviseur.
- Ajout filtre par loterie dans la liste des fiches.
- Conservation des validations: CSRF, permission, type de jeu, montant, numéro, risk management.

### Tirages
- Ajout de `tenant_id` lors de la création tirage.
- Filtrage des tirages par tenant.
- Sécurisation de l'accès au détail tirage par tenant.
- Validation de la loterie avant création du tirage.

### Gains
- Refonte du calcul pour éviter les recalculs destructifs après paiement.
- Recalcul refusé si un gain du tirage est déjà payé.
- Calcul strict par date + loterie: une fiche sans loterie n'est plus calculée sur tous les tirages.
- Mise à jour fiable du statut fiche: `lost`, `won`, `paid`, sans écraser `cancelled`.
- Paiement d'un gain: la fiche passe à `paid` uniquement lorsque tous ses gains gagnants sont payés.
- Protection tenant lors du paiement.

### API/PWA/Mobile
- Création fiche API/PWA alignée avec la logique web.
- Validation risk management appliquée côté API/mobile.
- Ledger/transactions via helper financier au lieu d'insertion manuelle fragile.
- Suppression du risque de double mise à jour balance agent.

## Tests effectués
- Validation syntaxique PHP complète sur tout le projet: OK.
- Vérification des dépendances principales helpers/actions/views: OK.
- Contrôle des requêtes critiques: OK.

## Points restants recommandés
- Ajouter des tests fonctionnels automatisés avec base de test.
- Ajouter une permission distincte `tirages.view` pour permettre aux agents de consulter les résultats sans gérer les tirages.
- Ajouter index complémentaires si le volume dépasse 100k fiches/jour.
