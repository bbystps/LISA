<?php
session_start();

if (empty($_SESSION['user'])) {
  header('Location: ../../login/index.php');
  exit;
}

$name = $_SESSION['user']['name'];
$studentId = $_SESSION['user']['student_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library System — Admin Dashboard</title>
  <!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> -->

  <link rel="stylesheet" href="../../plugins/datatables/datatables.css">
  <link rel="stylesheet" href="../../includes/css/users.css">
  <link rel="stylesheet" href="../../includes/css/user_modal.css">
  <link rel="stylesheet" href="../../includes/css/icon.css">
</head>

<body>
  <div class="app">

    <?php include("../sidenav.php"); ?>

    <main class="main">
      <div class="topbar">
        <div></div>
        <div class="hello">Welcome, <?= htmlspecialchars($name) ?></div>
        <!-- <div><a class="logout" href="logout.php">Logout</a></div> -->
      </div>

      <div class="content">

        <div class="toolbar">
          <div class="left">
            <div class="page-title">Browse Books</div>
          </div>
          <!-- <div class="right">
            <div class="borrow-transact">Books Borrowed/Reserved: 0/3</div>
          </div> -->
        </div>

        <div class="table-wrap">
          <table id="booksTable" class="display nowrap table" cellspacing="0" style="width:100%">
            <thead>
              <tr>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>

  <!-- Borrow Modal -->
  <div class="modal" id="borrowModal" aria-hidden="true">
    <div class="modal__overlay" data-close="true"></div>

    <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="borrowTitle">
      <div class="modal__header">
        <h2 id="borrowTitle">Borrow Book</h2>
        <button class="modal__close" id="borrowCloseBtn" aria-label="Close">&times;</button>
      </div>

      <div class="modal__body">
        <div class="form-alert" id="borrowAlert"></div>

        <form id="borrowForm" autocomplete="off" enctype="multipart/form-data">
          <fieldset>
            <div class="row">
              <div class="ctr full">
                <label>Book</label>
                <input type="hidden" name="student_id" id="student_id" value="<?= htmlspecialchars($studentId) ?>">
                <input type="text" id="borrowBookDisplay" name="borrowBookDisplay" readonly>
                <input type="hidden" name="borrow_isbn" id="borrow_isbn">
              </div>

              <div class="ctr">
                <label>Borrow Date <span class="req">*</span></label>
                <input type="date" name="borrow_date" id="borrow_date" required>
                <div class="hint"></div>
              </div>

              <div class="ctr">
                <label>Return Date <span class="req">*</span></label>
                <input type="date" name="return_date" id="return_date" required>
                <div class="hint">Default is 7 days from borrowed date.</div>
              </div>

              <div class="ctr full">
                <div class="err-msg" id="borrowInlineErr"></div>
              </div>
            </div>

            <div class="actions">
              <button type="button" class="btn" data-action="cancel">Cancel</button>
              <button type="submit" class="btn add" id="borrowSubmitBtn">Confirm Borrow</button>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
  </div>


  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../plugins/datatables/datatables.js"></script>

  <?php include("../sidenav_script.php"); ?>
  <?php include("books_script.php"); ?>
  <?php include("borrow_script.php"); ?>

</body>

</html>