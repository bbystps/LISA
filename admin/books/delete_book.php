<?php
// delete_book.php
// Place beside books_data.php (adjust path to db.php as needed)
require __DIR__ . '../../../includes/db.php'; // must create $pdo (PDO)

header('Content-Type: application/json; charset=UTF-8');

try {
  // 1) Read JSON body (because JS sends application/json)
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!$data || empty($data['book_id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'book_id required']);
    exit;
  }
  $book_id = $data['book_id'];

  // 2) (Optional) admin guard here
  // session_start();
  // if (!isset($_SESSION['admin'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }

  // 3) Ensure the book exists, and read its status
  $st = $pdo->prepare("SELECT status FROM books WHERE book_id = :id");
  $st->execute([':id' => $book_id]);
  $book = $st->fetch(PDO::FETCH_ASSOC);
  if (!$book) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Book not found']);
    exit;
  }

  // 4) Deny delete if the book’s own status is not Available
  if ($book['status'] !== 'Available') {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'Cannot delete: book is not Available.']);
    exit;
  }

  // 5) Safety check vs transactions
  // Adjust to your transactions schema. This matches your students rule:
  //   status = 'Borrowed' AND (FLAG = 'ACTIVE' OR FLAG = 'PENDING')
  $sqlActive = "
    SELECT COUNT(*) AS cnt
    FROM transactions
    WHERE book_id = :id
      AND status = 'Borrowed'
      AND (FLAG = 'ACTIVE' OR FLAG = 'PENDING')
  ";
  $st = $pdo->prepare($sqlActive);
  $st->execute([':id' => $book_id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if ($row && (int)$row['cnt'] > 0) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'Cannot delete: book is currently borrowed.']);
    exit;
  }

  // 6) Delete (will fail if you have a FK without CASCADE and any rows point to this book).
  $del = $pdo->prepare("DELETE FROM books WHERE book_id = :id LIMIT 1");
  $del->execute([':id' => $book_id]);

  if ($del->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Book not found or already deleted']);
    exit;
  }

  echo json_encode(['ok' => true]);
} catch (PDOException $e) {
  // FK constraint? (SQLSTATE 23000)
  if ($e->getCode() === '23000') {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'Cannot delete: book has related records.']);
  } else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database error']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Server error']);
}
