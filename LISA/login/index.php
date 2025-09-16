<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign in</title>
  <!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"> -->
  <style>
    :root {
      --bg: #f1f5f9;
      /* slate-100 */
      --panel: #ffffff;
      /* white */
      --muted: #64748b;
      /* slate-500 */
      --text: #0f172a;
      /* slate-900 */
      --primary: #2563eb;
      /* blue-600 */
      --primary-2: #1d4ed8;
      /* blue-700 */
      --border: #e5e7eb;
      /* gray-200 */
      --ring: rgba(37, 99, 235, .25);
      --radius: 14px;
    }

    * {
      box-sizing: border-box
    }

    html,
    body {
      height: 100%
    }

    body {
      margin: 0;
      display: grid;
      place-items: center;
      min-height: 100vh;
      background: var(--bg);
      color: var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      line-height: 1.45;
    }

    .wrap {
      width: 100%;
      max-width: 420px;
      padding: 40px 18px;
    }

    .card {
      background: var(--panel);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: 0 1px 2px rgba(0, 0, 0, .03);
      overflow: hidden;
    }

    .head {
      padding: 22px 22px 8px;
    }

    .kicker {
      color: var(--muted);
      font-weight: 600;
      letter-spacing: .4px;
      font-size: 12px;
      text-transform: uppercase;
    }

    .title {
      margin: 6px 0 2px;
      font-size: 24px;
      font-weight: 800;
      letter-spacing: .2px;
      color: #0f172a;
    }

    .subtitle {
      color: var(--muted);
      font-size: 14px;
    }

    form {
      display: grid;
      gap: 16px;
      padding: 18px 22px 22px;
    }

    label {
      font-size: 13px;
      color: #475569;
      display: block;
      margin: 0 0 6px;
      font-weight: 600
    }

    input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: #fff;
      color: var(--text);
      outline: none;
      font-size: 14px;
      transition: box-shadow .15s ease, border-color .15s ease;
    }

    input::placeholder {
      color: #94a3b8
    }

    input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px var(--ring);
    }

    .actions {
      display: flex;
      gap: 10px;
      align-items: center;
      justify-content: space-between;
      padding: 0 22px 22px;
    }

    .btn {
      appearance: none;
      border: 0;
      cursor: pointer;
      background: var(--primary);
      color: #fff;
      padding: 10px 16px;
      border-radius: 10px;
      font-weight: 700;
      box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
      transition: background .2s ease;
    }

    .btn:hover {
      background: var(--primary-2)
    }

    .link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600
    }

    .foot {
      font-size: 12px;
      color: var(--muted);
      text-align: center;
      padding: 0 0 6px
    }

    .error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
      border-radius: 10px;
      padding: 10px 12px;
      margin: 0 22px 10px;
      display: none
    }
  </style>
</head>

<body>
  <div class="wrap">
    <section class="card" aria-label="Login form">
      <div class="head">
        <div class="kicker">LISA</div>
        <h1 class="title">Sign in</h1>
        <p class="subtitle">Access your dashboard using your email/Student ID and password.</p>
      </div>

      <!-- Optional client-side error box (shown if ?error=... present) -->
      <div id="err" class="error" role="alert"></div>

      <form action="authenticate.php" method="post" novalidate>
        <div>
          <label for="handle">Email or Student ID</label>
          <input id="handle" name="handle" type="text" placeholder="Your email or student id" maxlength="256" required>
        </div>
        <div>
          <label for="password">Password</label>
          <input id="password" name="password" type="password" minlength="8" maxlength="256" placeholder="Your password" required>
        </div>
        <div class="actions">
          <a class="link" href="registration.php">Create account</a>
          <button class="btn" type="submit">Sign in</button>
        </div>
        <p class="foot">Forgot your password? Contact the administrator.</p>
      </form>
    </section>
  </div>

  <script>
    // Show simple error if redirected with ?error=...
    (function() {
      const params = new URLSearchParams(window.location.search);
      const e = params.get('error');
      if (e) {
        const box = document.getElementById('err');
        box.textContent = e;
        box.style.display = 'block';
      }
    })();
  </script>
</body>

</html>