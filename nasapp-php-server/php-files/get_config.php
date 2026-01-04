<?php
/**
 * 获取用户配置
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// 获取用户ID
$userId = $_GET['userId'] ?? 0;

// 验证参数
if ($userId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => '用户ID无效'
    ]);
    exit;
}

try {
    // 获取用户配置
    $stmt = $db->prepare("SELECT id, username, pushKey, monitorDirectory, autoUploadEnabled, deviceId, licensePlate, vinCode, qiniuAccessKey, qiniuSecretKey, qiniuBucket, qiniuDomain FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
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
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '用户不存在'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '获取配置时出错: ' . $e->getMessage()
    ]);
}