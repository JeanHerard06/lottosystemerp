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

## [1.0.0-rc1.4] - 2026-07-18

### Added
- Enterprise `HealthService`, `SystemDiagnosticService`, `BackupService` and `VersionService`.
- System Diagnostics page and persisted JSON reports.
- Version and deployment information page.
- SHA-256 metadata and controlled downloads for SQL backups.

### Changed
- Backup creation now requires POST, authentication, permission and CSRF validation.
- System Health UI now reports operational, warning and critical checks through one service.

### Security
- Removed state-changing backup creation through GET.
- Added backup filename validation and path traversal protection.
