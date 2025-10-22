<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library System — Admin Dashboard</title>
  <!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> -->

  <link rel="stylesheet" href="../../plugins/datatables/datatables.css">
  <link rel="stylesheet" href="../../includes/css/admin.css">
  <link rel="stylesheet" href="../../includes/css/modal.css">
  <link rel="stylesheet" href="../../includes/css/icon.css">
  <link rel="stylesheet" href="../../plugins/toastr/toastr.css">
</head>

<script src="../../mqtt/mqttws31.js"></script>
<?php include("../../mqtt/transaction_mqtt.php"); ?>

<body onload="client.connect(options);">
  <div class="app">

    <?php include("../sidenav.php"); ?>

    <main class="main">
      <div class="topbar">
        <div></div>
        <div class="hello">Welcome, Administrator</div>
        <!-- <div><a class="logout" href="logout.php">Logout</a></div> -->
      </div>

      <div class="content">

        <div class="toolbar">
          <div class="left">
            <div class="page-title">Transaction Management</div>
          </div>
          <div class="right">

            <!-- NEW: Retry button -->
            <button class="btn" id="btnRetry">
              Retry (revert Delivering/Fetching)
            </button>
            <!-- NEW: GO button + counter -->
            <button class="btn primary" id="btnGoDeliver" disabled>
              GO (D:<span id="selDeliver">0</span> / F:<span id="selFetch">0</span> | Total <span id="selCount">0</span>)
            </button>

          </div>
        </div>

        <div class="table-wrap">
          <table id="booksTable" class="display nowrap table" cellspacing="0" style="width:100%">
            <thead>
              <tr>
                <th>id</th>
                <th>student_id</th>
                <th>book_id</th>
                <th>Name</th>
                <th>Title</th>
                <th>Author</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Flag</th>
                <th>Location</th>
                <th>rfid</th>
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

  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../plugins/toastr/toastr.min.js"></script>
  <script src="../../plugins/datatables/datatables.js"></script>

  <?php include("../sidenav_script.php"); ?>
  <?php include("transactions_script.php"); ?>

</body>

</html>