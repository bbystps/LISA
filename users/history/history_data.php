<?php
// /admin/history/history_script.php
session_start();

header('Content-Type: application/json; charset=UTF-8');

// Guard: must be logged in and have student_id in session
if (empty($_SESSION['user']) || empty($_SESSION['user']['student_id'])) {
  http_response_code(403);
  echo json_encode(['data' => [], 'error' => 'Forbidden']);
  exit;
}

$studentId = $_SESSION['user']['student_id'];

require __DIR__ . '/../../includes/db.php'; // must set $pdo (PDO instance)

$sql = "
  SELECT
    b.title                         AS Title,
    b.category                      AS Category,
    t.borrow_date                   AS `Borrow Date`,
    t.return_date                   AS `Return Date`,
    t.status                        AS `Return Status`,
    DATE_FORMAT(t.transaction_date, '%Y-%m-%d %H:%i:%s') AS `Transact Date`
  FROM transactions t
  INNER JOIN books b ON b.book_id = t.book_id
  WHERE t.student_id = :student_id
  ORDER BY t.transaction_date DESC
";

try {
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':student_id' => $studentId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['data' => [], 'error' => 'Server error']);
}
