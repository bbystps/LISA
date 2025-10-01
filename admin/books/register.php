<?php
// /admin/books/register.php  (JSON-only responses for AJAX modal)
require __DIR__ . '/../../includes/db.php'; // must create $pdo (PDO)

// Avoid leaking notices/warnings into JSON
@ini_set('display_errors', '0');

function json_out(array $payload, int $code = 200): void
{
  if (function_exists('ob_get_length') && ob_get_length()) {
    ob_clean();
  }
  http_response_code($code);
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_out(['ok' => false, 'errors' => ['form' => 'Method Not Allowed']], 405);
}

function field(string $k): string
{
  return trim($_POST[$k] ?? '');
}

// ---------- Read fields ----------
$book_id  = field('book_id');     // used as “ISBN” column in table
$title    = field('title');
$author   = field('author');
$category = field('category');

// ---------- Validate ----------
$errors = [];

// book_id: allow letters/numbers/hyphen/spaces, 3..64 chars
if ($book_id === '' || strlen($book_id) < 3 || strlen($book_id) > 64 || !preg_match('/^[0-9A-Za-z\- ]+$/', $book_id)) {
  $errors['book_id'] = 'Invalid ISBN/Book ID. Use letters, numbers, spaces, or hyphens (3–64 chars).';
}

// title & author: 1..256
if ($title === '' || strlen($title) > 256) {
  $errors['title'] = 'Please enter a valid title (max 256 chars).';
}
if ($author === '' || strlen($author) > 256) {
  $errors['author'] = 'Please enter a valid author (max 256 chars).';
}

// category: non-empty; optionally whitelist known options from the modal
$allowedCategories = ['Fiction', 'Non-Fiction', 'Computer Science', 'History'];
if ($category === '') {
  $errors['category'] = 'Please select a category.';
} elseif (!in_array($category, $allowedCategories, true)) {
  // If you want to allow any free-text category, remove this branch.
  $errors['category'] = 'Invalid category selection.';
}

if ($errors) {
  json_out(['ok' => false, 'errors' => $errors], 422);
}

// ---------- DB ops ----------
try {
  // Duplicate check (by primary key/unique constraint on book_id)
  $stmt = $pdo->prepare('SELECT 1 FROM books WHERE book_id = ? LIMIT 1');
  $stmt->execute([$book_id]);
  if ($stmt->fetch()) {
    json_out(['ok' => false, 'errors' => ['book_id' => 'This ISBN/Book ID already exists.']], 409);
  }

  // Insert (status defaults to 'Available' per schema, but we can also set explicitly)
  $ins = $pdo->prepare('
    INSERT INTO books (book_id, title, author, category, status)
    VALUES (?, ?, ?, ?, "Available")
  ');
  $ins->execute([$book_id, $title, $author, $category]);

  json_out([
    'ok' => true,
    'book' => [
      'book_id'  => $book_id,
      'title'    => $title,
      'author'   => $author,
      'category' => $category,
      'status'   => 'Available'
    ]
  ], 200);
} catch (PDOException $e) {
  // error_log($e->getMessage()); // uncomment for server logs
  if ($e->getCode() === '23000') {
    // Unique/constraint violation
    json_out(['ok' => false, 'errors' => ['form' => 'Duplicate data. Please check your inputs.']], 409);
  }
  json_out(['ok' => false, 'errors' => ['form' => 'Server error. Please try again.']], 500);
}
