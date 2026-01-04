<?php
/**
 * 检查最新的视频记录
 */
header('Content-Type: text/html; charset=utf-8');

require_once 'config.php';

echo "<h1>最新视频记录检查</h1>";

// 检查用户14的最近5条视频记录
echo "<h2>用户14的最近5条视频记录</h2>";
$stmt = $db->prepare("
    SELECT id, userId, deviceId, originalName, filename, path, size, uploadTime
    FROM videos
    WHERE userId = 14
    ORDER BY uploadTime DESC
    LIMIT 5
");
$stmt->execute();
$videos = $stmt->fetchAll();

if ($videos) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>文件名</th><th>路径</th><th>存储类型</th><th>大小</th><th>上传时间</th></tr>";

    foreach ($videos as $video) {
        $path = $video['path'];
        $isQiniuUrl = strpos($path, 'clouddn.com') !== false;
        $storageType = $isQiniuUrl ? '七牛云' : 'NAS服务器';
        $pathColor = $isQiniuUrl ? 'green' : 'red';

        echo "<tr>";
        echo "<td><strong>{$video['id']}</strong></td>";
        echo "<td>{$video['originalName']}</td>";
        echo "<td><span style='color:$pathColor'>$path</span></td>";
        echo "<td><strong>$storageType</strong></td>";
        echo "<td>" . round($video['size'] / 1024 / 1024, 2) . " MB</td>";
        echo "<td>{$video['uploadTime']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 检查最新的视频ID 446
    $latestVideo = $videos[0];
    if ($latestVideo['id'] == 446) {
        echo "<h3>最新视频（ID: 446）详情</h3>";
        echo "<p>文件名: {$latestVideo['originalName']}</p>";
        echo "<p>路径: {$latestVideo['path']}</p>";
        echo "<p>大小: " . round($latestVideo['size'] / 1024 / 1024, 2) . " MB</p>";
        echo "<p>上传时间: {$latestVideo['uploadTime']}</p>";

        if (strpos($latestVideo['path'], 'clouddn.com') !== false) {
            echo "<p style='color:green; font-size:18px;'><strong>✅ 视频已上传到七牛云</strong></p>";
        } else {
            echo "<p style='color:red; font-size:18px;'><strong>❌ 视频上传到了NAS服务器</strong></p>";
        }
    }
} else {
    echo "<p>没有找到视频记录</p>";
}

echo "<hr>";
echo "<h2>问题排查</h2>";
echo "<p>从日志中可以看到：</p>";
echo "<ul>";
echo "<li>✅ 文件监控服务正常工作</li>";
echo "<li>✅ 上报成功（videoId=446）</li>";
echo "<li>✅ 七牛云上传服务已启动</li>";
echo "</ul>";
echo "<p>但是视频还是上传到了NAS，这说明：</p>";
echo "<ol>";
echo "<li>七牛云上传服务启动后立即失败</li>";
echo "<li>或者有其他地方还在调用VideoUploadService</li>";
echo "<li>需要查看Android端的完整日志</li>";
echo "</ol>";
echo "<hr>";
echo "<h2>需要提供的信息</h2>";
echo "<p>请提供Android端的完整日志，特别是：</p>";
echo "<ul>";
echo "<li><strong>QiniuUploadService</strong> 的日志（查看是否收到上传请求）</li>";
echo "<li><strong>VideoUploadService</strong> 的日志（查看是否被调用）</li>";
echo "<li><strong>AndroidRuntime</strong> 的错误日志（查看是否有异常）</li>";
echo "</ul>";
echo "<p>使用以下命令获取日志：</p>";
echo "<pre>adb logcat -s QiniuUploadService:V VideoUploadService:V FileMonitorService:V AndroidRuntime:E</pre>";