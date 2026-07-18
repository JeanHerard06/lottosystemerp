# Migration Duplicate/Compatibility Fix

This patch updates `app/Helpers/migrations.php` so installations and upgrades are safer across MySQL/MariaDB versions.

## Fixed

- `ALTER TABLE ... ADD COLUMN IF NOT EXISTS ...` is normalized to `ADD COLUMN ...` because some MySQL versions do not support that syntax.
- `CREATE INDEX IF NOT EXISTS ...` is normalized to `CREATE INDEX ...` for compatibility.
- Duplicate table/column/index/foreign-key errors are treated as non-blocking warnings.
- `install.php` can be re-run on a partially-created database without failing on duplicate schema objects.
- Migration files are still recorded in `schema_migrations` after successful execution.

## Why

The project has migrations from many sprints. Some migrations defensively add the same column/index/table more than once. On a clean database or a partially-created database, MySQL can raise errors such as:

- `Duplicate column name`
- `Duplicate key name`
- `Table already exists`
- `Duplicate foreign key constraint name`

These are now warnings instead of fatal errors.

## Recommended test

1. Create a new empty database.
2. Run `install.php`.
3. If warnings appear, review them, but installation should continue.
4. Login with the admin account.
