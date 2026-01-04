<?php
/**
 * 诊断脚本 - 检查数据库和文件夹
 */

header('Content-Type: text/html; charset=utf-8');

$uploadDir = '/volume1/web/sentinel/uploads';
$dbFile = '/volume1/web/sentinel/database/sentinel.db';

echo "<h1>系统诊断</h1>";

// 检查目录权限
echo "<h2>目录权限检查</h2>";
$dirs = [
    '/volume1/web/sentinel',
    '/volume1/web/sentinel/uploads',
    '/volume1/web/sentinel/database',
    '/volume1/web/sentinel/thumbnails'
];

foreach ($dirs as $dir) {
    $exists = file_exists($dir);
    $writable = is_writable($dir);
    $perms = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
    
    echo "<p><strong>$dir</strong><br>";
    echo "存在: " . ($exists ? '是' : '否') . "<br>";
    echo "可写: " . ($writable ? '是' : '否') . "<br>";
    echo "权限: $perms</p>";
}

// 检查数据库
echo "<h2>数据库检查</h2>";
$dbExists = file_exists($dbFile);
echo "<p>数据库文件: $dbFile<br>";
echo "存在: " . ($dbExists ? '是' : '否') . "</p>";

if ($dbExists) {
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 检查表
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>数据库表: " . implode(', ', $tables) . "</p>";
        
        // 检查用户
        if (in_array('users', $tables)) {
            $count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            echo "<p>用户数量: $count</p>";
            
            $users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($users);
            echo "</pre>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>数据库错误: " . $e->getMessage() . "</p>";
    }
}

// 检查uploads目录内容
echo "<h2>Uploads目录内容</h2>";
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    echo "<p>文件/文件夹: " . implode(', ', array_diff($files, ['.', '..'])) . "</p>";
} else {
    echo "<p style='color:red'>Uploads目录不存在</p>";
}

// 测试创建文件夹
echo "<h2>测试创建文件夹</h2>";
$testDir = $uploadDir . '/test_' . time();
if (mkdir($testDir, 0777, true)) {
    echo "<p style='color:green'>✓ 成功创建测试文件夹: $testDir</p>";
    rmdir($testDir);
} else {
    echo "<p style='color:red'>✗ 无法创建文件夹</p>";
}

// 测试写入数据库
echo "<h2>测试写入数据库</h2>";
try {
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 创建表（如果不存在）
    $db->exec("CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, name TEXT)");
    
    // 插入测试数据
    $stmt = $db->prepare("INSERT INTO test_table (name) VALUES (?)");
    $stmt->execute(['test_' . time()]);
    
    // 查询测试数据
    $result = $db->query("SELECT * FROM test_table")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color:green'>✓ 数据库写入测试成功</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    // 清理测试表
    $db->exec("DROP TABLE test_table");
} catch (Exception $e) {
    echo "<p style='color:red'>✗ 数据库写入失败: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>PHP信息</strong></p>";
echo "<p>PHP版本: " . phpversion() . "</p>";
echo "<p>SQLite扩展: " . (extension_loaded('pdo_sqlite') ? '已启用' : '未启用') . "</p>";
echo "<p>PDO SQLite驱动: " . (in_array('sqlite', PDO::getAvailableDrivers()) ? '可用' : '不可用') . "</p>";
?>