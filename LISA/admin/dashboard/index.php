<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library System — Admin Dashboard</title>
  <!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> -->

  <link rel="stylesheet" href="../../includes/css/admin.css">
  <link rel="stylesheet" href="../../includes/css/icon.css">
  <link rel="stylesheet" href="../../plugins/toastr/toastr.css">
  <link rel="stylesheet" href="../../plugins/datatables/datatables.css">
</head>

<script src="../../mqtt/mqttws31.js"></script>
<?php include("../../mqtt/admin_mqtt.php"); ?>

<body onload="client.connect(options);">
  <div class="app">
    <aside class="side">
      <div class="brand">
        <i class="mdi--library"></i>
        <span>LISA</span>
      </div>
      <nav class="nav">
        <a class="active">… <span>Dashboard</span></a>
        <a href="../students">… <span>Students</span></a>
        <a href="../books">… <span>Books</span></a>
        <a href="../transactions">… <span>Transactions</span></a>
        <a href="../penalties">… <span>Penalties</span></a>
      </nav>

    </aside>

    <main class="main">
      <div class="topbar">
        <div></div>
        <div class="hello">Welcome, Administrator</div>
        <!-- <div><a class="logout" href="logout.php">Logout</a></div> -->
      </div>

      <div class="content">
        <h1 class="page-title">Dashboard</h1>

        <section class="kpis">
          <div class="card kpi">
            <div>
              <h4>Total Books</h4>
              <div class="num" data-kpi="total-books">1,284</div>
            </div>
            <div class="ic blue" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                <path d="M20 22V6a2 2 0 0 0-2-2H7a3 3 0 0 0-3 3v14" />
              </svg>
            </div>
          </div>

          <div class="card kpi">
            <div>
              <h4>Active Students</h4>
              <div class="num" data-kpi="active-students">436</div>
            </div>
            <div class="ic teal" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 11c1.657 0 3-1.79 3-4s-1.343-4-3-4-3 1.79-3 4 1.343 4 3 4z" />
                <path d="M2 20c0-3.866 3.582-7 8-7" />
              </svg>
            </div>
          </div>

          <div class="card kpi">
            <div>
              <h4>Books Borrowed</h4>
              <div class="num" data-kpi="borrowed-now">93</div>
            </div>
            <div class="ic gray" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8h-7a4 4 0 1 0 0 8h7" />
                <polyline points="14 4 18 8 14 12" />
              </svg>
            </div>
          </div>

          <div class="card kpi">
            <div>
              <h4>Overdue</h4>
              <div class="num" style="color:#dc2626" data-kpi="overdue-now">7</div>
            </div>
            <div class="ic red" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 9v4" />
                <path d="M12 17h.01" />
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
              </svg>
            </div>
          </div>
        </section>

        <section class="grid">
          <div class="card">
            <div class="panel">
              <h3>New Transactions</h3>

              <!-- Example transactions (static). Replace/update via JS as needed. -->
              <div class="item">
                <div>
                  <div><strong>Introduction to Algorithms</strong></div>
                  <div class="meta">Borrowed by Jane Santos (202300123)</div>
                </div>
                <div class="meta">
                  Sep 10, 2025 14:30
                  <div>Due: Sep 24, 2025</div>
                </div>
              </div>

              <div class="item">
                <div>
                  <div><strong>Clean Code</strong></div>
                  <div class="meta">Borrowed by John Cruz (202200045)</div>
                </div>
                <div class="meta">
                  Sep 12, 2025 09:10
                  <div>Returned: Sep 14, 2025</div>
                </div>
              </div>

              <div class="item">
                <div>
                  <div><strong>Data Structures in C</strong></div>
                  <div class="meta">Borrowed by Maria Reyes (202400678)</div>
                </div>
                <div class="meta">
                  Sep 14, 2025 16:05
                  <div>Due: Sep 28, 2025</div>
                </div>
              </div>

              <!-- Empty state example (show this instead of items if needed)
              <div class="empty">No recent transactions</div>
              -->
            </div>
          </div>

          <div class="card">
            <div class="panel">
              <h3>Overdue Books</h3>

              <!-- Example overdue items (static). Replace/update via JS as needed. -->
              <div class="item">
                <div>
                  <div><strong>Operating Systems: Three Easy Pieces</strong></div>
                  <div class="meta">Borrowed by Mark Dela Cruz (202100512)</div>
                </div>
                <div class="meta">Due: Sep 08, 2025</div>
              </div>

              <div class="item">
                <div>
                  <div><strong>Design Patterns</strong></div>
                  <div class="meta">Borrowed by Anna Lopez (202300321)</div>
                </div>
                <div class="meta">Due: Sep 05, 2025</div>
              </div>

              <!-- Empty state example
              <div class="empty">No overdue books</div>
              -->
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../plugins/toastr/toastr.min.js"></script>
  <script src="../../plugins/datatables/datatables.js"></script>

</body>

</html>