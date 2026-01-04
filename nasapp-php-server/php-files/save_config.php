<?php
/**
 * 保存用户配置
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// 只允许 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => '只支持 POST 请求'
    ]);
    exit;
}

// 获取 POST 数据
$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'] ?? 0;
$pushKey = $data['pushKey'] ?? '';
$monitorDirectory = $data['monitorDirectory'] ?? '';
$autoUploadEnabled = isset($data['autoUploadEnabled']) ? ($data['autoUploadEnabled'] ? 1 : 0) : null;
$deviceId = $data['deviceId'] ?? '';
$licensePlate = $data['licensePlate'] ?? '';
$vinCode = $data['vinCode'] ?? '';
$qiniuAccessKey = $data['qiniuAccessKey'] ?? '';
$qiniuSecretKey = $data['qiniuSecretKey'] ?? '';
$qiniuBucket = $data['qiniuBucket'] ?? '';
$qiniuDomain = $data['qiniuDomain'] ?? '';

// 验证参数
if ($userId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => '用户ID无效'
    ]);
    exit;
}

try {
    // 更新用户配置
    $stmt = $db->prepare("
        UPDATE users 
        SET pushKey = COALESCE(NULLIF(?, ''), pushKey),
            monitorDirectory = COALESCE(NULLIF(?, ''), monitorDirectory),
            autoUploadEnabled = COALESCE(?, autoUploadEnabled),
            deviceId = COALESCE(NULLIF(?, ''), deviceId),
            licensePlate = COALESCE(NULLIF(?, ''), licensePlate),
            vinCode = COALESCE(NULLIF(?, ''), vinCode),
            qiniuAccessKey = COALESCE(NULLIF(?, ''), qiniuAccessKey),
            qiniuSecretKey = COALESCE(NULLIF(?, ''), qiniuSecretKey),
            qiniuBucket = COALESCE(NULLIF(?, ''), qiniuBucket),
            qiniuDomain = COALESCE(NULLIF(?, ''), qiniuDomain)
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $pushKey, 
        $monitorDirectory, 
        $autoUploadEnabled, 
        $deviceId, 
        $licensePlate, 
        $vinCode,
        $qiniuAccessKey,
        $qiniuSecretKey,
        $qiniuBucket,
        $qiniuDomain,
        $userId
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => '配置保存成功'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '配置保存失败'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '保存配置时出错: ' . $e->getMessage()
    ]);
}