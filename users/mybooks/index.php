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
            <div class="page-title">My Books</div>
          </div>
          <!-- <div class="right">
            <div class="borrow-transact">Books Borrowed/Reserved: 0/3</div>
          </div> -->
        </div>

        <!-- Hardcoded list for demo -->
        <div class="card">
          <div class="panel">
            <h3>Your Currently Borrowed/Reserved Books</h3>

            <div id="my-books">
              <!-- Item 1: Borrowed -->
              <div class="item" data-status="Borrowed" data-book-id="01237582">
                <div>
                  <strong>Book of love</strong><br>
                  <span class="meta">
                    Book ID: 01237582 · Author: Lovely Miso · Category: Non-Fiction
                  </span><br>
                  <span class="meta">
                    Status: <span class="badge badge-borrowed">Borrowed</span>
                    · Borrowed: 2025-09-22 · Due: <b>2025-09-29</b>
                  </span>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                  <button class="btn outline table-btn" disabled>Return</button>
                </div>
              </div>

              <!-- Item 2: Borrowed -->
              <div class="item" data-status="Borrowed" data-book-id="12345678">
                <div>
                  <strong>No Title</strong><br>
                  <span class="meta">
                    Book ID: 12345678 · Author: No Author · Category: Fiction
                  </span><br>
                  <span class="meta">
                    Status: <span class="badge badge-borrowed">Borrowed</span>
                    · Borrowed: 2025-09-22 · Due: <b>2025-09-29</b>
                  </span>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                  <button class="btn outline table-btn" disabled>Return</button>
                </div>
              </div>

              <!-- Item 3: Reserved -->
              <div class="item" data-status="Reserved" data-book-id="12311678">
                <div>
                  <strong>Libro ni Blor</strong><br>
                  <span class="meta">
                    Book ID: 12311678 · Author: Justin Blor · Category: Fiction
                  </span><br>
                  <span class="meta">
                    Status: <span class="badge badge-reserved">Reserved</span>
                    · Reserved: 2025-09-22
                    <!-- · Reserved: 2025-09-22 · Pickup within: <b>2025-09-29</b> -->
                  </span>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                  <button class="btn outline table-btn" disabled>Cancel</button>
                </div>
              </div>
            </div>

            <div class="meta" id="slots-left" style="margin-top:10px;">
              Slots left: <b>0</b> / 3
            </div>
          </div>
        </div>


      </div>
    </main>
  </div>

  <script src="../../includes/js/jquery.min.js"></script>

  <?php include("../sidenav_script.php"); ?>
  <?php include("mybooks_script.php"); ?>



</body>

</html>