<?php
/**
 * 上报视频信息接口（不上传文件）
 * 接收视频信息，保存到数据库，并发送推送通知
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    // 获取POST数据
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $userId = $data['userId'] ?? 0;
    $deviceId = $data['deviceId'] ?? '';
    $fileName = $data['fileName'] ?? '';
    $fileSize = $data['fileSize'] ?? 0;
    $timestamp = $data['timestamp'] ?? time() * 1000;

    error_log("收到视频上报: userId=$userId, fileName=$fileName, fileSize=$fileSize");

    // 验证用户
    $stmt = $db->prepare("SELECT id, username, pushKey, licensePlate, vinCode FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => '用户不存在']);
        exit;
    }

    $userPushKey = $user['pushKey'];
    $licensePlate = $user['licensePlate'] ?? '';
    $vinCode = $user['vinCode'] ?? '';

    // 检查是否已存在同名文件
    $stmt = $db->prepare("SELECT id FROM videos WHERE userId = ? AND originalName = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId, $fileName]);
    $existingVideo = $stmt->fetch();

    if ($existingVideo) {
        echo json_encode(['success' => true, 'message' => '文件已存在，跳过上报']);
        error_log("文件已存在，跳过上报: " . $fileName);
        exit;
    }

    // 计算时间
    $timestampSeconds = $timestamp / 1000;
    $uploadTime = date('Y-m-d H:i:s', $timestampSeconds);
    $expireTime = date('Y-m-d H:i:s', $timestampSeconds + VIDEO_RETENTION_TIME);

    // 创建视频记录（不上传文件）
    $filename = 'report_' . $timestamp . '_' . uniqid() . '.mp4';
    $filepath = UPLOAD_DIR . '/' . $user['username'] . '/' . $filename;

    $stmt = $db->prepare("
        INSERT INTO videos (userId, deviceId, originalName, filename, path, size, uploadTime, expireTime)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $deviceId,
        $fileName,
        $filename,
        $filepath,
        $fileSize,
        $uploadTime,
        $expireTime
    ]);

    $videoId = $db->lastInsertId();

    error_log("视频信息已保存: userId=$userId, videoId=$videoId, fileName=$fileName");

    // 发送推送通知
    if ($userPushKey) {
        $message = "🚨 哨兵模式预警\n\n" .
                   "检测到车辆异常\n\n" .
                   "车牌号: " . (!empty($licensePlate) ? $licensePlate : "未设置") . "\n" .
                   "车架号: " . (!empty($vinCode) ? $vinCode : "未设置") . "\n\n" .
                   "时间: {$uploadTime}\n" .
                   "文件名: {$fileName}\n" .
                   "文件大小: " . number_format($fileSize / 1024 / 1024, 2) . " MB\n\n" .
                   "熊哥和SS联合开发制作测试";

        $pushData = [
            'title' => '哨兵模式预警',
            'body' => '检测到车辆异常',
            'desp' => $message,
            'videoId' => $videoId,
            'videoUrl' => getBaseUrl() . '/video.php?id=' . $videoId
        ];

        sendPushNotification($userId, $pushData);
    }

    echo json_encode([
        'success' => true,
        'message' => '上报成功',
        'videoId' => $videoId
    ]);

} catch (Exception $e) {
    error_log("上报视频信息异常: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '服务器错误: ' . $e->getMessage()]);
}
?>