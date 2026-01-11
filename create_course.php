<?php
header('Content-Type: application/json; charset=utf-8');
// DEBUG MODE: remove detailed errors after fixing
$DEBUG = true;

$DB_HOST = '127.0.0.1';
$DB_NAME = 'educhain';
$DB_USER = 'root';
$DB_PASS = '';

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload', 'error' => json_last_error_msg()]);
    exit;
}

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$category = trim($input['category'] ?? '');
$instructor = trim($input['instructor'] ?? '');
$price = floatval($input['price'] ?? 0);
$thumbnail = trim($input['thumbnail'] ?? '📘');

if ($title === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $stmt = $pdo->prepare('
        INSERT INTO courses (title, description, category, instructor, price, thumbnail, enrolled, rating)
        VALUES (:title, :description, :category, :instructor, :price, :thumbnail, 0, 0)
    ');
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':category' => $category,
        ':instructor' => $instructor,
        ':price' => $price,
        ':thumbnail' => $thumbnail,
    ]);

    $id = (int)$pdo->lastInsertId();
    http_response_code(201);
    echo json_encode(['success' => true, 'id' => $id]);

} catch (PDOException $e) {
    error_log('create_course.php DB error: ' . $e->getMessage());
    http_response_code(500);
    $resp = ['success' => false, 'message' => 'Database error'];
    if ($DEBUG) { $resp['debug'] = $e->getMessage(); }
    echo json_encode($resp);
}
?>