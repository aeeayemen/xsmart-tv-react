<?php
// backend/favorites.php
require_once 'db.php';

$action = $_GET['action'] ?? '';
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    jsonResponse(['error' => 'غير مصرح'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'add':
        try {
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, type, item_id, name, image_url) VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE name = VALUES(name), image_url = VALUES(image_url)");
            $stmt->execute([
                $user_id,
                $data['type'],
                $data['item_id'],
                $data['name'],
                $data['image_url']
            ]);
            jsonResponse(['success' => true]);
        } catch (PDOException $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
        break;

    case 'remove':
        try {
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND type = ? AND item_id = ?");
            $stmt->execute([$user_id, $data['type'], $data['item_id']]);
            jsonResponse(['success' => true]);
        } catch (PDOException $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
        break;

    case 'list':
        try {
            $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $favs = $stmt->fetchAll();
            jsonResponse($favs);
        } catch (PDOException $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'طلب غير صالح'], 400);
}
?>