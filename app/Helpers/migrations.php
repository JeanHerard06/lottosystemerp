<?php
/**
 * Migration helpers shared by install.php and upgrade.php.
 * Works without Composer and keeps installs safer than executing one huge SQL file.
 */

function lotto_sql_statements(string $sql): array
{
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    $statements = [];
    $buffer = '';
    $inSingle = false;
    $inDouble = false;
    $escape = false;
    $len = strlen($sql);

    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $buffer .= $ch;

        if ($escape) {
            $escape = false;
            continue;
        }
        if ($ch === '\\') {
            $escape = true;
            continue;
        }
        if ($ch === "'" && !$inDouble) {
            $inSingle = !$inSingle;
            continue;
        }
        if ($ch === '"' && !$inSingle) {
            $inDouble = !$inDouble;
            continue;
        }
        if ($ch === ';' && !$inSingle && !$inDouble) {
            $stmt = trim(substr($buffer, 0, -1));
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
            $buffer = '';
        }
    }

    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}


/**
 * Normalize migration SQL for mixed MySQL/MariaDB versions.
 * Some environments do not support IF NOT EXISTS on ALTER TABLE ADD COLUMN
 * or CREATE INDEX. We remove the unsupported clause and let the migration
 * runner skip duplicate column/index/table errors as non-blocking warnings.
 */
function lotto_normalize_mysql_compat_sql(string $sql): string
{
    // MySQL/MariaDB compatibility: ALTER TABLE x ADD COLUMN IF NOT EXISTS y ...
    $sql = preg_replace('/\bADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\b/i', 'ADD COLUMN', $sql);
    $sql = preg_replace('/\bADD\s+IF\s+NOT\s+EXISTS\b/i', 'ADD', $sql);

    // MySQL compatibility: CREATE INDEX IF NOT EXISTS idx ON table(...)
    $sql = preg_replace('/\bCREATE\s+(UNIQUE\s+)?INDEX\s+IF\s+NOT\s+EXISTS\b/i', 'CREATE $1INDEX', $sql);

    // MySQL compatibility: ALTER TABLE x ADD INDEX IF NOT EXISTS idx (...)
    $sql = preg_replace('/\bADD\s+(UNIQUE\s+)?INDEX\s+IF\s+NOT\s+EXISTS\b/i', 'ADD $1INDEX', $sql);
    $sql = preg_replace('/\bADD\s+KEY\s+IF\s+NOT\s+EXISTS\b/i', 'ADD KEY', $sql);

    // MySQL compatibility: DROP INDEX IF EXISTS idx ON table
    $sql = preg_replace('/\bDROP\s+INDEX\s+IF\s+EXISTS\b/i', 'DROP INDEX', $sql);

    return $sql;
}

function lotto_prepare_migration_sql(string $sql, string $dbName): string
{
    $safeDb = str_replace('`', '``', $dbName);
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS\s+`?\w+`?\s+CHARACTER SET\s+\w+\s+COLLATE\s+\w+;?/i', '', $sql);
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS\s+`?\w+`?.*?;?/i', '', $sql);
    $sql = preg_replace('/USE\s+`?\w+`?\s*;?/i', "USE `{$safeDb}`;", $sql);
    return lotto_normalize_mysql_compat_sql($sql);
}

function lotto_is_benign_migration_error(Throwable $e): bool
{
    $message = $e->getMessage();
    $benignCodes = ['1060', '1061', '1068', '1091', '1050', '1062', '1072', '1091', '1826'];
    foreach ($benignCodes as $code) {
        if (str_contains($message, '[' . $code . ']') || str_contains($message, ' ' . $code . ' ')) {
            return true;
        }
    }
    $patterns = [
        'Duplicate column name',
        'Duplicate key name',
        'Multiple primary key',
        'already exists',
        "Can't DROP",
        'Duplicate entry',
        'Duplicate foreign key constraint name',
        'already used',
        'already used by another key',
        'check that column/key exists',
    ];
    foreach ($patterns as $pattern) {
        if (stripos($message, $pattern) !== false) {
            return true;
        }
    }
    return false;
}


function lotto_parse_index_statement(string $statement): ?array
{
    $s = trim(preg_replace('/\s+/', ' ', $statement));

    // CREATE [UNIQUE] INDEX idx ON table(col1, col2)
    if (preg_match('/^CREATE\s+(?:UNIQUE\s+)?INDEX\s+`?([a-zA-Z0-9_]+)`?\s+ON\s+`?([a-zA-Z0-9_]+)`?\s*\((.+)\)$/i', $s, $m)) {
        return ['table' => $m[2], 'columns_raw' => $m[3]];
    }

    // ALTER TABLE table ADD [UNIQUE] INDEX idx(col1, col2)
    if (preg_match('/^ALTER\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s+ADD\s+(?:UNIQUE\s+)?(?:INDEX|KEY)\s+`?[a-zA-Z0-9_]+`?\s*\((.+)\)$/i', $s, $m)) {
        return ['table' => $m[1], 'columns_raw' => $m[2]];
    }

    return null;
}

