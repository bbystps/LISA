<?php
// login.php
require __DIR__ . '/db.php';

// Secure session cookies a bit
session_set_cookie_params([
  'httponly' => true,
  'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  'samesite' => 'Lax',
]);
session_start(['use_strict_mode' => true]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$handle = trim($_POST['handle'] ?? '');        // email OR student_id
$pwd    = $_POST['password'] ?? '';

if ($handle === '' || $pwd === '') {
  header('Location: index.php?error=' . urlencode('Invalid email/ID or password.'));
  exit;
}

// Heuristic: if it looks like an email, normalize to lowercase.
// Otherwise treat as student_id as-is (your collation is case-insensitive anyway).
$isEmail = filter_var($handle, FILTER_VALIDATE_EMAIL);
$email   = $isEmail ? strtolower($handle) : null;
$studId  = $isEmail ? null : $handle;

try {
  if ($isEmail) {
    $stmt = $pdo->prepare('SELECT student_id, name, email, password FROM students WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
  } else {
    $stmt = $pdo->prepare('SELECT student_id, name, email, password FROM students WHERE student_id = ? LIMIT 1');
    $stmt->execute([$studId]);
  }

  $user = $stmt->fetch();
  if (!$user || !password_verify($pwd, $user['password'])) {
    header('Location: index.php?error=' . urlencode('Invalid email/ID or password.'));
    exit;
  }

  session_regenerate_id(true);
  $_SESSION['user'] = [
    'student_id' => $user['student_id'],
    'name'       => $user['name'],
    'email'      => $user['email'],
    'ts'         => time(),
  ];

  header('Location: ../users');
  exit;
} catch (PDOException $e) {
  // Optionally log $e->getMessage()
  http_response_code(500);
  exit('Server error. Please try again later.');
}
