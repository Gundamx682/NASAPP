<?php
/**
 * 数据库重置脚本
 * 警告：此脚本会删除所有数据！
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // 备份当前数据库
    $backupFile = dirname(DB_FILE) . '/sentinel_backup_' . date('YmdHis') . '.db';
    if (file_exists(DB_FILE)) {
        copy(DB_FILE, $backupFile);
    }
    
    // 删除旧数据库
    if (file_exists(DB_FILE)) {
        unlink(DB_FILE);
    }
    
    // 重新连接数据库
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 创建用户表
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            email TEXT,
            pushKey TEXT,
            fcmToken TEXT,
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            lastLogin DATETIME
        )
    ");
    
    // 创建视频表
    $db->exec("
        CREATE TABLE IF NOT EXISTS videos (
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
            FOREIGN KEY (userId) REFERENCES users(id)
        )
    ");
    
    // 创建索引
    $db->exec("CREATE INDEX IF NOT EXISTS idx_videos_userId ON videos(userId)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_videos_uploadTime ON videos(uploadTime)");
    
    echo json_encode([
        'success' => true,
        'message' => '数据库已重置',
        'backupFile' => basename($backupFile)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '重置失败: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>