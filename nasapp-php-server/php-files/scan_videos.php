<?php
/**
 * 扫描并注册现有视频
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>扫描并注册现有视频</h1>";

// 获取所有用户文件夹
$users = $db->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>找到 " . count($users) . " 个用户</h2>";

$registeredCount = 0;

foreach ($users as $user) {
    $userId = $user['id'];
    $username = $user['username'];
    $userDir = UPLOAD_DIR . '/' . $username;
    
    echo "<h3>用户: $username (ID: $userId)</h3>";
    
    if (!is_dir($userDir)) {
        echo "<p>目录不存在: $userDir</p>";
        continue;
    }
    
    // 获取所有视频文件
    $videoFiles = glob($userDir . '/*.{mp4,avi,mov,mkv,webm,MP4,AVI,MOV,MKV,WEBM}', GLOB_BRACE);
    
    echo "<p>找到 " . count($videoFiles) . " 个视频文件</p>";
    
    foreach ($videoFiles as $videoFile) {
        $fileName = basename($videoFile);
        $fileSize = filesize($videoFile);
        
        // 检查是否已注册
        $stmt = $db->prepare("SELECT id FROM videos WHERE userId = ? AND filename = ?");
        $stmt->execute([$userId, $fileName]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo "<p>✓ 已注册: $fileName</p>";
        } else {
            // 注册视频
            $uploadTime = date('Y-m-d H:i:s', filemtime($videoFile));
            $expireTime = date('Y-m-d H:i:s', filemtime($videoFile) + VIDEO_RETENTION_TIME);
            $deviceId = 'manual_upload';
            
            $stmt = $db->prepare("
                INSERT INTO videos (userId, deviceId, originalName, filename, path, size, uploadTime, expireTime)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $deviceId,
                $fileName,
                $fileName,
                $videoFile,
                $fileSize,
                $uploadTime,
                $expireTime
            ]);
            
            echo "<p style='color:green'>✓ 注册成功: $fileName</p>";
            $registeredCount++;
        }
    }
}

echo "<hr>";
echo "<h2>总结</h2>";
echo "<p>成功注册了 $registeredCount 个视频</p>";
echo "<p><a href='videos.php?userId=2'>查看用户222的视频</a></p>";
?>