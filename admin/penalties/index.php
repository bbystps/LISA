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
        <div class="hello">Welcome, <b>Library Administrator</b></div>
      </div>

      <div class="content">
        <h1 class="page-title">Penalty Management</h1>

        <!-- KPI cards -->
        <div class="kpis">
          <div class="card panel">
            <div class="kpi">
              <div>
                <h4>Total Penalties</h4>
                <div class="num" id="kpiTotal">₱0.00</div>
              </div>
              <div class="ic gray">₱</div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="table-wrap" style="margin-top:14px;">
          <table id="penaltiesTable" class="modern display" style="width:100%">
            <thead>
              <tr>
                <th>Student</th>
                <th>Book</th>
                <th>Due Date</th>
                <th>Days Late</th>
                <th>Penalty</th>
                <th>Status</th>
                <th style="width:140px">Actions</th>
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
  <?php include("penalties_script.php"); ?>
</body>

</html>