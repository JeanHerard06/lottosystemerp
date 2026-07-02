# Performance Guide — Lotto ERP Enterprise

## Index prioritaires
Ajouter des index sur toutes les colonnes utilisées dans les filtres fréquents.

## Tables critiques
- fiches
- fiche_details
- gains
- agent_transactions
- cash_sessions
- audit_logs
- notifications

## Règles
1. Toute requête tenant doit filtrer par `tenant_id`.
2. Toute liste doit être paginée.
3. Éviter `SELECT *` dans les rapports lourds.
4. Ajouter `created_at` dans les index de rapports.
5. Archiver les logs anciens.

## Exemples d’index
```sql
CREATE INDEX idx_fiches_tenant_date ON fiches(tenant_id, created_at);
CREATE INDEX idx_fiches_agent_date ON fiches(agent_id, created_at);
CREATE INDEX idx_gains_tenant_status ON gains(tenant_id, status);
CREATE INDEX idx_logs_tenant_date ON audit_logs(tenant_id, created_at);
```
