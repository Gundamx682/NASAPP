<?php
/**
 * 用户注册接口
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
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $pushKey = $_POST['pushKey'] ?? '';
    
    // 验证用户名（只允许英文和数字）
    if (empty($username) || !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => '用户名只能使用英文和数字']);
        exit;
    }
    
    // 验证用户名长度（3-20个字符）
    if (strlen($username) < 3 || strlen($username) > 20) {
        echo json_encode(['success' => false, 'message' => '用户名长度必须在3-20个字符之间']);
        exit;
    }
    
    // 验证密码（至少6个字符）
    if (empty($password) || strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => '密码至少需要6个字符']);
        exit;
    }
    
    // 验证邮箱格式（如果提供）
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
        exit;
    }
    
    // 检查用户名是否已存在
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo json_encode(['success' => false, 'message' => '用户名已存在']);
        exit;
    }
    
    // 创建新用户
    // 生成或使用用户提供的 PushKey
    $userPushKey = $pushKey ?: 'sentinel_' . $username . '_' . time();

    $stmt = $db->prepare("
        INSERT INTO users (username, password, email, pushKey)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$username, $password, $email ?: null, $userPushKey]);

    $userId = $db->lastInsertId();
    
    // 创建用户文件夹
    $userDir = UPLOAD_DIR . '/' . $username;
    if (!file_exists($userDir)) {
        if (!mkdir($userDir, 0777, true)) {
            error_log("[" . date('Y-m-d H:i:s') . "] 创建用户文件夹失败: $userDir");
            // 即使创建文件夹失败，也允许注册成功，但记录错误
        } else {
            error_log("[" . date('Y-m-d H:i:s') . "] 创建用户文件夹成功: $userDir");
        }
    }
    
    error_log("[" . date('Y-m-d H:i:s') . "] 新用户注册: username=$username, userId=$userId, pushKey=$userPushKey");

    echo json_encode([
        'success' => true,
        'message' => '注册成功，请保存您的 PushKey 用于接收通知',
        'userId' => $userId,
        'pushKey' => $userPushKey
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('注册失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '注册失败: ' . $e->getMessage()]);
}
?>
