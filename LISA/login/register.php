<?php
// register.php
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

function field($key)
{
  return trim($_POST[$key] ?? '');
}

$student_id = field('student_id');
$name       = field('name');
$email      = strtolower(field('email'));
$course     = field('course');
$pwd        = field('password');
$pwd2       = field('password_confirm');
$agree      = isset($_POST['agree']);

$errors = [];

// Basic validation
if ($student_id === '' || strlen($student_id) > 64) {
  $errors[] = 'Invalid Student ID.';
}
if ($name === '' || strlen($name) > 256) {
  $errors[] = 'Invalid name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 256) {
  $errors[] = 'Invalid email address.';
}
if ($course === '') {
  $errors[] = 'Please select a course.';
}
if (strlen($pwd) < 8 || strlen($pwd) > 256) {
  $errors[] = 'Password must be 8–256 characters.';
}
if ($pwd !== $pwd2) {
  $errors[] = 'Passwords do not match.';
}
if (!$agree) {
  $errors[] = 'You must accept the Terms.';
}

if ($errors) {
  // Simple HTML response (could also return JSON for AJAX)
  http_response_code(422);
  echo '<h3>Registration Error</h3><ul>';
  foreach ($errors as $e) echo '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>';
  echo '</ul><p><a href="javascript:history.back()">Go back</a></p>';
  exit;
}

// Check duplicates
try {
  // Check email
  $stmt = $pdo->prepare('SELECT 1 FROM students WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    http_response_code(409);
    exit('<h3>Registration Error</h3><p>Email is already registered.</p><p><a href="javascript:history.back()">Go back</a></p>');
  }

  // Check student_id
  $stmt = $pdo->prepare('SELECT 1 FROM students WHERE student_id = ? LIMIT 1');
  $stmt->execute([$student_id]);
  if ($stmt->fetch()) {
    http_response_code(409);
    exit('<h3>Registration Error</h3><p>Student ID is already registered.</p><p><a href="javascript:history.back()">Go back</a></p>');
  }

  // Hash password
  $hash = password_hash($pwd, PASSWORD_DEFAULT);

  // Insert
  $ins = $pdo->prepare('
    INSERT INTO students (student_id, name, email, course, password)
    VALUES (?, ?, ?, ?, ?)
  ');
  $ins->execute([$student_id, $name, $email, $course, $hash]);

  // Success (redirect to login or show success)
  // header('Location: /login'); exit;
  echo '<h3>Registration Successful</h3><p>You can now <a href="/login">sign in</a>.</p>';
} catch (PDOException $e) {
  // Optional: log $e->getMessage()
  http_response_code(500);
  exit('<h3>Server Error</h3><p>Please try again later.</p>');
}
