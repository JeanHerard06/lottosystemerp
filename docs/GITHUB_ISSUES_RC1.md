# Issues à créer pour v1.0 RC1

## 1. RC1: Architecture Cleanup
- Retirer les doublons dans actions
- Standardiser CRUD
- Déplacer logique SQL vers repositories/services

## 2. RC1: Tenant Isolation Audit
- Vérifier tous les modules
- Tester cross-tenant access
- Corriger query sans tenant scope

## 3. RC1: Database Audit
- Vérifier FK, indexes, tenant_id
- Corriger migrations incohérentes
- Ajouter indexes critiques

## 4. RC1: Security Audit
- CSRF
- Sessions
- Password policy
- API authentication
- File upload

## 5. RC1: QA Functional Tests
- Login
- Users
- Agents
- Lotteries
- Fiches
- Tirages
- Gains
- Cash sessions
- Reports
- API

## 6. RC1: Release Documentation
- INSTALL.md
- UPGRADE.md
- TEST_REPORT.md
- RELEASE_NOTES.md
