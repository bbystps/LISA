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
</head>

<body>
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
            <div class="page-title">Books Management</div>
          </div>
          <div class="right">
            <button class="btn add" id="btnAddBook">
              Add Book
            </button>
          </div>
        </div>

        <div class="table-wrap">
          <table id="booksTable" class="display nowrap table" cellspacing="0" style="width:100%">
            <thead>
              <tr>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Status</th>
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


  <!-- Book Registration Modal -->
  <div class="modal" id="bookRegModal" aria-hidden="true">
    <div class="modal__overlay" data-close-modal></div>

    <!-- Dialog is the a11y surface -->
    <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="reg_title" tabindex="-1">
      <div class="modal__header">
        <h2 id="reg_title">Book Registration</h2>
        <button class="modal__close" type="button" title="Close" aria-label="Close" data-close-modal>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="modal__body">
        <!-- your provided form -->
        <form id="studentRegForm" action="register.php" method="post" novalidate>
          <input type="hidden" id="original_id" name="original_id" value="">
          <fieldset>
            <div class="row">
              <div class="ctr">
                <label for="book_id">ISBN<span class="req" aria-hidden="true">*</span></label>
                <input id="book_id" name="book_id" type="text" inputmode="numeric"
                  placeholder="e.g., 04AABBCCDDEE" minlength="3" maxlength="64" required>
                <p class="err-msg" data-err-for="book_id"></p>
              </div>

              <div class="ctr">
                <label for="category">Category<span class="req" aria-hidden="true">*</span></label>
                <select id="category" name="category" required>
                  <option value="" disabled selected>Select your course…</option>
                  <option value="Fiction">Fiction</option>
                  <option value="Non-Fiction">Non-Fiction</option>
                  <option value="Computer Science">Computer Science</option>
                  <option value="History">History</option>
                </select>
                <p class="err-msg" data-err-for="category"></p>
              </div>

              <!-- Full width name field -->
              <div class="ctr full">
                <label for="title">Title<span class="req" aria-hidden="true">*</span></label>
                <input id="title" name="title" type="text" placeholder="Juan Dela Cruz" maxlength="256" required>
                <p class="err-msg" data-err-for="title"></p>
              </div>
              <!-- Full width name field -->
              <div class="ctr full">
                <label for="author">Author<span class="req" aria-hidden="true">*</span></label>
                <input id="author" name="author" type="text" placeholder="Juan Dela Cruz" maxlength="256" required>
                <p class="err-msg" data-err-for="author"></p>
              </div>
            </div>
          </fieldset>

          <div id="formAlert" class="form-alert" role="alert" aria-live="polite"></div>

          <div class="actions">
            <button class="btn secondary" type="reset">Reset</button>
            <button class="btn" type="submit" id="regSubmitBtn">Register Book</button>
          </div>

        </form>
      </div>
    </div>
  </div>

  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../plugins/datatables/datatables.js"></script>

  <?php include("../sidenav_script.php"); ?>
  <?php include("books_script.php"); ?>
  <?php include("modal_script.php"); ?>
</body>

</html>