<?php
// backend/login.php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    jsonResponse(['error' => 'يرجى إدخال اسم المستخدم وكلمة المرور'], 400);
}

$username = trim($data['username']);
$password = $data['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Authenticated
        unset($user['password']); // Don't send password back
        jsonResponse([
            'success' => true,
            'user_info' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'exp_date' => $user['exp_date'],
                'auth' => 1
            ]
        ]);
    } else {
        jsonResponse(['error' => 'خطأ في اسم المستخدم أو كلمة المرور'], 401);
    }
} catch (PDOException $e) {
    jsonResponse(['error' => 'حدث خطأ في الخادم'], 500);
}
?>