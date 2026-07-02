# Fraud Detection & Risk Alerts v2.0

## Objectif
Ajouter un moteur de détection fraude et d’alertes risque pour protéger chaque tenant contre les ventes suspectes, paiements doublons, tickets modifiés et accès non autorisés.

## Principes
- Toutes les règles sont tenant-scoped.
- Chaque alerte a un niveau: low, medium, high, critical.
- Aucune alerte ne supprime automatiquement les données; elle bloque ou signale selon configuration.
- Toutes les actions sensibles créent un audit log.

## Cas détectés
- Ticket avec code de vérification invalide.
- Paiement de gain déjà payé.
- Vente après fermeture lottery.
- Agent qui dépasse l’exposition autorisée sur un numéro.
- Accès ticket depuis un autre tenant.
- Annulation répétée ou suspecte.
- Vente sans cash session ouverte.
- Tentative de réimpression excessive.

## Workflow
1. Event métier: TicketCreated, TicketVerified, GainPaid, TicketCancelled.
2. FraudEngine évalue les règles actives.
3. RiskScore calcule score global.
4. FraudAlert est créée si seuil dépassé.
5. Notification envoyée aux rôles autorisés.
6. Audit log créé.

## Tables principales
- fraud_rules
- fraud_alerts
- risk_scores
- fraud_alert_actions

## Permissions
- fraud.view
- fraud.manage
- fraud.resolve
- risk.view

## API v2 proposée
- GET /api/v2/fraud/alerts
- GET /api/v2/fraud/alerts/{id}
- POST /api/v2/fraud/alerts/{id}/resolve
- GET /api/v2/risk/scores

## Definition of Done
- Tenant isolation validée.
- Règles configurables par tenant.
- Alerts visibles seulement aux utilisateurs autorisés.
- Audit log à chaque création/résolution.
- Tests sur double paiement, ticket invalide, vente après fermeture.
