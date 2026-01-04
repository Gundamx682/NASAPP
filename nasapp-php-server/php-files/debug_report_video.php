<?php
/**
 * 调试 report_video.php 接口
 */

require_once 'config.php';

echo "=== 调试视频上报接口 ===\n\n";

// 测试数据
$testData = [
    'userId' => 1,
    'deviceId' => 'TestDevice',
    'fileName' => 'test_video_' . time() . '.mp4',
    'fileSize' => 10485760,
    'timestamp' => time() * 1000
];

echo "测试数据:\n";
print_r($testData);
echo "\n";

try {
    // 验证用户
    $stmt = $db->prepare("SELECT id, username, pushKey, licensePlate, vinCode FROM users WHERE id = ?");
    $stmt->execute([$testData['userId']]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "❌ 用户不存在: userId={$testData['userId']}\n";
        exit;
    }

    echo "✅ 用户验证成功:\n";
    echo "- ID: {$user['id']}\n";
    echo "- 用户名: {$user['username']}\n";
    echo "- PushKey: " . (empty($user['pushKey']) ? "❌ 未配置" : "✅ 已配置") . "\n";
    echo "- 车牌号: " . (empty($user['licensePlate']) ? "❌ 未设置" : "✅ " . $user['licensePlate']) . "\n";
    echo "- 车架号: " . (empty($user['vinCode']) ? "❌ 未设置" : "✅ " . $user['vinCode']) . "\n\n";

    // 检查是否已存在同名文件
    $stmt = $db->prepare("SELECT id FROM videos WHERE userId = ? AND originalName = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$testData['userId'], $testData['fileName']]);
    $existingVideo = $stmt->fetch();

    if ($existingVideo) {
        echo "⚠️  文件已存在，跳过测试\n";
        exit;
    }

    // 计算时间
    $timestampSeconds = $testData['timestamp'] / 1000;
    $uploadTime = date('Y-m-d H:i:s', $timestampSeconds);
    $expireTime = date('Y-m-d H:i:s', $timestampSeconds + VIDEO_RETENTION_TIME);

    echo "时间信息:\n";
    echo "- 时间戳: {$testData['timestamp']}\n";
    echo "- 上传时间: $uploadTime\n";
    echo "- 过期时间: $expireTime\n\n";

    // 创建视频记录
    $filename = 'report_' . $testData['timestamp'] . '_' . uniqid() . '.mp4';
    $filepath = UPLOAD_DIR . '/' . $user['username'] . '/' . $filename;

    echo "准备插入数据库:\n";
    echo "- userId: {$testData['userId']}\n";
    echo "- deviceId: {$testData['deviceId']}\n";
    echo "- fileName: {$testData['fileName']}\n";
    echo "- filename: $filename\n";
    echo "- filepath: $filepath\n";
    echo "- fileSize: {$testData['fileSize']}\n";
    echo "- uploadTime: $uploadTime\n";
    echo "- expireTime: $expireTime\n\n";

    $stmt = $db->prepare("
        INSERT INTO videos (userId, deviceId, originalName, filename, path, size, uploadTime, expireTime)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $result = $stmt->execute([
        $testData['userId'],
        $testData['deviceId'],
        $testData['fileName'],
        $filename,
        $filepath,
        $testData['fileSize'],
        $uploadTime,
        $expireTime
    ]);

    if ($result) {
        $videoId = $db->lastInsertId();
        echo "✅ 插入成功，videoId: $videoId\n\n";

        // 测试推送
        if ($user['pushKey']) {
            echo "测试推送通知...\n";
            $licensePlate = $user['licensePlate'] ?? '';
            $vinCode = $user['vinCode'] ?? '';
            
            $message = "🚨 哨兵模式预警\n\n" .
                       "检测到车辆异常\n\n" .
                       "车牌号: " . (!empty($licensePlate) ? $licensePlate : "未设置") . "\n" .
                       "车架号: " . (!empty($vinCode) ? $vinCode : "未设置") . "\n\n" .
                       "时间: $uploadTime\n" .
                       "文件名: {$testData['fileName']}\n" .
                       "文件大小: " . number_format($testData['fileSize'] / 1024 / 1024, 2) . " MB\n\n" .
                       "熊哥和SS联合开发制作测试\n\n" .
                       "点击查看: " . getBaseUrl() . '/video.php?id=' . $videoId;

            $pushData = [
                'title' => '哨兵模式预警',
                'body' => '检测到车辆异常',
                'desp' => $message,
                'videoId' => $videoId,
                'videoUrl' => getBaseUrl() . '/video.php?id=' . $videoId
            ];

            $pushResult = sendPushNotification($testData['userId'], $pushData);
            if ($pushResult) {
                echo "✅ 推送发送成功\n";
            } else {
                echo "❌ 推送发送失败\n";
            }
        } else {
            echo "⚠️  PushKey 未配置，跳过推送\n";
        }

    } else {
        echo "❌ 插入失败\n";
        print_r($stmt->errorInfo());
    }

} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== 调试完成 ===\n";
?>