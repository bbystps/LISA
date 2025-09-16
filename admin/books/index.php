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
        <a class="active">… <span>Books</span></a>
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

        <h1 class="page-title">Books Management</h1>

        <div class="toolbar">
          <div class="left">
            <input id="bookSearch" class="input" type="search" placeholder="Search books by title, author, or ISBN…">
            <select id="bookCategory" class="select">
              <option value="">All Categories</option>
              <option>Fiction</option>
              <option>Non-Fiction</option>
              <option>Computer Science</option>
              <option>History</option>
            </select>
            <button id="btnSearch" class="btn">Search</button>
          </div>
          <div class="right">
            <button class="btn add" id="btnAddBook">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                <path d="M12 5v14M5 12h14" />
              </svg>
              Add Book
            </button>
          </div>
        </div>

        <div class="table-wrap">
          <table id="booksTable" class="modern display" style="width:100%">
            <thead>
              <tr>
                <th style="min-width:260px">Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Category</th>
                <th>Status</th>
                <th style="width:140px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><a href="#" class="table-title">The Great Gatsby</a></td>
                <td>F. Scott Fitzgerald</td>
                <td>978-0-7432-7356-5</td>
                <td>Fiction</td>
                <td><span class="badge available">Available</span></td>
                <td class="actions">
                  <button class="icon-btn edit" title="Edit">…</button>
                  <button class="icon-btn move" title="Transfer">…</button>
                  <button class="icon-btn del" title="Delete">…</button>
                </td>
              </tr>
              <tr>
                <td><a href="#" class="table-title">To Kill a Mockingbird</a></td>
                <td>Harper Lee</td>
                <td>978-0-06-112008-4</td>
                <td>Fiction</td>
                <td><span class="badge available">Available</span></td>
                <td class="actions">
                  <button class="icon-btn edit" title="Edit">…</button>
                  <button class="icon-btn move" title="Transfer">…</button>
                  <button class="icon-btn del" title="Delete">…</button>
                </td>
              </tr>
              <tr>
                <td><a href="#" class="table-title">1984</a></td>
                <td>George Orwell</td>
                <td>978-0-452-28423-4</td>
                <td>Fiction</td>
                <td><span class="badge available">Available</span></td>
                <td class="actions">
                  <button class="icon-btn edit" title="Edit">…</button>
                  <button class="icon-btn move" title="Transfer">…</button>
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

  <?php include("books_script.php"); ?>
</body>

</html>