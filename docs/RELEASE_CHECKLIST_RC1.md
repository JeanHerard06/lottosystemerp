# v1.0 RC1 Release Checklist

## Database
- [ ] All migrations applied
- [ ] Foreign keys verified
- [ ] Indexes verified
- [ ] Tenant isolation tested

## Security
- [ ] Super admin role protected
- [ ] Tenant cannot access other tenant data
- [ ] CSRF enabled on write actions
- [ ] Password reset tested
- [ ] API tokens tested

## Business Workflows
- [ ] Tenant registration
- [ ] Tenant approval/rejection
- [ ] Agency CRUD
- [ ] Agent CRUD
- [ ] Lottery CRUD
- [ ] Lottery schedule
- [ ] Auto close lottery
- [ ] Fiche sale
- [ ] Ticket print/reprint
- [ ] Gain calculation/payment
- [ ] Cash session open/close/approval
- [ ] Notifications
- [ ] Audit logs

## UI/UX
- [ ] Desktop dashboard
- [ ] Mobile sidebar
- [ ] Responsive tables
- [ ] Form validation messages

## Deployment
- [ ] `.env.example` complete
- [ ] Install guide complete
- [ ] Upgrade guide complete
- [ ] Cron guide complete
- [ ] Backup/restore guide complete
