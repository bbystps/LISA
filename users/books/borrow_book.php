<?php
header('Content-Type: application/json');
include("../db_conn.php");

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);

  // Read POST
  // $student_id  = "11111111"; 
  $student_id     = $_POST['student_id'] ?? '';
  $book_id     = $_POST['borrow_isbn'] ?? '';
  $borrow_date = $_POST['borrow_date'] ?? '';
  $return_date = $_POST['return_date'] ?? '';
  $status      = "Reserved";  // reserving first
  $flag        = "ACTIVE";

  if (empty($student_id) || empty($book_id) || empty($borrow_date) || empty($return_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
  }

  $pdo->beginTransaction();

  // 1) Enforce limit: student may have at most 3 active (Borrowed/Reserved)
  // $limitStmt = $pdo->prepare("
  //   SELECT COUNT(*) 
  //   FROM transactions 
  //   WHERE student_id = :sid 
  //     AND status IN ('Borrowed','Reserved')
  // ");

  $limitStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM transactions
    WHERE student_id = :sid
      AND flag IN ('ACTIVE','PENDING')
      AND status IN ('Borrowed','Reserved')
  ");

  $limitStmt->execute([':sid' => $student_id]);
  $activeCount = (int)$limitStmt->fetchColumn();

  if ($activeCount >= 3) {
    $pdo->rollBack();
    echo json_encode([
      'status' => 'error',
      'message' => 'Limit reached: You already have 3 active books (Borrowed/Reserved).'
    ]);
    exit;
  }

  // 2) Optional (recommended): ensure the book is still Available before reserving
  //    If you don’t want this yet, comment this block out.
  $bookChk = $pdo->prepare("SELECT status FROM books WHERE book_id = :book_id LIMIT 1");
  $bookChk->execute([':book_id' => $book_id]);
  $bookRow = $bookChk->fetch(PDO::FETCH_ASSOC);
  if (!$bookRow) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Book not found.']);
    exit;
  }
  if ($bookRow['status'] !== 'Available') {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Book is not available for reservation.']);
    exit;
  }

  // 3) Insert into transactions
  $ins = $pdo->prepare("
    INSERT INTO transactions 
      (student_id, book_id, borrow_date, return_date, status, flag)
    VALUES 
      (:student_id, :book_id, :borrow_date, :return_date, :status, :flag)
  ");
  $ins->execute([
    ':student_id'  => $student_id,
    ':book_id'     => $book_id,
    ':borrow_date' => $borrow_date,
    ':return_date' => $return_date,
    ':status'      => $status,   // 'Reserved'
    ':flag'        => $flag,     // 'ACTIVE'
  ]);

  // 4) Mark book as Reserved
  $upd = $pdo->prepare("UPDATE books SET status = 'Reserved' WHERE book_id = :book_id");
  $upd->execute([':book_id' => $book_id]);

  $pdo->commit();

  echo json_encode([
    'status' => 'success',
    'message' => 'Reservation recorded and book marked as Reserved.'
  ]);
} catch (Exception $e) {
  if ($pdo && $pdo->inTransaction()) {
    $pdo->rollBack();
  }
  error_log("Error reserving book: " . $e->getMessage());
  echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again.']);
}
