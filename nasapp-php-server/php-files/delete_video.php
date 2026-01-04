<?php
/**
 * 删除视频接口
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 处理OPTIONS请求（CORS预检）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    exit;
}

// 只支持DELETE请求
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只支持DELETE请求']);
    exit;
}

// 从URL路径获取videoId
$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$videoId = end($parts);

if (empty($videoId) || !is_numeric($videoId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '无效的视频ID']);
    exit;
}

try {
    // 查询视频信息
    $stmt = $db->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$videoId]);
    $video = $stmt->fetch();

    if (!$video) {
        echo json_encode(['success' => false, 'message' => '视频不存在']);
        exit;
    }

    // 删除文件
    if (file_exists($video['path'])) {
        if (!unlink($video['path'])) {
            error_log("删除文件失败: {$video['path']}");
        }
    }

    // 删除缩略图
    $thumbnailPath = THUMBNAIL_DIR . '/' . $videoId . '.jpg';
    if (file_exists($thumbnailPath)) {
        unlink($thumbnailPath);
    }

    // 删除数据库记录
    $stmt = $db->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$videoId]);

    error_log("视频删除成功: videoId=$videoId");

    echo json_encode([
        'success' => true,
        'message' => '删除成功'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('删除视频失败: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '删除失败']);
}
?>