<?php
/**
 * 检查视频存储位置
 */
header('Content-Type: text/html; charset=utf-8');

require_once 'config.php';

echo "<h1>视频存储位置检查</h1>";

// 检查用户14的最近视频记录
echo "<h2>用户14的最近10条视频记录</h2>";
$stmt = $db->prepare("
    SELECT id, userId, deviceId, originalName, filename, path, size, uploadTime
    FROM videos
    WHERE userId = 14
    ORDER BY uploadTime DESC
    LIMIT 10
");
$stmt->execute();
$videos = $stmt->fetchAll();

if ($videos) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>文件名</th><th>路径</th><th>存储类型</th><th>大小</th><th>上传时间</th></tr>";

    $qiniuCount = 0;
    $nasCount = 0;

    foreach ($videos as $video) {
        $path = $video['path'];
        $isQiniuUrl = strpos($path, 'clouddn.com') !== false;
        $storageType = $isQiniuUrl ? '七牛云' : 'NAS服务器';
        $pathColor = $isQiniuUrl ? 'green' : 'red';

        if ($isQiniuUrl) {
            $qiniuCount++;
        } else {
            $nasCount++;
        }

        echo "<tr>";
        echo "<td>{$video['id']}</td>";
        echo "<td>{$video['originalName']}</td>";
        echo "<td><span style='color:$pathColor'>$path</span></td>";
        echo "<td><strong>$storageType</strong></td>";
        echo "<td>" . round($video['size'] / 1024 / 1024, 2) . " MB</td>";
        echo "<td>{$video['uploadTime']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3>统计</h3>";
    echo "<p>七牛云存储: <strong>$qiniuCount</strong> 条</p>";
    echo "<p>NAS服务器存储: <strong>$nasCount</strong> 条</p>";

    if ($nasCount > 0) {
        echo "<p style='color:red; font-size:18px;'><strong>⚠️ 警告：仍有视频上传到NAS服务器！</strong></p>";
        echo "<p>这说明 QiniuUploadService 没有正常工作，或者还在使用旧的 VideoUploadService。</p>";
    } else {
        echo "<p style='color:green; font-size:18px;'><strong>✅ 所有视频都已上传到七牛云</strong></p>";
    }
} else {
    echo "<p>没有找到视频记录</p>";
}

// 检查uploads目录
echo "<h2>检查NAS uploads目录</h2>";
$uploadDir = __DIR__ . '/uploads';
if (is_dir($uploadDir)) {
    $files = glob($uploadDir . '/*.mp4');
    echo "<p>uploads目录中的视频文件数: <strong>" . count($files) . "</strong></p>";

    if (count($files) > 0) {
        echo "<p style='color:red'>⚠️ uploads目录中仍有视频文件</p>";
        echo "<ul>";
        foreach (array_slice($files, 0, 10) as $file) {
            $filename = basename($file);
            $filesize = round(filesize($file) / 1024 / 1024, 2);
            $filetime = date('Y-m-d H:i:s', filemtime($file));
            echo "<li>$filename ($filesize MB, $filetime)</li>";
        }
        if (count($files) > 10) {
            echo "<li>... 还有 " . (count($files) - 10) . " 个文件</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:green'>✅ uploads目录为空</p>";
    }
} else {
    echo "<p>uploads目录不存在</p>";
}

echo "<hr>";
echo "<h2>问题排查建议</h2>";
echo "<ol>";
echo "<li>如果视频仍在上传到NAS，说明 QiniuUploadService 没有被正确调用</li>";
echo "<li>请检查 Android 端日志，查找 QiniuUploadService 的启动信息</li>";
echo "<li>确认 FileMonitorService 中的 uploadToQiniu() 方法是否被调用</li>";
echo "<li>检查是否有错误日志显示七牛云上传失败</li>";
echo "</ol>";