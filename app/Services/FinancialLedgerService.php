<?php

class FinancialLedgerService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function postEntry(int $tenantId, string $sourceType, ?int $sourceId, string $description, array $lines, ?int $userId = null): int
    {
        $debit = 0;
        $credit = 0;
        foreach ($lines as $line) {
            $debit += (float)($line['debit'] ?? 0);
            $credit += (float)($line['credit'] ?? 0);
        }

        if (round($debit, 2) !== round($credit, 2)) {
            throw new InvalidArgumentException('Journal entry is not balanced.');
        }

        $entryNo = 'JE-' . date('YmdHis') . '-' . random_int(1000, 9999);

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO journal_entries (tenant_id, entry_no, source_type, source_id, description, posted_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, $entryNo, $sourceType, $sourceId, $description, $userId]);
            $entryId = (int)$this->pdo->lastInsertId();

            $lineStmt = $this->pdo->prepare("INSERT INTO journal_lines (tenant_id, journal_entry_id, account_id, debit, credit, memo) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($lines as $line) {
                $lineStmt->execute([
                    $tenantId,
                    $entryId,
                    (int)$line['account_id'],
                    (float)($line['debit'] ?? 0),
                    (float)($line['credit'] ?? 0),
                    $line['memo'] ?? null,
                ]);
            }

            $this->pdo->commit();
            return $entryId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
