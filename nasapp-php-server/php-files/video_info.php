<?php
/**
 * 获取视频详情接口
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$videoId = $_GET['videoId'] ?? '';

if (empty($videoId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '缺少视频ID']);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT id, userId, filename, size, uploadTime, expireTime, path
        FROM videos
        WHERE id = ?
    ");
    $stmt->execute([$videoId]);
    $video = $stmt->fetch();

    if (!$video) {
        echo json_encode(['success' => false, 'message' => '视频不存在']);
        exit;
    }

    $baseUrl = getBaseUrl();
    $result = [
        'id' => $video['id'],
        'url' => $baseUrl . '/video.php?id=' . $video['id'],
        'videoUrl' => $baseUrl . '/video.php?id=' . $video['id'],
        'fileName' => $video['filename'],
        'size' => (int)$video['size'],
        'fileSize' => (int)$video['size'],
        'uploadTime' => strtotime($video['uploadTime']) * 1000,
        'expireTime' => strtotime($video['expireTime']) * 1000,
        'remainingTime' => max(0, strtotime($video['expireTime']) - time()),
        'thumbnail' => $baseUrl . '/thumbnails/' . $video['id'] . '.jpg',
        'thumbnailUrl' => $baseUrl . '/thumbnails/' . $video['id'] . '.jpg'
    ];

    echo json_encode([
        'success' => true,
        'video' => $result
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('获取视频详情失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '获取视频详情失败']);
}
?>