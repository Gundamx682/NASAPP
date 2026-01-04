<?php
/**
 * NASAPP 数据库初始化脚本
 * 用于创建数据库表结构和初始测试用户
 */

require_once 'config.php';

try {
    // 连接数据库
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "开始创建数据库表...\n";
    
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
    echo "✓ 用户表创建成功\n";
    
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
    echo "✓ 视频表创建成功\n";
    
    // 创建索引
    $db->exec('CREATE INDEX IF NOT EXISTS idx_videos_userId ON videos(userId)');
    $db->exec('CREATE INDEX IF NOT EXISTS idx_videos_uploadTime ON videos(uploadTime)');
    echo "✓ 索引创建成功\n";
    
    // 检查是否已存在测试用户
    $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute(['test']);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // 创建测试用户
        $stmt = $db->prepare('INSERT INTO users (username, password, email, pushKey) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            'test',
            password_hash('123456', PASSWORD_DEFAULT),
            'test@example.com',
            'test_push_key_' . time()
        ]);
        echo "✓ 测试用户创建成功 (test/123456)\n";
    } else {
        echo "✓ 测试用户已存在\n";
    }
    
    echo "\n数据库初始化完成！\n";
    
} catch (PDOException $e) {
    echo "数据库初始化失败: " . $e->getMessage() . "\n";
    exit(1);
}
