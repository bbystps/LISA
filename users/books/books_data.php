<?php
// /admin/books/books_data.php
require __DIR__ . '../../../includes/db.php'; // must create $pdo (PDO)

header('Content-Type: application/json; charset=UTF-8');

// Optional: admin guard
// if (!isset($_SESSION['admin'])) { http_response_code(403); echo json_encode(['data'=>[]]); exit; }

$sql = "
  SELECT
    title      AS Title,
    author     AS Author,
    book_id    AS ISBN,      -- using book_id as the ISBN column in the table
    category   AS Category,
    status     AS Status
  FROM books
  ORDER BY title ASC
";

try {
  $stmt = $pdo->query($sql);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['data' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['data' => [], 'error' => 'Server error']);
}
