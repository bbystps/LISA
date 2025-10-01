<?php
// /admin/penalties/data.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  require __DIR__ . '/../../includes/db.php';
  if (!($pdo instanceof PDO)) throw new Exception('PDO not initialized');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $id     = $_POST['id']     ?? 0;
  $action = $_POST['action'] ?? 'load';
  if ($action !== 'load') throw new Exception('Invalid action');

  // settings
  $st = $pdo->query("SELECT daily_rate, grace_days FROM penalty_settings WHERE id=1");
  $settings = $st->fetch(PDO::FETCH_ASSOC) ?: ['daily_rate' => 5.00, 'grace_days' => 1];

  // KPIs
  $total       = (float)$pdo->query("SELECT IFNULL(SUM(amount),0) FROM penalties")->fetchColumn();
  $paid        = (float)$pdo->query("SELECT IFNULL(SUM(amount),0) FROM penalties WHERE status='Paid'")->fetchColumn();
  $outstanding = (float)$pdo->query("SELECT IFNULL(SUM(amount),0) FROM penalties WHERE status='Unpaid'")->fetchColumn();

  // rows
  $sql = "
    SELECT p.id, p.student_id, s.name AS student_name,
           p.book_id, b.title AS book_title,
           p.due_date, p.days_late, p.amount, p.status
    FROM penalties p
    JOIN students s ON s.student_id = p.student_id
    JOIN books b    ON b.book_id    = p.book_id
    ORDER BY p.status='Unpaid' DESC, p.due_date ASC, p.id DESC
  ";
  $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success'  => true,
    'settings' => $settings,
    'kpis'     => [
      'total'       => number_format($total, 2, '.', ''),
      'paid'        => number_format($paid, 2, '.', ''),
      'outstanding' => number_format($outstanding, 2, '.', ''),
    ],
    'rows'     => $rows
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
