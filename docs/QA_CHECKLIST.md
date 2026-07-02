# QA Checklist — v1.0 RC1

## Sécurité
- [ ] Tenant ne voit pas les données d’un autre tenant
- [ ] Tenant ne peut pas attribuer `super_admin`
- [ ] Agent ne voit que ses propres fiches
- [ ] Superviseur limité à son agence
- [ ] CSRF présent sur actions sensibles
- [ ] Upload logo sécurisé
- [ ] Password reset fonctionne
- [ ] API token vérifié

## Fonctionnel
- [ ] Login / logout
- [ ] Users CRUD
- [ ] Roles / permissions
- [ ] Tenant register / approve / reject
- [ ] Agencies CRUD
- [ ] Agents CRUD
- [ ] Lotteries CRUD
- [ ] Lottery schedules
- [ ] Auto close lottery
- [ ] Fiche store
- [ ] Ticket print
- [ ] Tirages
- [ ] Gains calculation
- [ ] Gains payment
- [ ] Cash sessions open / close
- [ ] Notifications
- [ ] Reports

## Database
- [ ] Migrations applicables sans erreur
- [ ] Foreign keys critiques présentes
- [ ] Index tenant_id présents
- [ ] Pas de colonne inconnue
- [ ] Pas de HY093

## UI/UX
- [ ] Sidebar mobile
- [ ] Tables responsive
- [ ] Dashboard par rôle
- [ ] Messages d’erreur clairs
- [ ] Confirmations delete/cancel

## Release
- [ ] README à jour
- [ ] INSTALL.md à jour
- [ ] UPGRADE.md à jour
- [ ] CHANGELOG.md à jour
