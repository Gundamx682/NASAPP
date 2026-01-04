<?php
/**
 * 视频播放接口
 */

require_once 'config.php';

// 设置CORS头
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Range, Authorization');

// 处理OPTIONS请求（CORS预检）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit;
}

$videoId = $_GET['id'] ?? '';

if (empty($videoId)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => '缺少视频ID']));
}

try {
    // 从数据库获取视频信息
    $stmt = $db->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$videoId]);
    $video = $stmt->fetch();

    if (!$video) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => '视频不存在']));
    }

    // 检查是否过期
    if (strtotime($video['expireTime']) < time()) {
        // 返回HTML错误页面，而不是JSON
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(410);
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>视频已过期</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #f44336;
            margin-top: 0;
        }
        p {
            color: #666;
            font-size: 16px;
        }
        .icon {
            font-size: 60px;
            color: #f44336;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">⚠️</div>
        <h1>视频已过期</h1>
        <p>该视频已超过保留时间（7天），已被自动删除。</p>
        <p>请上传新的视频进行测试。</p>
    </div>
</body>
</html>';
        exit;
    }

    // 检查文件是否存在
    if (!file_exists($video['path'])) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => '视频文件不存在']));
    }

    // 获取文件大小
    $fileSize = filesize($video['path']);

    // 获取文件扩展名以确定Content-Type
    $extension = strtolower(pathinfo($video['filename'], PATHINFO_EXTENSION));
    $contentType = 'video/mp4';

    switch ($extension) {
        case 'mp4':
            $contentType = 'video/mp4';
            break;
        case 'avi':
            $contentType = 'video/x-msvideo';
            break;
        case 'mov':
            $contentType = 'video/quicktime';
            break;
        case 'mkv':
            $contentType = 'video/x-matroska';
            break;
        case 'webm':
            $contentType = 'video/webm';
            break;
        case '3gp':
            $contentType = 'video/3gpp';
            break;
        case 'flv':
            $contentType = 'video/x-flv';
            break;
    }

    // 设置通用响应头
    header('Accept-Ranges: bytes');
    header('Cache-Control: public, max-age=31536000');
    header('Content-Disposition: attachment; filename="' . $video['originalName'] . '"');

    // 支持断点续传
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        $rangeParts = explode('-', substr($range, 6));
        $start = intval($rangeParts[0]);
        $end = isset($rangeParts[1]) ? intval($rangeParts[1]) : $fileSize - 1;
        $chunkSize = ($end - $start) + 1;

        header('HTTP/1.1 206 Partial Content');
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Content-Length: ' . $chunkSize);
        header('Content-Type: ' . $contentType);

        $file = fopen($video['path'], 'rb');
        fseek($file, $start);
        echo fread($file, $chunkSize);
        fclose($file);
    } else {
        header('Content-Length: ' . $fileSize);
        header('Content-Type: ' . $contentType);

        readfile($video['path']);
    }

} catch (Exception $e) {
    error_log('播放失败: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '播放失败']);
}
?>