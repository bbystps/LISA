<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Library System — Admin Dashboard</title>

  <link rel="stylesheet" href="../../datatables/jquery.dataTables.min.css">
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
      </div>

      <div class="content">

        <div class="toolbar">
          <div class="left">
            <div class="page-title">Students</div>
          </div>
          <div class="right">
            <button class="btn add" id="btnAddStudent">
              Add Student
            </button>
          </div>
        </div>

        <div class="table-wrap">
          <table id="studentsTable" class="display nowrap table" cellspacing="0" style="width:100%">
            <thead>
              <tr>
                <th>RFID</th> <!-- keep this -->
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Books Borrowed</th>
                <!-- <th>Actions</th> -->
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>


      </div>
    </main>
  </div>

  <!-- Registration Modal -->
  <div class="modal" id="studentRegModal" aria-hidden="true">
    <div class="modal__overlay" data-close-modal></div>

    <!-- Dialog is the a11y surface -->
    <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="reg_title" tabindex="-1">
      <div class="modal__header">
        <h2 id="reg_title">Student Registration</h2>
        <button class="modal__close" type="button" title="Close" aria-label="Close" data-close-modal>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="modal__body">
        <!-- your provided form -->
        <form id="studentRegForm" action="register.php" method="post" novalidate>
          <fieldset>
            <div class="row">
              <div class="ctr">
                <label for="rfid_key">RFID Key<span class="req" aria-hidden="true">*</span></label>
                <input id="rfid_key" name="rfid_key" type="text" inputmode="numeric"
                  placeholder="e.g., 04AABBCCDDEE" minlength="3" maxlength="64" required>
                <p class="err-msg" data-err-for="rfid_key"></p>
              </div>

              <div class="ctr">
                <label for="student_id">Student ID<span class="req" aria-hidden="true">*</span></label>
                <input id="student_id" name="student_id" type="text" inputmode="numeric"
                  placeholder="e.g., 2025-00123" minlength="3" maxlength="64" required>
                <p class="err-msg" data-err-for="student_id"></p>
              </div>

              <!-- Full width name field -->
              <div class="ctr full">
                <label for="name">Full Name<span class="req" aria-hidden="true">*</span></label>
                <input id="name" name="name" type="text" placeholder="Juan Dela Cruz" maxlength="256" required>
                <p class="err-msg" data-err-for="name"></p>
              </div>
            </div>

            <div class="row">
              <div class="ctr">
                <label for="email">Email<span class="req" aria-hidden="true">*</span></label>
                <input id="email" name="email" type="email" placeholder="you@school.edu" maxlength="256" required>
                <p class="err-msg" data-err-for="email"></p>
              </div>
              <div class="ctr">
                <label for="course">Course<span class="req" aria-hidden="true">*</span></label>
                <select id="course" name="course" required>
                  <option value="" disabled selected>Select your course…</option>
                  <option value="BS ECE">BS ECE</option>
                  <option value="BS EE">BS EE</option>
                  <option value="BS CpE">BS CpE</option>
                  <option value="BS CS">BS CS</option>
                  <option value="BS IT">BS IT</option>
                  <option value="BS ME">BS ME</option>
                  <option value="BS CE">BS CE</option>
                  <option value="Other">Other</option>
                </select>
                <p class="err-msg" data-err-for="course"></p>
              </div>
            </div>

            <div class="row">
              <div class="ctr">
                <label for="password">Password<span class="req" aria-hidden="true">*</span></label>
                <input id="password" name="password" type="password" minlength="8" maxlength="256" placeholder="At least 8 characters" required aria-describedby="pwd_hint">
                <p class="err-msg" data-err-for="password"></p>
              </div>
              <div class="ctr">
                <label for="password_confirm">Confirm Password<span class="req" aria-hidden="true">*</span></label>
                <input id="password_confirm" name="password_confirm" type="password" minlength="8" maxlength="256" placeholder="Re-enter your password" required>
                <p class="err-msg" data-err-for="password_confirm"></p>
              </div>
            </div>

          </fieldset>

          <div id="formAlert" class="form-alert" role="alert" aria-live="polite"></div>

          <div class="actions">
            <button class="btn secondary" type="reset">Reset</button>
            <button class="btn" type="submit" id="regSubmitBtn">Create account</button>
          </div>

        </form>
      </div>
    </div>
  </div>


  <script src="../../includes/js/jquery.min.js"></script>
  <script src="../../datatables/jquery.dataTables.js"></script>

  <?php include("../sidenav_script.php"); ?>
  <?php include("students_script.php"); ?>
  <?php include("modal_script.php"); ?>

</body>

</html>