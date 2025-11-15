<?php
// update_student.php
require __DIR__ . '../../../includes/db.php';
@ini_set('display_errors', '0');

function json_out(array $payload, int $code = 200): void
{
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

$original_id = field('original_student_id');   // from modal hidden
$rfid_key    = strtoupper(str_replace([' ', '-'], '', field('rfid_key')));
$student_id  = field('student_id');           // currently read-only; same as original
$name        = field('name');
$email       = strtolower(field('email'));
$course      = field('course');
$pwd         = field('password');             // may be empty (no change)
$pwd2        = field('password_confirm');

$errors = [];

if ($original_id === '')                      $errors['form'] = 'Missing original student id.';
if ($rfid_key === '' || strlen($rfid_key) > 64 || !preg_match('/^[0-9A-F]+$/', $rfid_key)) $errors['rfid_key'] = 'Invalid RFID Key.';
if ($student_id === '' || strlen($student_id) > 64) $errors['student_id'] = 'Invalid Student ID.';
if ($name === '' || strlen($name) > 256)      $errors['name'] = 'Invalid name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 256) $errors['email'] = 'Invalid email address.';
if ($course === '')                           $errors['course'] = 'Please select a course.';
if ($pwd !== '' || $pwd2 !== '') {
  if (strlen($pwd) < 8 || strlen($pwd) > 256) $errors['password'] = 'Password must be 8–256 characters.';
  if ($pwd !== $pwd2)                         $errors['password_confirm'] = 'Passwords do not match.';
}

if ($errors) json_out(['ok' => false, 'errors' => $errors], 422);

try {
  // Ensure target exists
  $stmt = $pdo->prepare('SELECT student_id FROM students WHERE student_id = ? LIMIT 1');
  $stmt->execute([$original_id]);
  if (!$stmt->fetch()) json_out(['ok' => false, 'errors' => ['form' => 'Student not found.']], 404);

  // Uniqueness checks (exclude this student)
  $stmt = $pdo->prepare('SELECT 1 FROM students WHERE email = ? AND student_id <> ? LIMIT 1');
  $stmt->execute([$email, $original_id]);
  if ($stmt->fetch()) json_out(['ok' => false, 'errors' => ['email' => 'Email is already used.']], 409);

  $stmt = $pdo->prepare('SELECT 1 FROM students WHERE rfid_key = ? AND student_id <> ? LIMIT 1');
  $stmt->execute([$rfid_key, $original_id]);
  if ($stmt->fetch()) json_out(['ok' => false, 'errors' => ['rfid_key' => 'RFID Key is already used.']], 409);

  // Build update
  if ($pwd !== '') {
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    $sql = 'UPDATE students SET rfid_key=?, name=?, email=?, course=?, password=? WHERE student_id=?';
    $params = [$rfid_key, $name, $email, $course, $hash, $original_id];
  } else {
    $sql = 'UPDATE students SET rfid_key=?, name=?, email=?, course=? WHERE student_id=?';
    $params = [$rfid_key, $name, $email, $course, $original_id];
  }

  $upd = $pdo->prepare($sql);
  $upd->execute($params);

  json_out(['ok' => true]);
} catch (PDOException $e) {
  if ($e->getCode() === '23000') {
    json_out(['ok' => false, 'errors' => ['form' => 'Duplicate data.']], 409);
  }
  json_out(['ok' => false, 'errors' => ['form' => 'Server error.']], 500);
}
