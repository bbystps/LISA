<?php
// /admin/students/students_data.php
require __DIR__ . '../../../includes/db.php'; // must create $pdo (PDO)

header('Content-Type: application/json; charset=UTF-8');

// Optional: admin/session guard here
// if (!isset($_SESSION['admin'])) { http_response_code(403); echo json_encode(['data'=>[]]); exit; }

/*
 We assume transactions.status uses 'Borrowed' while a copy is out.
 If you use another indicator (e.g. flag, or return_date logic), tweak the CASE below.
*/
$sql = "
  SELECT
    s.rfid_key        AS rfid,
    s.student_id      AS student_id,
    s.name            AS name,
    s.email           AS email,
    s.course          AS course,
    COALESCE(SUM(CASE WHEN t.status = 'Borrowed' AND (t.FLAG = 'ACTIVE' OR t.FLAG = 'PENDING') THEN 1 ELSE 0 END), 0) AS borrowed
  FROM students s
  LEFT JOIN transactions t
         ON t.student_id = s.student_id
  GROUP BY s.student_id, s.rfid_key, s.name, s.email, s.course
  ORDER BY s.name ASC
";

try {
  $stmt = $pdo->query($sql);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['data' => [], 'error' => 'Server error']);
}
