<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

require_once("../db_conn.php");

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  $studentId = $_SESSION['user']['student_id'] ?? null;
  if (!$studentId) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
  }

  // Accept book_id or id (compat: id == book_id)
  $bookId = $_POST['book_id'] ?? $_POST['id'] ?? '';
  $action = strtolower($_POST['action'] ?? '');

  if (!$bookId || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
  }
  if (!in_array($action, ['cancel', 'return'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
  }

  $pdo->beginTransaction();

  if ($action === 'cancel') {
    // Close ACTIVE/PENDING transactions for this user+book, then free the book
    $updTx = $pdo->prepare("
      UPDATE transactions
      SET status = 'Cancelled',
          flag   = 'DONE'
      WHERE student_id = :sid
        AND book_id    = :bid
        AND flag IN ('ACTIVE','PENDING')
    ");
    $updTx->execute([':sid' => $studentId, ':bid' => $bookId]);

    if ($updTx->rowCount() === 0) {
      $pdo->rollBack();
      echo json_encode(['status' => 'error', 'message' => 'No active/pending record found for this book.']);
      exit;
    }

    $updBook = $pdo->prepare("UPDATE books SET status = 'Available' WHERE book_id = :bid");
    $updBook->execute([':bid' => $bookId]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Reservation cancelled.']);
    exit;
  }

  // ---- RETURN FLOW ----
  // Get latest ACTIVE/PENDING for context (dates); optional
  $sel = $pdo->prepare("
    SELECT id, borrow_date, return_date
    FROM transactions
    WHERE student_id = :sid
      AND book_id    = :bid
      AND flag IN ('ACTIVE','PENDING')
    ORDER BY transaction_date DESC
    LIMIT 1
  ");
  $sel->execute([':sid' => $studentId, ':bid' => $bookId]);
  $current = $sel->fetch();

  if (!$current) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'No active/pending record found for this book.']);
    exit;
  }

  // 1) Mark all ACTIVE/PENDING for this user+book as DONE (keep their status text)
  $close = $pdo->prepare("
    UPDATE transactions
    SET flag = 'DONE'
    WHERE student_id = :sid
      AND book_id    = :bid
      AND flag IN ('ACTIVE','PENDING')
  ");
  $close->execute([':sid' => $studentId, ':bid' => $bookId]);

  // 2) Insert new "Returning" row with flag ACTIVE (NO location column)
  $ins = $pdo->prepare("
    INSERT INTO transactions
      (student_id, book_id, borrow_date, return_date, status, flag)
    VALUES
      (:sid, :bid, :bdate, :rdate, 'Returning', 'ACTIVE')
  ");
  $ins->execute([
    ':sid'   => $studentId,
    ':bid'   => $bookId,
    ':bdate' => $current['borrow_date'] ?? '',
    ':rdate' => $current['return_date'] ?? '',
  ]);

  $pdo->commit();
  echo json_encode(['status' => 'success', 'message' => 'Return initiated (Returning).']);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  error_log("status_update error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
