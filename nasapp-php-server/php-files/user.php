<?php
/**
 * 获取用户信息接口
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$userId = $_GET['userId'] ?? '';

if (empty($userId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '缺少用户ID']);
    exit;
}

try {
    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => '用户不存在']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('获取用户信息失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '获取用户信息失败']);
}
?>