<?php
/**
 * 用户登录接口
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
    
    // 验证参数
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '用户名和密码不能为空']);
        exit;
    }
    
    // 查询用户
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
        exit;
    }
    
    // 验证密码（实际应该使用password_verify）
    if ($user['password'] !== $password) {
        echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
        exit;
    }
    
    // 更新最后登录时间
    $stmt = $db->prepare("UPDATE users SET lastLogin = datetime('now') WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    error_log("[" . date('Y-m-d H:i:s') . "] 用户登录: username=$username, userId={$user['id']}");
    
    echo json_encode([
        'success' => true,
        'message' => '登录成功',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'pushKey' => $user['pushKey'] ?? '',
            'monitorDirectory' => $user['monitorDirectory'] ?? '',
            'autoUploadEnabled' => ($user['autoUploadEnabled'] ?? 0) == 1,
            'deviceId' => $user['deviceId'] ?? '',
            'licensePlate' => $user['licensePlate'] ?? '',
            'vinCode' => $user['vinCode'] ?? '',
            'qiniuAccessKey' => $user['qiniuAccessKey'] ?? '',
            'qiniuSecretKey' => $user['qiniuSecretKey'] ?? '',
            'qiniuBucket' => $user['qiniuBucket'] ?? '',
            'qiniuDomain' => $user['qiniuDomain'] ?? ''
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('登录失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '登录失败: ' . $e->getMessage()]);
}
?>