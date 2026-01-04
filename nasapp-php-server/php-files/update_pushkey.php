<?php
/**
 * 更新用户 PushKey 接口
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    // 只支持POST请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => '只支持POST请求']);
        exit;
    }

    // 获取参数
    $userId = $_POST['userId'] ?? '';
    $pushKey = $_POST['pushKey'] ?? '';

    // 验证参数
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => '缺少用户ID']);
        exit;
    }

    // 验证用户是否存在
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => '用户不存在']);
        exit;
    }

    // 更新 PushKey
    $stmt = $db->prepare("UPDATE users SET pushKey = ? WHERE id = ?");
    $stmt->execute([$pushKey ?: null, $userId]);

    error_log("[" . date('Y-m-d H:i:s') . "] 用户 {$userId} 更新 PushKey: " . ($pushKey ?: '已清除'));

    echo json_encode([
        'success' => true,
        'message' => $pushKey ? 'PushKey 已更新' : 'PushKey 已清除'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('更新 PushKey 失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
}
?>