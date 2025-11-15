<?php
// /admin/books/update_book.php
require __DIR__ . '/../../includes/db.php';

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

$book_id    = field('book_id');     // current ISBN shown (kept read-only by UI)
$originalId = field('original_id'); // in case you later support changing IDs
$title      = field('title');
$author     = field('author');
$category   = field('category');

$errors = [];

// book_id validation
if ($book_id === '' || strlen($book_id) < 3 || strlen($book_id) > 64 || !preg_match('/^[0-9A-Za-z\- ]+$/', $book_id)) {
  $errors['book_id'] = 'Invalid ISBN/Book ID. Use letters, numbers, spaces, or hyphens (3–64 chars).';
}

if ($title === '' || strlen($title) > 256) {
  $errors['title'] = 'Please enter a valid title (max 256 chars).';
}
if ($author === '' || strlen($author) > 256) {
  $errors['author'] = 'Please enter a valid author (max 256 chars).';
}

$allowedCategories = ['Fiction', 'Non-Fiction', 'Computer Science', 'History'];
if ($category === '') {
  $errors['category'] = 'Please select a category.';
} elseif (!in_array($category, $allowedCategories, true)) {
  $errors['category'] = 'Invalid category selection.';
}

if ($errors) {
  json_out(['ok' => false, 'errors' => $errors], 422);
}

try {
  // Ensure record exists
  $check = $pdo->prepare('SELECT 1 FROM books WHERE book_id = ? LIMIT 1');
  $check->execute([$book_id]);
  if (!$check->fetch()) {
    json_out(['ok' => false, 'errors' => ['form' => 'Book not found.']], 404);
  }

  // Update
  $upd = $pdo->prepare('
    UPDATE books
       SET title = ?, author = ?, category = ?
     WHERE book_id = ?
    LIMIT 1
  ');
  $upd->execute([$title, $author, $category, $book_id]);

  json_out([
    'ok' => true,
    'book' => [
      'book_id'  => $book_id,
      'title'    => $title,
      'author'   => $author,
      'category' => $category
    ]
  ]);
} catch (PDOException $e) {
  json_out(['ok' => false, 'errors' => ['form' => 'Server error. Please try again.']], 500);
}
