# v2.0 — Financial Ledger Engine

## Objectif
Centraliser tout mouvement financier dans un moteur de journal comptable double-entry.

## Principes
- Aucun module ne modifie directement une balance.
- Toute opération crée une `journal_entry` et plusieurs `journal_lines`.
- Total débit = total crédit.
- Chaque ligne porte `tenant_id`.
- Toutes les écritures sensibles doivent être auditées.

## Mouvements couverts
- Vente ticket
- Paiement gain
- Commission agent
- Dépôt
- Retrait
- Transfert caisse
- Paiement abonnement
- Dépense
- Revenu

## Comptes recommandés
- CASH_MAIN
- CASH_AGENCY
- CASH_AGENT
- SALES_REVENUE
- GAINS_PAYABLE
- COMMISSIONS_EXPENSE
- SUBSCRIPTION_REVENUE
- EXPENSES
- SUSPENSE

## Workflow vente ticket
1. Validation tenant/agent/cash session/lottery.
2. Création ticket.
3. Création journal entry `ticket_sale`.
4. Débit CASH_AGENT.
5. Crédit SALES_REVENUE.
6. Audit log.

## Workflow paiement gain
1. Vérifier gain non payé.
2. Vérifier cash session ouverte.
3. Création journal entry `gain_payment`.
4. Débit GAINS_PAYABLE.
5. Crédit CASH_AGENT.
6. Marquer gain payé.
7. Audit log.
