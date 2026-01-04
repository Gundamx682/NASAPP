<?php
// 最简单的测试脚本
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$tests = [];
$dbTables = [];
$userCount = 0;
$videoCount = 0;

// 测试1: PHP环境
$tests['php'] = [
    'name' => 'PHP环境',
    'status' => true,
    'message' => 'PHP ' . PHP_VERSION . ' 运行正常'
];

// 测试2: 配置文件
$tests['config'] = [
    'name' => '配置文件',
    'status' => true,
    'message' => 'config.php 加载成功'
];

// 测试3: 数据库连接
try {
    $db = new PDO('sqlite:' . DB_FILE);
    $tests['database'] = [
        'name' => '数据库连接',
        'status' => true,
        'message' => '数据库连接成功'
    ];
    
    // 获取表信息
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    $dbTables = $tables;
    
    // 获取用户和视频数量
    if (in_array('users', $tables)) {
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
    if (in_array('videos', $tables)) {
        $videoCount = $db->query("SELECT COUNT(*) FROM videos")->fetchColumn();
    }
} catch (Exception $e) {
    $tests['database'] = [
        'name' => '数据库连接',
        'status' => false,
        'message' => $e->getMessage()
    ];
}

// 测试4: 目录权限
$tests['upload_dir'] = [
    'name' => '上传目录',
    'status' => is_dir(UPLOAD_DIR) && is_writable(UPLOAD_DIR),
    'message' => is_dir(UPLOAD_DIR) ? (is_writable(UPLOAD_DIR) ? '可写' : '只读') : '不存在'
];

$tests['thumbnail_dir'] = [
    'name' => '缩略图目录',
    'status' => is_dir(THUMBNAIL_DIR) && is_writable(THUMBNAIL_DIR),
    'message' => is_dir(THUMBNAIL_DIR) ? (is_writable(THUMBNAIL_DIR) ? '可写' : '只读') : '不存在'
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP环境测试</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-bottom: 20px; }
        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            background: #f8f9fa;
        }
        .test-item.success { border-left: 4px solid #4CAF50; }
        .test-item.error { border-left: 4px solid #f44336; }
        .test-name { font-weight: 600; color: #333; }
        .test-message { color: #666; font-size: 14px; }
        .status-icon { font-size: 20px; margin-right: 10px; }
        .info-box {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
        }
        .info-box h3 { color: #0d47a1; margin-bottom: 10px; }
        .info-box p { color: #0d47a1; margin: 5px 0; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number { font-size: 28px; font-weight: 700; }
        .stat-label { font-size: 14px; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 PHP环境测试</h1>
        
        <?php foreach ($tests as $test): ?>
            <div class="test-item <?php echo $test['status'] ? 'success' : 'error'; ?>">
                <div>
                    <span class="status-icon"><?php echo $test['status'] ? '✅' : '❌'; ?></span>
                    <span class="test-name"><?php echo $test['name']; ?></span>
                </div>
                <span class="test-message"><?php echo $test['message']; ?></span>
            </div>
        <?php endforeach; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $userCount; ?></div>
                <div class="stat-label">注册用户</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $videoCount; ?></div>
                <div class="stat-label">视频总数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($dbTables); ?></div>
                <div class="stat-label">数据库表</div>
            </div>
        </div>

        <div class="info-box">
            <h3>📋 系统信息</h3>
            <p><strong>测试时间:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>基础URL:</strong> <?php echo BASE_URL; ?></p>
            <p><strong>数据库文件:</strong> <?php echo DB_FILE; ?></p>
            <p><strong>上传目录:</strong> <?php echo UPLOAD_DIR; ?></p>
            <p><strong>数据库表:</strong> <?php echo implode(', ', $dbTables); ?></p>
        </div>
    </div>
</body>
</html>