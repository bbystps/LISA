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
        // 1) update the transaction (only if still ACTIVE)
        $sql1 = "UPDATE transactions
                SET status = 'Borrowed',
                    flag   = 'PENDING',
                    transaction_date = NOW()
              WHERE id = :id
                AND flag = 'ACTIVE'";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([':id' => $id]);
        $affected1 = $stmt1->rowCount();

        // 2) set the corresponding book to Borrowed
        // (ties via the same transaction id)
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
        exit; // prevent the generic $stmt handler below
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
        // 1) mark transaction DONE
        $sql1 = "UPDATE transactions
                SET flag = 'DONE',
                    transaction_date = NOW()
             WHERE id = :id
               AND status = 'Returned'
               AND flag = 'ACTIVE'";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([':id' => $id]);
        $affected1 = $stmt1->rowCount();

        // 2) book → Available
        $sql2 = "UPDATE books
                SET status = 'Available'
             WHERE book_id = (SELECT book_id FROM transactions WHERE id = :id)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([':id' => $id]);
        $affected2 = $stmt2->rowCount();

        $pdo->commit();

        // respond and STOP here so the generic $stmt code doesn't run
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
      // no break needed due to exit

    default:
      throw new Exception('Invalid action');
  }

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':id' => $id]);

  echo json_encode([
    'success' => true,
    'affected' => $stmt->rowCount(),
    'id' => $id,
    'action' => $action
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
