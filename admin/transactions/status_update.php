<?php
// /admin/transactions/status_update.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  require __DIR__ . '/../../includes/db.php';
  if (!($pdo instanceof PDO)) throw new Exception('PDO not initialized');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $id     = $_POST['id']     ?? null;
  $action = $_POST['action'] ?? null;

  if (!$id || !$action) throw new Exception('Missing id or action');

  switch ($action) {
    case 'deliver':
      // Set to Delivering; keep only ACTIVE rows eligible
      $sql = "UPDATE transactions
                 SET status = 'Delivering'
               WHERE id = :id AND flag = 'ACTIVE'";
      break;

    case 'ack':
      // Borrower acknowledged pickup:
      // 1) mark transaction as Borrowed (still PENDING), timestamp NOW
      // 2) mark the related book as Borrowed
      $pdo->beginTransaction();
      try {
        $sql1 = "UPDATE transactions
                    SET status = 'Borrowed',
                        flag   = 'PENDING',
                        transaction_date = NOW()
                  WHERE id = :id
                    AND flag = 'ACTIVE'";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([':id' => $id]);
        $affected1 = $stmt1->rowCount();

        $sql2 = "UPDATE books
                    SET status = 'Borrowed'
                 WHERE book_id = (SELECT book_id FROM transactions WHERE id = :id)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([':id' => $id]);
        $affected2 = $stmt2->rowCount();

        $pdo->commit();

        echo json_encode([
          'success'  => true,
          'affected' => ['transactions' => $affected1, 'books' => $affected2],
          'id'       => $id,
          'action'   => $action
        ]);
        exit;
      } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
      }

    case 'fetch':
      // Move from "To Fetch" to "Fetching"; only if still ACTIVE
      $sql = "UPDATE transactions
                 SET status = 'Fetching'
               WHERE id = :id AND flag = 'ACTIVE'";
      break;

    case 'ack_returned':
      $pdo->beginTransaction();
      try {
        $sql1 = "UPDATE transactions
                    SET flag = 'DONE',
                        transaction_date = NOW()
                 WHERE id = :id
                   AND status = 'Returned'
                   AND flag = 'ACTIVE'";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([':id' => $id]);
        $affected1 = $stmt1->rowCount();

        $sql2 = "UPDATE books
                    SET status = 'Available'
                 WHERE book_id = (SELECT book_id FROM transactions WHERE id = :id)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([':id' => $id]);
        $affected2 = $stmt2->rowCount();

        $pdo->commit();

        echo json_encode([
          'success'  => true,
          'affected' => ['transactions' => $affected1, 'books' => $affected2],
          'id'       => $id,
          'action'   => $action
        ]);
        exit;
      } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
      }

    case 'cancel': // ← NEW: Cancel a Reserved transaction
      $pdo->beginTransaction();
      try {
        // 1) close the reservation
        $sql1 = "UPDATE transactions
                    SET flag = 'DONE',
                        status = 'Cancelled',
                        transaction_date = NOW()
                 WHERE id = :id
                   AND status = 'Reserved'
                   AND flag IN ('ACTIVE','PENDING')";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([':id' => $id]);
        $affected1 = $stmt1->rowCount();

        if ($affected1 === 0) {
          // nothing changed -> either not Reserved or not eligible
          $pdo->rollBack();
          echo json_encode([
            'success' => false,
            'error'   => 'Reservation not found or not eligible for cancel.',
            'id'      => $id,
            'action'  => $action
          ]);
          exit;
        }

        // 2) free the book
        $sql2 = "UPDATE books
                    SET status = 'Available'
                 WHERE book_id = (SELECT book_id FROM transactions WHERE id = :id)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([':id' => $id]);
        $affected2 = $stmt2->rowCount();

        $pdo->commit();

        echo json_encode([
          'success'  => true,
          'affected' => ['transactions' => $affected1, 'books' => $affected2],
          'id'       => $id,
          'action'   => $action
        ]);
        exit;
      } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
      }

    default:
      throw new Exception('Invalid action');
  }

  // generic single-statement path
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':id' => $id]);

  echo json_encode([
    'success'  => true,
    'affected' => $stmt->rowCount(),
    'id'       => $id,
    'action'   => $action
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
