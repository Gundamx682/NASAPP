<?php
/**
 * 检查所有用户和七牛云配置
 */
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 所有用户和七牛云配置检查</h2>";

// 查询所有用户
$stmt = $db->query("SELECT id, username, email, qiniuAccessKey, qiniuSecretKey, qiniuBucket, qiniuDomain FROM users ORDER BY id");
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "<p style='color: red;'>❌ 数据库中没有用户</p>";
    exit;
}

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>ID</th>";
echo "<th>用户名</th>";
echo "<th>邮箱</th>";
echo "<th>七牛云AccessKey</th>";
echo "<th>七牛云SecretKey</th>";
echo "<th>七牛云Bucket</th>";
echo "<th>七牛云Domain</th>";
echo "<th>配置状态</th>";
echo "</tr>";

foreach ($users as $user) {
    $isConfigured = !empty($user['qiniuAccessKey']) && !empty($user['qiniuSecretKey']) &&
                   !empty($user['qiniuBucket']) && !empty($user['qiniuDomain']);

    $rowStyle = $isConfigured ? 'background: #e6ffed;' : 'background: #fff1f0;';

    echo "<tr style='$rowStyle'>";
    echo "<td><strong>{$user['id']}</strong></td>";
    echo "<td>{$user['username']}</td>";
    echo "<td>" . ($user['email'] ?: '-') . "</td>";
    echo "<td>" . (empty($user['qiniuAccessKey']) ? '<span style="color: red;">❌ 未配置</span>' : '<span style="color: green;">✅ 已配置</span>') . "</td>";
    echo "<td>" . (empty($user['qiniuSecretKey']) ? '<span style="color: red;">❌ 未配置</span>' : '<span style="color: green;">✅ 已配置</span>') . "</td>";
    echo "<td>" . (empty($user['qiniuBucket']) ? '<span style="color: red;">❌ 未配置</span>' : '<span style="color: green;">✅ ' . htmlspecialchars($user['qiniuBucket']) . '</span>') . "</td>";
    echo "<td>" . (empty($user['qiniuDomain']) ? '<span style="color: red;">❌ 未配置</span>' : '<span style="color: green;">✅ ' . htmlspecialchars($user['qiniuDomain']) . '</span>') . "</td>";
    echo "<td>" . ($isConfigured ? '<strong style="color: green;">✅ 配置完整</strong>' : '<strong style="color: red;">❌ 配置不完整</strong>') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>📝 测试API链接</h3>";
echo "<p>点击以下链接测试每个用户的七牛云配置：</p>";

foreach ($users as $user) {
    echo "<p><strong>用户ID {$user['id']} ({$user['username']}):</strong> ";
    echo "<a href='api/qiniu_token.php?userId={$user['id']}' target='_blank'>测试API</a></p>";
}

echo "<h3>💡 提示</h3>";
echo "<ul>";
echo "<li>如果配置状态显示'❌ 配置不完整'，请在APP中登录该用户并保存七牛云配置</li>";
echo "<li>如果配置状态显示'✅ 配置完整'，点击测试API链接应该返回上传凭证</li>";
echo "<li>确保APP中登录的用户ID与数据库中的用户ID一致</li>";
echo "</ul>";
?>