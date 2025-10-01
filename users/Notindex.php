<?php
// dashboard.php (student)
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../login/index.php');
  exit;
}
$user = $_SESSION['user'];
// Borrow limit can be configured; using 3 for demo
$BORROW_LIMIT = 3;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library System — Student</title>
  <!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> -->
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

  <link rel="stylesheet" href="../includes/css/dashboard.css">
</head>

<body>
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
        <div class="hello">Welcome, <b><?php echo htmlspecialchars($user['name'] ?: $user['email']); ?></b></div>
        <div class="top-right">
          <span>Books borrowed: <b id="borrowedCount">0</b>/<?php echo (int)$BORROW_LIMIT; ?></span>
          <a class="logout" href="logout.php">Logout</a>
        </div>
      </div>

      <div class="content">
        <div class="page-head">
          <h1 class="title">Browse Books</h1>
        </div>

        <section class="card">
          <div class="toolbar">
            <div class="search"><input type="text" id="q" placeholder="Search books by title, author, or category…"></div>
            <select class="filter" id="cat">
              <option value="">All Categories</option>
              <option>Fiction</option>
              <option>Non-fiction</option>
              <option>Science</option>
              <option>Technology</option>
              <option>History</option>
            </select>
            <button class="btn" id="btnSearch">Search</button>
          </div>
          <div style="padding:12px">
            <table id="books" class="display" style="width:100%">
              <thead>
                <tr>
                  <th style="width:40%">Title</th>
                  <th style="width:23%">Author</th>
                  <th style="width:16%">Category</th>
                  <th style="width:11%">Status</th>
                  <th style="width:10%">Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- jQuery + DataTables -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>


  <script>
    // ---- Config ----
    const BORROW_LIMIT = <?php echo (int)$BORROW_LIMIT; ?>;

    // Initialize DataTable
    const dt = $(function() {
      const table = $('#books').DataTable({
        ajax: {
          url: 'api/books_dt.php', // Return { data: [ {title, author, category, status, book_id} ... ] }
          dataSrc: 'data'
        },
        columns: [{
            data: 'title'
          },
          {
            data: 'author'
          },
          {
            data: 'category'
          },
          {
            data: 'status',
            render: function(v) {
              const ok = (v || '').toLowerCase() === 'available';
              return `<span class="status ${ok? 'ok':'bad'}">${ok? 'Available':'Unavailable'}</span>`;
            }
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            render: function(row) {
              const disabled = (row.status || '').toLowerCase() !== 'available' ? 'disabled' : '';
              return `<button class="action-btn borrow" data-id="${row.book_id}" ${disabled}>Borrow</button>`;
            }
          }
        ],
        paging: true,
        pageLength: 10,
        order: [
          [0, 'asc']
        ],
        deferRender: true,
        autoWidth: false
      });

      // Wire custom search and category filter to DataTables
      const applyFilters = () => {
        const q = $('#q').val();
        const c = $('#cat').val();
        table.search(q).draw();
        if (c) {
          // Category is column index 2
          table.column(2).search('^' + c + '$', true, false).draw();
        } else {
          table.column(2).search('').draw();
        }
      };
      $('#btnSearch').on('click', applyFilters);
      $('#q').on('keydown', e => {
        if (e.key === 'Enter') applyFilters();
      });
      $('#cat').on('change', applyFilters);

      // Borrow handler
      $('#books').on('click', 'button.borrow', function() {
        const id = $(this).data('id');
        // Simple guard: check limit client-side; server must enforce too
        const used = parseInt($('#borrowedCount').text() || '0', 10);
        if (used >= BORROW_LIMIT) {
          alert('You have reached your borrow limit.');
          return;
        }
        $(this).prop('disabled', true);
        $.post('api/borrow.php', {
            book_id: id
          })
          .done(resp => {
            alert('Book borrowed successfully.');
            table.ajax.reload(null, false);
            refreshBorrowCount();
          })
          .fail(xhr => {
            alert(xhr.responseText || 'Borrow failed.');
            table.ajax.reload(null, false);
          });
      });

      // Load borrowed count at start
      refreshBorrowCount();

      function refreshBorrowCount() {
        $.getJSON('api/borrowed_count.php')
          .done(d => {
            $('#borrowedCount').text(d.count ?? 0);
          })
          .fail(() => {
            /* keep default 0 */
          });
      }

      return table;
    });
  </script>
</body>

</html>