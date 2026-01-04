<?php
/**
 * 视频列表接口
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$userId = $_GET['userId'] ?? '';

if (empty($userId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '缺少用户ID']);
    exit;
}

try {
    // 查询未过期的视频
    $stmt = $db->prepare("
        SELECT id, originalName, filename, path, size, uploadTime, expireTime 
        FROM videos 
        WHERE userId = ? AND expireTime > datetime('now')
        ORDER BY uploadTime DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $videos = $stmt->fetchAll();
    
    // 七牛云代理服务器配置（国外服务器）
    $proxyServer = 'http://45.130.146.21/qiniu_proxy_server.php';
    
    $result = [];
    foreach ($videos as $video) {
        $baseUrl = getBaseUrl();
        
        // 检查是否是七牛云视频（path 以 https:// 开头）
        if (strpos($video['path'], 'https://') === 0) {
            // 七牛云视频，使用国外服务器代理
            $videoUrl = $proxyServer . '?videoId=' . $video['id'];
            $thumbnailUrl = null; // 七牛云视频暂不支持缩略图
        } else {
            // NAS服务器视频，使用服务器URL
            $videoUrl = $baseUrl . '/video.php?id=' . $video['id'];
            $thumbnailUrl = $baseUrl . '/thumbnails/' . $video['id'] . '.jpg';
        }
        
        $result[] = [
            'id' => $video['id'],
            'url' => $videoUrl,
            'videoUrl' => $videoUrl,
            'thumbnail' => $thumbnailUrl,
            'thumbnailUrl' => $thumbnailUrl,
            'fileName' => $video['originalName'],  // 使用原始文件名
            'size' => (int)$video['size'],
            'fileSize' => (int)$video['size'],
            'uploadTime' => strtotime($video['uploadTime']) * 1000,
            'expireTime' => strtotime($video['expireTime']) * 1000,
            'remainingTime' => max(0, strtotime($video['expireTime']) - time())
        ];
    }    
    echo json_encode([
        'success' => true,
        'videos' => $result,
        'total' => count($result)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('查询失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '查询失败']);
}
?>