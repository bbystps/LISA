<?php
session_start();

header('Content-Type: application/json');
require_once("../db_conn.php");

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  $studentId = $_SESSION['user']['student_id'] ?? null;
  if (!$studentId) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
  }

  // Get ALL ACTIVE regardless of status (these all consume slots)
  $sql = "
    SELECT 
      t.id            AS tx_id,
      t.book_id,
      t.status,
      t.borrow_date,
      t.return_date,
      t.transaction_date,
      b.title,
      b.author,
      b.category
    FROM transactions t
    JOIN books b ON b.book_id = t.book_id
    WHERE t.student_id = :sid
      AND t.flag IN ('ACTIVE','PENDING')
    ORDER BY t.transaction_date DESC
  ";
  $st = $pdo->prepare($sql);
  $st->execute([':sid' => $studentId]);
  $rows = $st->fetchAll();

  $limit = 3;                          // max concurrent ACTIVE items allowed
  $activeCount = count($rows);         // ALL ACTIVE count (any status)

  echo json_encode([
    'status'      => 'success',
    'limit'       => $limit,
    'count'       => $activeCount,                     // shows how many slots are used
    'slots_left'  => max(0, $limit - $activeCount),    // remaining slots
    'items'       => array_map(function ($r) {
      return [
        'tx_id'            => (int)$r['tx_id'],
        'book_id'          => $r['book_id'],
        'title'            => $r['title'],
        'author'           => $r['author'],
        'category'         => $r['category'],
        'status'           => $r['status'],
        'borrow_date'      => $r['borrow_date'],
        'return_date'      => $r['return_date'],
        'transaction_date' => $r['transaction_date'],
      ];
    }, $rows),
  ]);
} catch (Throwable $e) {
  error_log("get_my_active error: " . $e->getMessage());
  echo json_encode(['status' => 'error', 'message' => 'Server error.']);
}
