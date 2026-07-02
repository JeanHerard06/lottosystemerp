# Testing Strategy — Lotto ERP Enterprise v1.0 RC1

## Objectif
Mettre en place une base de tests progressive pour stabiliser le projet avant v1.0 Stable.

## Niveaux de tests

### 1. Syntax Check
- Vérifier tous les fichiers PHP avec `php -l`.
- Exclure `vendor/`, `storage/`, `.git/`, `.idea/`.

### 2. Smoke Tests
- Login admin.
- Dashboard charge sans erreur.
- Pages critiques accessibles.
- Permissions respectées.

### 3. Tenant Isolation Tests
- Tenant A ne voit pas les données Tenant B.
- Agent ne voit que ses propres fiches.
- Superviseur ne voit que son agence.
- Super admin voit tout.

### 4. Business Tests
- Création fiche.
- Blocage lottery fermée.
- Cash session requise.
- Calcul gains.
- Paiement gains.
- Audit log généré.

### 5. API Tests
- Login token.
- Dashboard mobile.
- Création fiche mobile.
- Rejet si tenant suspendu.
- Rejet si lottery fermée.

## Checklist RC1
- [ ] Syntax PHP OK
- [ ] Login OK
- [ ] Tenant isolation OK
- [ ] Fiches OK
- [ ] Tirages OK
- [ ] Gains OK
- [ ] Cash sessions OK
- [ ] Tickets OK
- [ ] Reports OK
- [ ] API OK
