# Developer Handbook

## Add a new module
1. Create migration.
2. Create Repository.
3. Create Service.
4. Create views.
5. Create actions/controllers.
6. Add permissions.
7. Add sidebar links.
8. Add tenant scope.
9. Add audit logs.
10. Update docs and changelog.

## Critical rule
Never trust `tenant_id` from user input. Resolve it from the authenticated user unless the user is `super_admin`.
