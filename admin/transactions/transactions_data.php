<?php
// /admin/transactions/transactions_data.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  require __DIR__ . '/../../includes/db.php'; // must set $pdo (PDO)

  if (!($pdo instanceof PDO)) {
    throw new Exception('PDO not initialized');
  }
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sql = "
    SELECT
      t.id,
      t.student_id,
      t.book_id,
      COALESCE(s.name, t.student_id) AS Name,
      b.title AS Title,
      b.author AS Author,
      t.borrow_date AS `Borrow Date`,
      t.return_date AS `Return Date`,
      t.status AS Status,
      t.flag AS Flag,
      t.location AS Location
    FROM transactions t
    INNER JOIN books b ON b.book_id = t.book_id
    LEFT JOIN students s ON s.student_id = t.student_id
    WHERE t.flag = 'ACTIVE'
    ORDER BY t.id DESC
  ";

  $stmt = $pdo->query($sql);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    http_response_code(500);
    echo json_encode(['data' => [], 'error' => $e->getMessage()]);
  } else {
    http_response_code(500);
    echo json_encode(['data' => [], 'error' => 'Server error']);
  }
}
