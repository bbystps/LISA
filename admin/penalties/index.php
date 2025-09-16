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
    <aside class="side">
      <div class="brand">
        <i class="mdi--library"></i>
        <span>LISA</span>
      </div>
      <nav class="nav">
        <a href="../dashboard">… <span>Dashboard</span></a>
        <a href="../students">… <span>Students</span></a>
        <a href="../books">… <span>Books</span></a>
        <a href="../transactions">… <span>Transactions</span></a>
        <a class="active">… <span>Penalties</span></a>
      </nav>
    </aside>

    <main class="main">
      <div class="topbar">
        <div></div>
        <div class="hello">Welcome, <b>Library Administrator</b></div>
        <button class="btn secondary" id="btnSaveSettings" title="Save penalty settings" style="margin-left:auto">
          <!-- gear -->
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1.82v.08a2 2 0 1 1-3.32 0v-.08A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1-.6 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.82-.33h-.08a2 2 0 1 1 0-3.32h.08A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.82-.33l-.06.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6c.25-.11.47-.27.6-.5a1.65 1.65 0 0 0 .33-1.82V2.2a2 2 0 1 1 3.32 0v.08c.09.32.22.62.4.9.13.22.35.39.6.5A1.65 1.65 0 0 0 19.4 9c.31.24.7.37 1.1.35h.1a2 2 0 1 1 0 3.3h-.08c-.4-.02-.79.11-1.12.35z" />
          </svg>
          Save Settings
        </button>
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

          <div class="card panel">
            <div class="kpi">
              <div>
                <h4>Paid Penalties</h4>
                <div class="num" id="kpiPaid">₱0.00</div>
              </div>
              <div class="ic teal">✓</div>
            </div>
          </div>

          <div class="card panel">
            <div class="kpi">
              <div>
                <h4>Outstanding</h4>
                <div class="num" id="kpiOutstanding" style="color:#ef4444">₱0.00</div>
              </div>
              <div class="ic red">!</div>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <div class="card panel" style="margin-top:14px;">
          <h3>Penalty Configuration</h3>
          <div class="grid" style="grid-template-columns: 1fr 1fr;">
            <div>
              <label style="font-weight:700; display:block; margin:6px 0 6px;">Daily Penalty Rate (₱)</label>
              <input id="penaltyRate" class="input" type="number" step="0.01" min="0" value="5.00" />
            </div>
            <div>
              <label style="font-weight:700; display:block; margin:6px 0 6px;">Grace Period (days)</label>
              <input id="gracePeriod" class="input" type="number" step="1" min="0" value="1" />
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
              <!-- Sample rows (replace with server data or keep for demo) -->
              <tr data-status="Unpaid">
                <td>John Doe</td>
                <td>1984</td>
                <td>01/09/2025</td>
                <td></td>
                <td></td>
                <td><span class="badge reserved">Unpaid</span></td>
                <td class="actions">
                  <button class="icon-btn pay" title="Mark as Paid">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="20 6 9 17 4 12" />
                    </svg>
                  </button>
                  <button class="icon-btn del" title="Delete">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6" />
                      <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                      <path d="M10 11v6M14 11v6" />
                      <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                    </svg>
                  </button>
                </td>
              </tr>
              <tr data-status="Paid">
                <td>Jane Smith</td>
                <td>The Great Gatsby</td>
                <td>15/08/2025</td>
                <td></td>
                <td></td>
                <td><span class="badge available">Paid</span></td>
                <td class="actions">
                  <button class="icon-btn pay" title="Mark as Paid">✓</button>
                  <button class="icon-btn del" title="Delete">…</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>

  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../plugins/datatables/datatables.js"></script>

  <?php include("penalties_script.php"); ?>
</body>

</html>