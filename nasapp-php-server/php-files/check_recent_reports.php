<?php
/**
 * 检查最近的上报记录
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>最近上报记录（最近10条）</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>用户ID</th>";
echo "<th>设备ID</th>";
echo "<th>文件名</th>";
echo "<th>文件大小</th>";
echo "<th>上报时间</th>";
echo "<th>过期时间</th>";
echo "</tr>";

try {
    $stmt = $db->query("SELECT * FROM videos ORDER BY id DESC LIMIT 10");
    $videos = $stmt->fetchAll();

    if (empty($videos)) {
        echo "<tr><td colspan='7' style='text-align: center;'>没有上报记录</td></tr>";
    } else {
        foreach ($videos as $video) {
            echo "<tr>";
            echo "<td>{$video['id']}</td>";
            echo "<td>{$video['userId']}</td>";
            echo "<td>{$video['deviceId']}</td>";
            echo "<td>{$video['originalName']}</td>";
            echo "<td>" . number_format($video['size'] / 1024 / 1024, 2) . " MB</td>";
            echo "<td>{$video['uploadTime']}</td>";
            echo "<td>{$video['expireTime']}</td>";
            echo "</tr>";
        }
    }

    echo "</table>";

    // 检查用户的 PushKey
    echo "<h2>用户 PushKey 配置</h2>";
    $stmt = $db->query("SELECT id, username, pushKey FROM users");
    $users = $stmt->fetchAll();

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr>";
    echo "<th>用户ID</th>";
    echo "<th>用户名</th>";
    echo "<th>PushKey</th>";
    echo "<th>状态</th>";
    echo "</tr>";

    foreach ($users as $user) {
        $status = empty($user['pushKey']) ? "❌ 未配置" : "✅ 已配置";
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>" . (empty($user['pushKey']) ? "-" : htmlspecialchars($user['pushKey'])) . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (Exception $e) {
    echo "<p style='color: red;'>错误: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='test_report_video.php'>测试上报接口</a></p>";
echo "<p><a href='test_pushdeer_simple.php'>测试推送</a></p>";
echo "<p><a href='check_pushkey.php'>检查 PushKey</a></p>";
?>