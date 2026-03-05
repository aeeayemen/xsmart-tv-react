<?php
// backend/register.php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    jsonResponse(['error' => 'البيانات غير مكتملة'], 400);
}

$username = trim($data['username']);
$email = trim($data['email'] ?? '');
$password = password_hash($data['password'], PASSWORD_DEFAULT);
// Default 1 month subscription for new users
$exp_date = time() + (30 * 24 * 60 * 60);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, exp_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $password, $exp_date]);

    jsonResponse(['message' => 'تم إنشاء الحساب بنجاح', 'success' => true]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Unique constraint
        jsonResponse(['error' => 'اسم المستخدم موجود مسبقاً'], 409);
    }
    jsonResponse(['error' => 'فشل في إنشاء الحساب: ' . $e->getMessage()], 500);
}
?>