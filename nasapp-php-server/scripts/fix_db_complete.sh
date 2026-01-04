#!/bin/bash

################################################################################
# 完整修复数据库管理工具
# 修复所有 JSON 响应问题
################################################################################

echo "========================================"
echo "  完整修复数据库管理工具"
echo "========================================"
echo ""

# 1. 修复 diagnose_database.php
echo "步骤 1/3: 修复 diagnose_database.php..."
cat > /var/www/html/sentinel/diagnose_database.php <<'EOF'
<?php
/**
 * 数据库诊断工具
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once 'config.php';

    // 连接数据库
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $result = [
        'success' => true,
        'database' => DB_FILE,
        'exists' => file_exists(DB_FILE),
        'size' => file_exists(DB_FILE) ? filesize(DB_FILE) : 0,
        'tables' => []
    ];

    // 获取所有表
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    $result['tables'] = $tables;

    // 检查每个表
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        $result[$table] = [
            'count' => $count,
            'columns' => []
        ];

        // 获取列信息
        $columns = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $result[$table]['columns'][] = [
                'name' => $col['name'],
                'type' => $col['type'],
                'notnull' => $col['notnull'],
                'pk' => $col['pk']
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
EOF

chmod 777 /var/www/html/sentinel/diagnose_database.php
echo "✓ diagnose_database.php 已修复"

# 2. 修复 reset_database.php
echo "步骤 2/3: 修复 reset_database.php..."
cat > /var/www/html/sentinel/reset_database.php <<'EOF'
<?php
/**
 * 重置数据库
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once 'config.php';

    // 连接数据库
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 删除数据库文件
    if (file_exists(DB_FILE)) {
        unlink(DB_FILE);
    }

    // 重新连接（会自动创建）
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建用户表
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        email TEXT,
        pushKey TEXT,
        fcmToken TEXT,
        monitorDirectory TEXT,
        autoUploadEnabled INTEGER DEFAULT 0,
        deviceId TEXT DEFAULT "",
        licensePlate TEXT DEFAULT "",
        vinCode TEXT DEFAULT "",
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        lastLogin DATETIME
    )');

    // 创建视频表
    $db->exec('CREATE TABLE IF NOT EXISTS videos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        userId INTEGER NOT NULL,
        deviceId TEXT NOT NULL,
        originalName TEXT NOT NULL,
        filename TEXT NOT NULL,
        path TEXT NOT NULL,
        size INTEGER NOT NULL,
        uploadTime DATETIME NOT NULL,
        expireTime DATETIME NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
    )');

    // 创建索引
    $db->exec('CREATE INDEX IF NOT EXISTS idx_videos_userId ON videos(userId)');
    $db->exec('CREATE INDEX IF NOT EXISTS idx_videos_uploadTime ON videos(uploadTime)');

    // 创建测试用户
    $stmt = $db->prepare('INSERT INTO users (username, password, email, pushKey) VALUES (?, ?, ?, ?)');
    $stmt->execute(['test', '123456', 'test@example.com', 'test_push_key_' . time()]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '数据库重置成功'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
EOF

chmod 777 /var/www/html/sentinel/reset_database.php
echo "✓ reset_database.php 已修复"

# 3. 测试
echo "步骤 3/3: 测试..."
echo ""
echo "=== 测试 diagnose_database.php ==="
curl -s http://localhost:9665/diagnose_database.php

echo ""
echo "=== 测试 reset_database.php ==="
curl -s http://localhost:9665/reset_database.php

echo ""
echo "========================================"
echo "  修复完成！"
echo "========================================"