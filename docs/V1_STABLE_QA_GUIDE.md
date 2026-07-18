# Lotto ERP Enterprise — v1.0 Stable QA Guide

Objectif: valider que `master` est prêt pour un usage client.

## 1. Installation
1. Créer une database vide.
2. Configurer `.env`.
3. Ouvrir `/install.php`.
4. Vérifier `/views/settings/checklist.php`.

## 2. Workflow Web complet
- Login admin.
- Créer tenant.
- Créer agence.
- Créer agent.
- Créer lottery + tirage.
- Ouvrir cash session.
- Créer fiche.
- Imprimer ticket.
- Entrer résultat.
- Calculer gains.
- Payer gain.
- Fermer cash session.

## 3. Workflow Mobile Agent
- Login mobile.
- Dashboard agent.
- Charger lotteries.
- Charger tirages.
- Créer fiche avec preview.
- Tester validation numéros.
- Tester offline queue.
- Tester sync.
- Vérifier ticket QR.
- Soumettre claim.

## 4. Sécurité tenant
- Tenant A ne doit jamais voir agents/fiches/lotteries/rapports de Tenant B.
- Agent ne doit jamais voir menus admin.
- Tenant admin ne doit jamais créer ou attribuer `super_admin`.

## 5. Critères de sortie v1.0 Stable
- 0 fatal error PHP.
- 0 SQLSTATE bloquant.
- Mobile Agent workflow PASS.
- Tenant isolation PASS.
- Ticket creation + print PASS.
- Cash session PASS.
