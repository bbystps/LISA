<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library System — Admin Dashboard</title>

  <link rel="stylesheet" href="../../includes/css/admin.css">
  <link rel="stylesheet" href="../../includes/css/icon.css">
  <link rel="stylesheet" href="../../plugins/datatables/datatables.css">
</head>

<body>
  <div class="app">

    <?php include("../sidenav.php"); ?>
    <main class="main">
      <div class="topbar">
        <div></div>
        <div class="hello">Welcome, Administrator</div>
      </div>

      <div class="content">

        <h1 class="page-title">Students</h1>

        <div class="toolbar">
          <div class="left">
            <input id="studentSearch" class="input" type="search" placeholder="Search students...">
          </div>
          <div class="right">
            <button class="btn add" id="btnAddStudent">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                <path d="M12 5v14M5 12h14" />
              </svg>
              Add Student
            </button>
          </div>
        </div>

        <div class="table-wrap">
          <table id="studentsTable" class="modern display" style="width:100%">
            <thead>
              <tr>
                <!-- Hidden RFID column (index 0) -->
                <th>RFID</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Books Borrowed</th>
                <th style="width:140px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>04AABBCCDDEE</td>
                <td>STU001</td>
                <td>John Doe</td>
                <td>student@library.com</td>
                <td>Computer Science</td>
                <td>0/3</td>
                <td class="actions">
                  <button class="icon-btn edit" title="Edit">
                    <!-- pencil -->
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M12 20h9" />
                      <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                    </svg>
                  </button>
                  <button class="icon-btn del" title="Delete">
                    <!-- trash -->
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6" />
                      <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                      <path d="M10 11v6M14 11v6" />
                      <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                    </svg>
                  </button>
                </td>
              </tr>
              <!-- Add more rows or swap to server-side later -->
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../plugins/datatables/datatables.js"></script>

  <?php include("../sidenav_script.php"); ?>
  <?php include("students_script.php"); ?>
</body>

</html>