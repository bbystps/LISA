<?php
// /admin/transactions/status_retry.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  require __DIR__ . '/../../includes/db.php';
  if (!($pdo instanceof PDO)) throw new Exception('PDO not initialized');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Expecting an array of IDs: ids[]=1&ids[]=2...
  $ids = $_POST['ids'] ?? null;
  if (!$ids || !is_array($ids)) throw new Exception('Missing or invalid ids');

  // Keep only integers
  $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
  if (empty($ids)) throw new Exception('No valid ids');

  // Build placeholders for IN (...)
  $ph = implode(',', array_fill(0, count($ids), '?'));

  // Only revert ACTIVE rows currently in Delivering/Fetching
  $sql = "
    UPDATE transactions
       SET status = CASE
                      WHEN status = 'Delivering' THEN 'To Deliver'
                      WHEN status = 'Fetching'   THEN 'To Fetch'
                      ELSE status
                    END
     WHERE id IN ($ph)
       AND flag = 'ACTIVE'
       AND status IN ('Delivering', 'Fetching')
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($ids);
  $affected = $stmt->rowCount();

  echo json_encode([
    'success'  => true,
    'affected' => $affected,
    'ids'      => $ids,
    'note'     => 'Only ACTIVE rows in Delivering/Fetching are reverted',
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
