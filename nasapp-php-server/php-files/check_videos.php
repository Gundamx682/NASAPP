<?php
/**
 * 检查用户222的视频数据
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$userId = 5; // 用户222的ID

echo "<h1>用户222的视频数据检查</h1>";

// 查询所有视频（包括已过期的）
$stmt = $db->prepare("
    SELECT id, userId, filename, size, uploadTime, expireTime, 
    datetime('now') as currentTime,
    CASE WHEN expireTime > datetime('now') THEN '未过期' ELSE '已过期' END as status
    FROM videos 
    WHERE userId = ?
    ORDER BY uploadTime DESC
");
$stmt->execute([$userId]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>找到 " . count($videos) . " 个视频记录</h2>";

if (count($videos) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>文件名</th><th>大小</th><th>上传时间</th><th>过期时间</th><th>当前时间</th><th>状态</th></tr>";
    
    foreach ($videos as $video) {
        echo "<tr>";
        echo "<td>{$video['id']}</td>";
        echo "<td>{$video['filename']}</td>";
        echo "<td>" . round($video['size'] / 1024 / 1024, 2) . " MB</td>";
        echo "<td>{$video['uploadTime']}</td>";
        echo "<td>{$video['expireTime']}</td>";
        echo "<td>{$video['currentTime']}</td>";
        echo "<td style='color:" . ($video['status'] == '未过期' ? 'green' : 'red') . "'>{$video['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>数据库中没有找到任何视频记录</p>";
}

// 检查uploads目录
echo "<h2>uploads/222/ 目录内容</h2>";
$userDir = UPLOAD_DIR . '/222';
if (is_dir($userDir)) {
    $files = glob($userDir . '/*.{mp4,avi,mov,mkv,webm,MP4,AVI,MOV,MKV,WEBM}', GLOB_BRACE);
    echo "<p>找到 " . count($files) . " 个视频文件</p>";
    echo "<ul>";
    foreach ($files as $file) {
        $size = filesize($file);
        $mtime = date('Y-m-d H:i:s', filemtime($file));
        echo "<li>" . basename($file) . " - " . round($size / 1024 / 1024, 2) . " MB - 修改时间: $mtime</li>";
    }
    echo "</ul>";
} else {
    echo "<p>目录不存在: $userDir</p>";
}

// 临时解决方案：更新过期时间
echo "<h2>解决方案</h2>";
echo "<p>如果视频已过期，可以更新过期时间：</p>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='extend'>";
echo "<input type='submit' value='延长所有视频过期时间1天'>";
echo "</form>";

if (isset($_POST['action']) && $_POST['action'] == 'extend') {
    $stmt = $db->prepare("UPDATE videos SET expireTime = datetime(expireTime, '+1 day') WHERE userId = ?");
    $stmt->execute([$userId]);
    $affected = $stmt->rowCount();
    echo "<p style='color:green'>✓ 已延长 $affected 个视频的过期时间</p>";
    echo "<p><a href='videos.php?userId=$userId'>重新查看视频列表</a></p>";
}
?>