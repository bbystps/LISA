<?php
// /admin/penalties/update.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  require __DIR__ . '/../../includes/db.php';
  if (!($pdo instanceof PDO)) throw new Exception('PDO not initialized');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $id     = $_POST['id']     ?? null;   // keep your preferred POST shape
  $action = $_POST['action'] ?? null;
  if (!$action) throw new Exception('Missing action');

  switch ($action) {
    case 'save_settings': {
        $rate  = isset($_POST['rate'])  ? (float)$_POST['rate']  : null;
        $grace = isset($_POST['grace']) ? (int)$_POST['grace']   : null;
        if ($rate === null || $grace === null) throw new Exception('Missing rate or grace');

        // ensure row id=1 exists
        $pdo->exec("INSERT IGNORE INTO penalty_settings (id, daily_rate, grace_days) VALUES (1, 5.00, 1)");

        $stmt = $pdo->prepare("UPDATE penalty_settings SET daily_rate=:r, grace_days=:g WHERE id=1");
        $stmt->execute([':r' => $rate, ':g' => $grace]);

        echo json_encode(['success' => true, 'affected' => $stmt->rowCount(), 'saved' => ['rate' => $rate, 'grace' => $grace]]);
        break;
      }

    case 'mark_paid': {
        if (!$id) throw new Exception('Missing id');
        $stmt = $pdo->prepare("UPDATE penalties SET status='Paid', paid_at=NOW() WHERE id=:id AND status='Unpaid'");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'affected' => $stmt->rowCount()]);
        break;
      }

    case 'delete': {
        if (!$id) throw new Exception('Missing id');
        $stmt = $pdo->prepare("DELETE FROM penalties WHERE id=:id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'affected' => $stmt->rowCount()]);
        break;
      }

    default:
      throw new Exception('Invalid action');
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
