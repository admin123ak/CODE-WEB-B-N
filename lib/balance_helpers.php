<?php
/**
 * Helper thay đổi balance của user nguyên tử (atomic).
 *
 * Mọi credit/debit BẮT BUỘC đi qua đây — không UPDATE thẳng users.balance
 * vì sẽ race condition + không có audit log.
 *
 * Pattern:
 *   - BEGIN transaction
 *   - SELECT balance FOR UPDATE (row-lock)
 *   - Check đủ tiền nếu debit
 *   - UPDATE users + INSERT balance_logs
 *   - COMMIT
 *
 * Caller chịu trách nhiệm đảm bảo $db không đang trong transaction khác.
 * Nếu đã có transaction ngoài, các function này sẽ nest — MySQL không support
 * nested → behave như savepoint. An toàn nhất là gọi khi không có outer tx.
 */

/**
 * Cộng tiền vào balance.
 * @return array ['balance_before' => float, 'balance_after' => float, 'log_id' => int]
 * @throws RuntimeException nếu user không tồn tại
 */
function balanceCredit(
    PDO $db,
    int $user_id,
    float $amount,
    string $reason,           // 'topup' | 'refund' | 'admin_adjust'
    string $ref_type = '',    // 'topup_request' | 'order' | 'manual'
    ?int $ref_id = null,
    string $note = ''
): array {
    if ($amount <= 0) {
        throw new InvalidArgumentException('balanceCredit: amount phải > 0, nhận ' . $amount);
    }
    $allowedReasons = ['topup','refund','admin_adjust'];
    if (!in_array($reason, $allowedReasons, true)) {
        throw new InvalidArgumentException('balanceCredit: reason invalid: ' . $reason);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $db->rollBack();
            throw new RuntimeException('User không tồn tại: ' . $user_id);
        }
        $before = (float)$row['balance'];
        $after  = $before + $amount;

        $db->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$after, $user_id]);

        $db->prepare("INSERT INTO balance_logs
            (user_id, amount, balance_after, reason, ref_type, ref_id, note)
            VALUES (?, ?, ?, ?, ?, ?, ?)")
           ->execute([$user_id, $amount, $after, $reason, $ref_type ?: null, $ref_id, $note ?: null]);
        $logId = (int)$db->lastInsertId();

        $db->commit();
        return ['balance_before' => $before, 'balance_after' => $after, 'log_id' => $logId];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

/**
 * Trừ tiền khỏi balance. Refuse nếu balance không đủ.
 * @return array ['balance_before' => float, 'balance_after' => float, 'log_id' => int]
 * @throws RuntimeException nếu user không tồn tại hoặc số dư không đủ
 */
function balanceDebit(
    PDO $db,
    int $user_id,
    float $amount,
    string $reason,           // 'purchase' | 'admin_adjust'
    string $ref_type = '',
    ?int $ref_id = null,
    string $note = ''
): array {
    if ($amount <= 0) {
        throw new InvalidArgumentException('balanceDebit: amount phải > 0, nhận ' . $amount);
    }
    $allowedReasons = ['purchase','admin_adjust'];
    if (!in_array($reason, $allowedReasons, true)) {
        throw new InvalidArgumentException('balanceDebit: reason invalid: ' . $reason);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $db->rollBack();
            throw new RuntimeException('User không tồn tại: ' . $user_id);
        }
        $before = (float)$row['balance'];
        if ($before < $amount) {
            $db->rollBack();
            throw new RuntimeException(sprintf(
                'Số dư không đủ: cần %s, có %s',
                number_format($amount), number_format($before)
            ));
        }
        $after = $before - $amount;

        $db->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$after, $user_id]);

        $db->prepare("INSERT INTO balance_logs
            (user_id, amount, balance_after, reason, ref_type, ref_id, note)
            VALUES (?, ?, ?, ?, ?, ?, ?)")
           ->execute([$user_id, -$amount, $after, $reason, $ref_type ?: null, $ref_id, $note ?: null]);
        $logId = (int)$db->lastInsertId();

        $db->commit();
        return ['balance_before' => $before, 'balance_after' => $after, 'log_id' => $logId];
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

/**
 * Đọc balance hiện tại (không lock).
 */
function balanceGet(PDO $db, int $user_id): float {
    $stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (float)$row['balance'] : 0.0;
}

/**
 * Lịch sử balance gần đây của user.
 */
function balanceHistory(PDO $db, int $user_id, int $limit = 50): array {
    $stmt = $db->prepare("SELECT * FROM balance_logs
        WHERE user_id = ?
        ORDER BY id DESC
        LIMIT " . (int)$limit);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
