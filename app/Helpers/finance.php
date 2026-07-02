<?php
function finance_reference(string $prefix = 'TRX'): string
{
    return $prefix . '-' . date('YmdHis') . '-' . random_int(100, 999);
}

function agent_balance(PDO $pdo, int $agentId): float
{
    $stmt = $pdo->prepare("\n        SELECT COALESCE(SUM(\n            CASE\n                WHEN type IN ('depot','commission','vente') THEN amount\n                WHEN type IN ('retrait','gain') THEN -amount\n                ELSE 0\n            END\n        ), 0)\n        FROM agent_transactions\n        WHERE agent_id = ? AND status = 'posted'\n    ");
    $stmt->execute([$agentId]);
    return (float)$stmt->fetchColumn();
}

function sync_agent_balance(PDO $pdo, int $agentId): float
{
    $balance = agent_balance($pdo, $agentId);
    $stmt = $pdo->prepare('UPDATE agents SET balance = ? WHERE id = ?');
    $stmt->execute([$balance, $agentId]);
    return $balance;
}

function post_agent_transaction(PDO $pdo, int $agentId, string $type, float $amount, string $description = '', ?int $createdBy = null, ?string $referenceNo = null, ?int $cashSessionId = null): int
{
    if (!in_array($type, ['vente','commission','depot','retrait','gain'], true)) {
        throw new InvalidArgumentException('Type transaction invalide.');
    }
    if ($amount <= 0) {
        throw new InvalidArgumentException('Le montant doit être supérieur à zéro.');
    }
    $referenceNo = $referenceNo ?: finance_reference(strtoupper(substr($type, 0, 3)));
    $stmt = $pdo->prepare("\n        INSERT INTO agent_transactions(agent_id, cash_session_id, type, amount, description, reference_no, status, created_by)\n        VALUES (?, ?, ?, ?, ?, ?, 'posted', ?)\n    ");
    $stmt->execute([$agentId, $cashSessionId, $type, $amount, $description, $referenceNo, $createdBy]);
    sync_agent_balance($pdo, $agentId);
    return (int)$pdo->lastInsertId();
}
