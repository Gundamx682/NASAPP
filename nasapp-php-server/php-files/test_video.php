<?php
/**
 * 视频播放测试脚本
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>视频播放测试</h1>";

try {
    // 获取最新的视频
    $stmt = $db->prepare("SELECT id, originalName, filename, path FROM videos ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $video = $stmt->fetch();

    if (!$video) {
        echo "<p>没有找到视频</p>";
        echo "<p><a href='login.php'>请先上传视频</a></p>";
        exit;
    }

    echo "<h2>视频信息</h2>";
    echo "<ul>";
    echo "<li>ID: " . $video['id'] . "</li>";
    echo "<li>原始文件名: " . htmlspecialchars($video['originalName']) . "</li>";
    echo "<li>保存文件名: " . htmlspecialchars($video['filename']) . "</li>";
    echo "<li>文件路径: " . htmlspecialchars($video['path']) . "</li>";
    echo "</ul>";

    // 检查文件是否存在
    if (file_exists($video['path'])) {
        echo "<p style='color: green;'>✓ 文件存在</p>";
        echo "<p>文件大小: " . formatBytes(filesize($video['path'])) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ 文件不存在</p>";
    }

    echo "<h2>测试链接</h2>";
    $baseUrl = getBaseUrl();
    $videoUrl = $baseUrl . '/video.php?id=' . $video['id'];

    echo "<p>直接访问: <a href='$videoUrl' target='_blank'>$videoUrl</a></p>";
    echo "<p>API访问: <a href='{$baseUrl}/api/video/{$video['id']}' target='_blank'>{$baseUrl}/api/video/{$video['id']}</a></p>";

    echo "<h2>视频播放器测试</h2>";
    echo "<video controls width='640'>";
    echo "<source src='$videoUrl' type='video/mp4'>";
    echo "您的浏览器不支持视频播放。";
    echo "</video>";

    echo "<h2>CORS测试</h2>";
    echo "<p>请在浏览器控制台运行以下代码测试CORS：</p>";
    echo "<pre>";
    echo "fetch('$videoUrl')\n";
    echo "  .then(response => response.blob())\n";
    echo "  .then(blob => console.log('CORS测试成功，文件大小:', blob.size))\n";
    echo "  .catch(error => console.error('CORS测试失败:', error));";
    echo "</pre>";

} catch (Exception $e) {
    echo "<p style='color: red;'>错误: " . htmlspecialchars($e->getMessage()) . "</p>";
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>