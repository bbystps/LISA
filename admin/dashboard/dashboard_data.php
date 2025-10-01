<?php
// /admin/dashboard_data.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  require __DIR__ . '/../../includes/db.php'; // adjust if your db.php lives elsewhere
  if (!($pdo instanceof PDO)) throw new Exception('PDO not initialized');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Optional POSTs for your required format (we'll accept id + action)
  $id     = $_POST['id']     ?? null;
  $action = $_POST['action'] ?? 'load';

  if ($action !== 'load') throw new Exception('Invalid action for dashboard');

  // ---- KPIs ----
  $kpis = [];

  // Total Books
  $kpis['total_books'] = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();

  // Active Students (simple count of students table)
  $kpis['active_students'] = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

  // Books Borrowed now = books currently marked as 'Borrowed'
  $kpis['borrowed_now'] = (int)$pdo->query("SELECT COUNT(*) FROM books WHERE status='Borrowed'")->fetchColumn();

  // Overdue now: transactions still ACTIVE, not done, due date < today and status implies the book hasn't fully returned
  // NOTE: return_date stored as varchar(32) like 'YYYY-MM-DD'
  $sqlOverdueNow = "
    SELECT COUNT(*) 
      FROM transactions t
     WHERE t.flag = 'ACTIVE'
       AND t.status IN ('Borrowed','Returning','To Fetch','Delivering','Fetching')
       AND STR_TO_DATE(t.return_date, '%Y-%m-%d') < CURDATE()
  ";
  $kpis['overdue_now'] = (int)$pdo->query($sqlOverdueNow)->fetchColumn();

  // ---- New Transactions (ACTIVE only) ----
  // Show latest 10 ACTIVE transactions with useful joins
  $sqlNewTx = "
    SELECT 
      t.id,
      t.student_id,
      s.name AS student_name,
      t.book_id,
      b.title AS book_title,
      t.status,
      t.borrow_date,
      t.return_date,
      t.transaction_date
    FROM transactions t
    JOIN students s ON s.student_id = t.student_id
    JOIN books b    ON b.book_id    = t.book_id
   WHERE t.flag = 'ACTIVE'
   ORDER BY t.transaction_date DESC
   LIMIT 10
  ";
  $newTx = $pdo->query($sqlNewTx)->fetchAll(PDO::FETCH_ASSOC);

  // ---- Overdue list (details) ----
  $sqlOverdueList = "
    SELECT 
      t.id,
      t.student_id,
      s.name AS student_name,
      t.book_id,
      b.title AS book_title,
      t.status,
      t.return_date
    FROM transactions t
    JOIN students s ON s.student_id = t.student_id
    JOIN books b    ON b.book_id    = t.book_id
   WHERE t.flag = 'ACTIVE'
     AND t.status IN ('Borrowed','Returning','To Fetch','Delivering','Fetching')
     AND STR_TO_DATE(t.return_date, '%Y-%m-%d') < CURDATE()
   ORDER BY STR_TO_DATE(t.return_date, '%Y-%m-%d') ASC, t.id DESC
   LIMIT 10
  ";
  $overdueList = $pdo->query($sqlOverdueList)->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'kpis' => $kpis,
    'new_transactions' => $newTx,
    'overdue' => $overdueList,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
