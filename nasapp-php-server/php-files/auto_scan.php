<?php
/**
 * 自动扫描并注册现有视频（用于定时任务）
 */

require_once 'config.php';

// 获取所有用户文件夹
$users = $db->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);

$registeredCount = 0;

foreach ($users as $user) {
    $userId = $user['id'];
    $username = $user['username'];
    $userDir = UPLOAD_DIR . '/' . $username;
    
    if (!is_dir($userDir)) {
        continue;
    }
    
    // 获取所有视频文件
    $videoFiles = glob($userDir . '/*.{mp4,avi,mov,mkv,webm,MP4,AVI,MOV,MKV,WEBM}', GLOB_BRACE);
    
    foreach ($videoFiles as $videoFile) {
        $fileName = basename($videoFile);
        $fileSize = filesize($videoFile);
        
        // 检查是否已注册
        $stmt = $db->prepare("SELECT id FROM videos WHERE userId = ? AND filename = ?");
        $stmt->execute([$userId, $fileName]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            // 注册视频
            $uploadTime = date('Y-m-d H:i:s', filemtime($videoFile));
            $expireTime = date('Y-m-d H:i:s', filemtime($videoFile) + VIDEO_RETENTION_TIME);
            $deviceId = 'auto_scan';
            
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
            
            $registeredCount++;
        }
    }
}

// 记录日志
error_log("[" . date('Y-m-d H:i:s') . "] 自动扫描完成，注册了 $registeredCount 个视频");
?>