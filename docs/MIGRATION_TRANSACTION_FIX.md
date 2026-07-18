# Migration Transaction Fix

## Problem

During install/upgrade the first migration could fail with:

```text
✗ 001_sprint0_schema.sql: There is no active transaction
```

## Cause

MySQL implicitly commits many DDL statements such as `CREATE TABLE`, `ALTER TABLE`, and `DROP TABLE`. The migration runner was wrapping each SQL migration file in `beginTransaction()` / `commit()`. After MySQL implicitly committed the DDL, PHP attempted to `commit()` a transaction that was no longer active.

## Fix

`app/Helpers/migrations.php` now executes migration files statement-by-statement without wrapping DDL-heavy files inside an explicit transaction. The migration is recorded in `schema_migrations` only after the file finishes successfully.

## How to test

1. Create a fresh database, for example `lotto_system_test`.
2. Open `install.php` in the browser.
3. Run the install.
4. Confirm `001_sprint0_schema.sql` runs without the transaction error.
