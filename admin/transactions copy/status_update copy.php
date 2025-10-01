<?php
// /admin/transactions/status_update.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  require __DIR__ . '/../../includes/db.php';

  if (!($pdo instanceof PDO)) {
    throw new Exception('PDO not initialized');
  }
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Collect POST data
  $id = $_POST['id'] ?? null;

  if (!$id) {
    throw new Exception('Missing transaction ID');
  }

  // Update status
  $sql = "UPDATE transactions SET status = 'Delivering' WHERE id = :id AND flag = 'ACTIVE'";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':id' => $id]);

  echo json_encode(['success' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
