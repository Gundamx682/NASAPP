<?php
/**
 * 默认首页
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => '哨兵模式视频监控API服务器',
    'version' => '1.0.0',
    'endpoints' => [
        'health' => '/health.php',
        'register' => '/register.php',
        'login' => '/login.php',
        'upload' => '/upload.php',
        'videos' => '/videos.php?userId={userId}',
        'videoInfo' => '/video_info.php?videoId={videoId}',
        'deleteVideo' => '/delete_video.php'
    ]
], JSON_UNESCAPED_UNICODE);
?>