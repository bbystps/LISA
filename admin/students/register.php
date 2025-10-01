<?php
// register.php  (JSON-only responses for AJAX modal)
require __DIR__ . '../../../includes/db.php';

// Avoid leaking notices/warnings into JSON
@ini_set('display_errors', '0');

function json_out(array $payload, int $code = 200): void
{
  // Clean any accidental output before emitting JSON
  if (function_exists('ob_get_length') && ob_get_length()) {
    ob_clean();
  }
  http_response_code($code);
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($payload);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_out(['ok' => false, 'errors' => ['form' => 'Method Not Allowed']], 405);
}

function field(string $k): string
{
  return trim($_POST[$k] ?? '');
}

$rfid_key   = field('rfid_key');
$student_id = field('student_id');
$name       = field('name');
$email      = strtolower(field('email'));
$course     = field('course');
$pwd        = field('password');
$pwd2       = field('password_confirm');

$errors = [];

/* -------------------- Validation -------------------- */
// RFID: hex only (allow user to type spaces/dashes; normalize later)
if ($rfid_key === '' || strlen($rfid_key) > 64 || !preg_match('/^[0-9A-Fa-f\- ]+$/', $rfid_key)) {
  $errors['rfid_key'] = 'Invalid RFID Key. Use hex characters only (e.g., 04AABBCCDDEE).';
} else {
  $rfid_key = strtoupper(str_replace([' ', '-'], '', $rfid_key)); // normalize
}

if ($student_id === '' || strlen($student_id) > 64) {
  $errors['student_id'] = 'Invalid Student ID.';
}
if ($name === '' || strlen($name) > 256) {
  $errors['name'] = 'Invalid name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 256) {
  $errors['email'] = 'Invalid email address.';
}
if ($course === '') {
  $errors['course'] = 'Please select a course.';
}
if (strlen($pwd) < 8 || strlen($pwd) > 256) {
  $errors['password'] = 'Password must be 8–256 characters.';
}
if ($pwd !== $pwd2) {
  $errors['password_confirm'] = 'Passwords do not match.';
}

if ($errors) {
  json_out(['ok' => false, 'errors' => $errors], 422);
}

/* -------------------- DB operations -------------------- */
try {
  // Duplicate checks
  $stmt = $pdo->prepare('SELECT 1 FROM students WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    json_out(['ok' => false, 'errors' => ['email' => 'Email is already registered.']], 409);
  }

  $stmt = $pdo->prepare('SELECT 1 FROM students WHERE student_id = ? LIMIT 1');
  $stmt->execute([$student_id]);
  if ($stmt->fetch()) {
    json_out(['ok' => false, 'errors' => ['student_id' => 'Student ID is already registered.']], 409);
  }

  $stmt = $pdo->prepare('SELECT 1 FROM students WHERE rfid_key = ? LIMIT 1');
  $stmt->execute([$rfid_key]);
  if ($stmt->fetch()) {
    json_out(['ok' => false, 'errors' => ['rfid_key' => 'RFID Key is already registered.']], 409);
  }

  // Insert
  $hash = password_hash($pwd, PASSWORD_DEFAULT);
  $ins = $pdo->prepare('
    INSERT INTO students (rfid_key, student_id, name, email, course, password)
    VALUES (?, ?, ?, ?, ?, ?)
  ');
  $ins->execute([$rfid_key, $student_id, $name, $email, $course, $hash]);

  json_out([
    'ok' => true,
    'student' => [
      'rfid_key'   => $rfid_key,
      'student_id' => $student_id,
      'name'       => $name,
      'email'      => $email,
      'course'     => $course
    ]
  ], 200);
} catch (PDOException $e) {
  // Optionally log: error_log($e->getMessage());
  // Handle unique index violations gracefully (SQLSTATE 23000)
  if ($e->getCode() === '23000') {
    json_out(['ok' => false, 'errors' => ['form' => 'Duplicate data. Please check your inputs.']], 409);
  }
  json_out(['ok' => false, 'errors' => ['form' => 'Server error. Please try again.']], 500);
}
