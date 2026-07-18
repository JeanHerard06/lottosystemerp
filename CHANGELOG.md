# Changelog

## v1.0 RC1 — En préparation

### Added
- GitHub Actions PHP syntax check
- Architecture documentation
- QA checklist
- Roadmap RC1 → Stable

### Changed
- Passage officiel vers workflow GitHub
- Préparation release candidate

### Planned
- Full code audit
- Tenant isolation audit
- Security audit
- Database audit
- Performance review

## Configurable Game Engine
- Catalogue de jeux configurable par tenant.
- Règles de validation et moteurs de correspondance dynamiques.
- Multiplicateurs par tenant et par lottery.
- Mobile Agent charge les jeux depuis l'API.
- Vente Web, PWA et Mobile valident les jeux via le moteur central.

## RC1.2 — Enterprise UI & UX

- Ajout d’une bibliothèque de composants Web réutilisables.
- Uniformisation des titres, KPI, boutons, badges, filtres et états vides.
- Migration des pages Fiches, Gagnants, Agents, Agences, Sessions de caisse et Commissions.
- Amélioration des cartes mobiles et des actions tactiles.
- Renforcement du scope tenant dans la sélection des agents pour les commissions.

## RC1.3 - Enterprise Security Review
- Added web security headers and authenticated no-store caching.
- Enforced POST guards on sensitive actions.
- Added backward-compatible CSRF verification alias.
- Hardened legacy blocage/limite endpoints by delegating to canonical tenant-scoped actions.
- Added automated action security audit.
