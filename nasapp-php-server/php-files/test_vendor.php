<?php
/**
 * 测试vendor目录和七牛云SDK
 */
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Vendor目录和七牛云SDK测试</h2>";

// 1. 检查vendor目录
echo "<h3>1. 检查vendor目录</h3>";
$vendorDir = __DIR__ . '/vendor';
if (file_exists($vendorDir)) {
    echo "<p style='color: green;'>✅ vendor目录存在: $vendorDir</p>";
    echo "<p>目录内容:</p>";
    echo "<pre>";
    print_r(scandir($vendorDir));
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ vendor目录不存在: $vendorDir</p>";
}

// 2. 检查autoload.php
echo "<h3>2. 检查autoload.php</h3>";
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    echo "<p style='color: green;'>✅ autoload.php存在: $autoloadFile</p>";
} else {
    echo "<p style='color: red;'>❌ autoload.php不存在: $autoloadFile</p>";
}

// 3. 尝试加载autoload
echo "<h3>3. 尝试加载autoload</h3>";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p style='color: green;'>✅ autoload加载成功</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ autoload加载失败: " . $e->getMessage() . "</p>";
}

// 4. 检查Qiniu\Auth类
echo "<h3>4. 检查Qiniu\Auth类</h3>";
if (class_exists('Qiniu\Auth')) {
    echo "<p style='color: green;'>✅ Qiniu\Auth类存在</p>";
} else {
    echo "<p style='color: red;'>❌ Qiniu\Auth类不存在</p>";
}

// 5. 检查composer.json
echo "<h3>5. 检查composer.json</h3>";
$composerJson = __DIR__ . '/vendor/composer.json';
if (file_exists($composerJson)) {
    echo "<p style='color: green;'>✅ composer.json存在</p>";
    echo "<pre>";
    echo file_get_contents($composerJson);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ composer.json不存在</p>";
}

// 6. 检查composer.lock
echo "<h3>6. 检查composer.lock</h3>";
$composerLock = __DIR__ . '/vendor/composer.lock';
if (file_exists($composerLock)) {
    echo "<p style='color: green;'>✅ composer.lock存在</p>";
} else {
    echo "<p style='color: red;'>❌ composer.lock不存在</p>";
}

// 7. 测试数据库连接
echo "<h3>7. 测试数据库连接</h3>";
try {
    require_once 'config.php';
    echo "<p style='color: green;'>✅ 数据库连接成功</p>";

    // 测试查询用户
    $stmt = $db->prepare("SELECT id, username FROM users WHERE id = 14");
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        echo "<p style='color: green;'>✅ 用户查询成功: ID={$user['id']}, 用户名={$user['username']}</p>";
    } else {
        echo "<p style='color: red;'>❌ 用户查询失败: 用户ID 14不存在</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 数据库连接失败: " . $e->getMessage() . "</p>";
}

echo "<h3>💡 建议</h3>";
echo "<ul>";
echo "<li>如果vendor目录不存在，需要重新安装七牛云SDK</li>";
echo "<li>如果autoload.php不存在，需要重新运行composer install</li>";
echo "<li>如果Qiniu\Auth类不存在，说明SDK安装不完整</li>";
echo "</ul>";
?>