function lotto_index_columns_from_raw(string $raw): array
{
    $cols = [];
    foreach (explode(',', $raw) as $part) {
        $part = trim($part);
        // Accept `column`, column, column(20), `column`(20)
        if (preg_match('/^`?([a-zA-Z0-9_]+)`?(?:\s*\(\s*\d+\s*\))?/i', $part, $m)) {
            $cols[] = $m[1];
        }
    }
    return $cols;
}

function lotto_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

function lotto_table_columns(PDO $pdo, string $table): array
{
    if (!lotto_table_exists($pdo, $table)) {
        return [];
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM `" . str_replace('`', '``', $table) . "`");
    return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
}

function lotto_validate_index_statement(PDO $pdo, string $statement): ?string
{
    $parsed = lotto_parse_index_statement($statement);
    if (!$parsed) {
        return null;
    }

    $table = $parsed['table'];
    if (!lotto_table_exists($pdo, $table)) {
        return "Skipped index: table '{$table}' does not exist";
    }

    $existing = array_map('strtolower', lotto_table_columns($pdo, $table));
    $missing = [];
    foreach (lotto_index_columns_from_raw($parsed['columns_raw']) as $column) {
        if (!in_array(strtolower($column), $existing, true)) {
            $missing[] = $column;
        }
    }

    if ($missing) {
        return "Skipped index on {$table}: missing column(s) " . implode(', ', $missing);
    }

    return null;
}

function lotto_run_sql_file(PDO $pdo, string $file, string $dbName, bool $ignoreBenign = true): array
{
    $warnings = [];
    $sql = lotto_prepare_migration_sql(file_get_contents($file), $dbName);
    foreach (lotto_sql_statements($sql) as $statement) {
        $trimmed = trim($statement);
        if ($trimmed === '') { continue; }
        $skipReason = lotto_validate_index_statement($pdo, $trimmed);
        if ($skipReason !== null) {
            $warnings[] = basename($file) . ': ' . $skipReason;
            continue;
        }

        try {
            $pdo->exec($trimmed);
        } catch (Throwable $e) {
            if ($ignoreBenign && lotto_is_benign_migration_error($e)) {
                $warnings[] = basename($file) . ': ' . $e->getMessage();
                continue;
            }
            throw $e;
        }
    }
    return $warnings;
}

function lotto_run_migrations(PDO $pdo, string $migrationDir, string $dbName, bool $freshInstall = false): array
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (id INT AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) UNIQUE, executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
    $done = $freshInstall ? [] : $pdo->query("SELECT migration FROM schema_migrations")->fetchAll(PDO::FETCH_COLUMN);
    $files = glob(rtrim($migrationDir, '/') . '/*.sql') ?: [];
    sort($files, SORT_NATURAL);

    $ran = [];
    $warnings = [];

    foreach ($files as $file) {
        $name = basename($file);
        if (in_array($name, $done, true)) { continue; }
        try {
            // MySQL DDL statements (CREATE TABLE, ALTER TABLE, DROP TABLE, etc.)
            // cause implicit commits. Wrapping a migration file that contains DDL
            // in a transaction can therefore produce: "There is no active transaction"
            // when commit() is called after the implicit commit. Execute SQL files
            // statement-by-statement and record the migration after successful execution.
            // Migrations were produced over many sprints and may contain defensive
            // duplicate ADD COLUMN / CREATE INDEX statements. Always treat
            // duplicate table/column/index/FK errors as warnings so install.php
            // can be re-run safely on a partially-created database.
            $warnings = array_merge($warnings, lotto_run_sql_file($pdo, $file, $dbName, true));
            $stmt = $pdo->prepare('INSERT IGNORE INTO schema_migrations(migration) VALUES (?)');
            $stmt->execute([$name]);
            $ran[] = $name;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            throw new RuntimeException($name . ': ' . $e->getMessage(), 0, $e);
        }
    }

    return ['ran' => $ran, 'warnings' => $warnings];
}

function lotto_write_env(array $values, string $path): void
{
    $existing = [];
    if (is_readable($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            if (trim($line) === '' || str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $existing[trim($k)] = trim($v);
        }
    }
    $merged = array_merge($existing, $values);
    $out = [];
    foreach ($merged as $key => $value) {
        $value = (string)$value;
        if (preg_match('/\s/', $value) || $value === '') {
            $value = '"' . str_replace('"', '\\"', $value) . '"';
        }
        $out[] = $key . '=' . $value;
    }
    file_put_contents($path, implode(PHP_EOL, $out) . PHP_EOL);
}
