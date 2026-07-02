# Coding Standards

- PHP 8+ with PDO prepared statements.
- No SQL directly in views.
- Business rules belong in Services.
- Database access belongs in Repositories.
- Every store/update/delete must verify permission and tenant scope.
- Every destructive action must use CSRF.
- Use transactions for multi-table writes.
- Never let a tenant assign `super_admin`.
