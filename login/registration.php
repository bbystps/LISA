<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Registration</title>
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->

  <link rel="stylesheet" href="../includes/css/register.css">
</head>

<body>
  <div class="wrap max">
    <header class="page-head">
      <div class="kicker">LISA</div>
      <h1 class="title">Register Student</h1>
      <p class="subtitle">Create a new student account to access the system. Use official details only.</p>
    </header>

    <section class="card" aria-label="Registration form">
      <form action="register.php" method="post" novalidate>
        <fieldset>
          <div class="row">
            <div class="ctr">
              <label for="student_id">Student ID<span class="req" aria-hidden="true">*</span></label>
              <input id="student_id" name="student_id" type="text" inputmode="numeric" placeholder="e.g., 2025-00123" minlength="3" maxlength="64" required aria-describedby="student_id_hint">
              <div id="student_id_hint" class="hint">Max 64 chars. Follow school format.</div>
            </div>
            <div class="ctr">
              <label for="name">Full Name<span class="req" aria-hidden="true">*</span></label>
              <input id="name" name="name" type="text" placeholder="Juan Dela Cruz" maxlength="256" required>
            </div>
          </div>

          <div class="row">
            <div class="ctr">
              <label for="email">Email<span class="req" aria-hidden="true">*</span></label>
              <input id="email" name="email" type="email" placeholder="you@school.edu" maxlength="256" required>
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
            </div>
          </div>

          <div class="row">
            <div class="ctr">
              <label for="password">Password<span class="req" aria-hidden="true">*</span></label>
              <input id="password" name="password" type="password" minlength="8" maxlength="256" placeholder="At least 8 characters" required aria-describedby="pwd_hint">
              <div id="pwd_hint" class="hint">Use 8+ chars with a mix of letters, numbers, and symbols.</div>
            </div>
            <div class="ctr">
              <label for="password_confirm">Confirm Password<span class="req" aria-hidden="true">*</span></label>
              <input id="password_confirm" name="password_confirm" type="password" minlength="8" maxlength="256" placeholder="Re-enter your password" required>
            </div>
          </div>

          <div class="terms">
            <input type="checkbox" id="agree" name="agree" required>
            <label for="agree">I agree to the <a href="#" style="color:#2563eb; text-decoration:none; font-weight:600;">Terms of Service</a> and <a href="#" style="color:#2563eb; text-decoration:none; font-weight:600;">Privacy Policy</a>.</label>
          </div>
        </fieldset>

        <div class="actions">
          <button class="btn secondary" type="reset">Reset</button>
          <button class="btn" type="submit">Create account</button>
        </div>

        <p class="legal">Already registered? <a href="index.php" style="color:#2563eb; font-weight:600; text-decoration:none;">Sign in</a></p>
      </form>
    </section>
  </div>
</body>

</html>