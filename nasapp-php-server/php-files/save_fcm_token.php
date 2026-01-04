<?php
/**
 * 保存FCM Token接口
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 处理OPTIONS请求（CORS预检）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit;
}

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, '只支持POST请求');
}

// 获取JSON数据
$input = json_decode(file_get_contents('php://input'), true);

$userId = $input['userId'] ?? '';
$fcmToken = $input['fcmToken'] ?? '';

if (empty($userId) || empty($fcmToken)) {
    jsonResponse(false, '缺少必要参数: userId和fcmToken');
}

try {
    // 更新用户的FCM Token
    $stmt = $db->prepare("UPDATE users SET fcmToken = ? WHERE id = ?");
    $stmt->execute([$fcmToken, $userId]);
    
    if ($stmt->rowCount() > 0) {
        error_log("FCM Token已保存: userId=$userId, token=$fcmToken");
        jsonResponse(true, 'FCM Token保存成功');
    } else {
        jsonResponse(false, '用户不存在');
    }
    
} catch (Exception $e) {
    error_log('保存FCM Token失败: ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, '保存FCM Token失败');
}
?>