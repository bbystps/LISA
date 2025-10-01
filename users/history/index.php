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
            <div class="page-title">Transaction History</div>
          </div>
          <!-- <div class="right">
            <div class="borrow-transact">Books Borrowed/Reserved: 0/3</div>
          </div> -->
        </div>

        <div class="table-wrap">
          <table id="booksTable" class="display nowrap table" cellspacing="0" style="width:100%">
            <thead>
              <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status/Type</th>
                <th>Transact Date</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>

  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../plugins/datatables/datatables.js"></script>

  <?php include("../sidenav_script.php"); ?>
  <?php include("history_script.php"); ?>

</body>

</html>