# Sprint — Agences Tenant Scope

## Corrections
- Les agences sont filtrées par `tenant_id` partout pour les utilisateurs tenant.
- `super_admin` peut voir toutes les agences, avec le tenant affiché.
- Création agence: `tenant_id` obligatoire pour super_admin; automatique pour tenant.
- Modification agence: impossible de modifier une agence hors tenant.
- Agents et superviseurs ne peuvent être rattachés qu'à une agence du même tenant.
- Les dropdowns d'agences utilisent `visible_agencies()`.
- Les limites/blocages/mariages utilisent un filtrage tenant pour éviter les mélanges de données.

## Helpers ajoutés
- `tenant_required_insert_id()`
- `visible_agencies()`
- `ensure_agency_scope()`

## Règle métier
Un utilisateur tenant ne doit jamais voir, sélectionner ou modifier une agence appartenant à un autre tenant.
