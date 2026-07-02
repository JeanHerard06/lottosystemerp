# Fraud Detection Engine

Objectif: détecter les activités suspectes avant qu'elles causent une perte financière.

## Alertes initiales
- Vente après fermeture lottery
- Tentative accès cross-tenant
- Paiement gain doublon
- Réimpression ticket excessive
- Suppression/annulation inhabituelle
- Login échoué répété
- Agent avec différence caisse élevée

## Table recommandée
`fraud_alerts`

## Statuts
- `open`
- `reviewing`
- `resolved`
- `dismissed`

## Priorités
- low
- medium
- high
- critical
