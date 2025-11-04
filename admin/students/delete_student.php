<?php
// delete_student.php
require __DIR__ . '../../../includes/db.php'; // must create $pdo (PDO)

header('Content-Type: application/json; charset=UTF-8');

try {
  // Read JSON body
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!$data || empty($data['student_id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'student_id required']);
    exit;
  }

  $student_id = $data['student_id'];

  // OPTIONAL: session/role check here
  // if (!isset($_SESSION['admin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }

  // Safety check: block delete if student still has borrowed items (Borrowed + ACTIVE/PENDING)
  // Adjust table/columns if yours differ.
  $sqlActive = "
    SELECT COUNT(*) AS cnt
    FROM transactions
    WHERE student_id = :sid
      AND status = 'Borrowed'
      AND (FLAG = 'ACTIVE' OR FLAG = 'PENDING')
  ";
  $st = $pdo->prepare($sqlActive);
  $st->execute([':sid' => $student_id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if ($row && (int)$row['cnt'] > 0) {
    http_response_code(409);
    echo json_encode([
      'ok' => false,
      'error' => 'Cannot delete: student still has borrowed books.'
    ]);
    exit;
  }

  // If you have foreign keys from transactions → students and you want to keep history,
  // this simple delete is fine (it will fail if FK is ON and not nullable without ON DELETE SET NULL / CASCADE).
  // If you enabled ON DELETE CASCADE and want to wipe related rows, it will cascade.
  $sqlDel = "DELETE FROM students WHERE student_id = :sid LIMIT 1";
  $st = $pdo->prepare($sqlDel);
  $st->execute([':sid' => $student_id]);

  if ($st->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Student not found']);
    exit;
  }

  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  // error_log($e->getMessage());
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
}
