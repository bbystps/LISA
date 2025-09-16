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
        <a class="active">… <span>Transactions</span></a>
        <a href="../penalties">… <span>Penalties</span></a>
      </nav>
    </aside>

    <main class="main">
      <div class="topbar">
        <div></div>
        <div class="hello">Welcome, Administrator</div>
      </div>

      <div class="content">
        <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
          <button class="btn secondary" id="btnExport">
            <!-- download icon -->
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <path d="M7 10l5 5 5-5" />
              <path d="M12 15V3" />
            </svg>
            Export
          </button>
        </div>

        <h1 class="page-title">Transaction History</h1>

        <div class="toolbar">
          <input id="txSearch" class="input" type="search" placeholder="Search transactions...">
          <select id="txType" class="select">
            <option value="">All Types</option>
            <option>Borrow</option>
            <option>Return</option>
            <option>Renew</option>
            <option>Lost</option>
          </select>
          <!-- native date works; we’ll also accept dd/mm/yyyy text -->
          <input id="txDate" class="input" type="date" placeholder="dd/mm/yyyy" />
          <button id="btnFilter" class="btn">
            <!-- funnel icon -->
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 4h18l-7 8v6l-4 2v-8L3 4z" />
            </svg>
            Filter
          </button>
        </div>

        <div class="table-wrap">
          <table id="transactionsTable" class="modern display" style="width:100%">
            <thead>
              <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Book</th>
                <th>Type</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Penalty</th>
                <th style="width:120px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Sample rows (feel free to remove and swap to server data) -->
              <tr>
                <td>12/09/2025</td>
                <td>John Doe</td>
                <td>1984</td>
                <td>Borrow</td>
                <td>19/09/2025</td>
                <td><span class="badge borrowed">On Loan</span></td>
                <td>₱0</td>
                <td class="actions">
                  <button class="icon-btn view" title="View">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" />
                      <circle cx="12" cy="12" r="3" />
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
              <tr>
                <td>01/09/2025</td>
                <td>Jane Smith</td>
                <td>The Great Gatsby</td>
                <td>Return</td>
                <td>08/09/2025</td>
                <td><span class="badge available">Returned</span></td>
                <td>₱0</td>
                <td class="actions">
                  <button class="icon-btn view" title="View">…</button>
                  <button class="icon-btn del" title="Delete">…</button>
                </td>
              </tr>
              <tr>
                <td>20/08/2025</td>
                <td>John Doe</td>
                <td>To Kill a Mockingbird</td>
                <td>Borrow</td>
                <td>03/09/2025</td>
                <td><span class="badge reserved">Overdue</span></td>
                <td>₱50</td>
                <td class="actions">
                  <button class="icon-btn view" title="View">…</button>
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

  <?php include("transactions_script.php"); ?>
</body>

</html>