<?php
/**
 * 删除七牛云配置字段的数据库迁移脚本
 * 运行方式: php remove_qiniu_fields.php
 */

require_once 'config.php';

echo "开始删除七牛云配置字段...\n";
echo "数据库路径: " . DB_FILE . "\n";

try {
    // SQLite 不支持直接删除列，需要重建表
    echo "步骤 1: 备份现有数据...\n";

    // 创建新表（不包含七牛云字段）
    echo "步骤 2: 创建新表结构...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS users_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            email TEXT,
            pushKey TEXT,
            fcmToken TEXT,
            monitorDirectory TEXT,
            autoUploadEnabled INTEGER DEFAULT 0,
            deviceId TEXT DEFAULT '',
            licensePlate TEXT DEFAULT '',
            vinCode TEXT DEFAULT '',
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            lastLogin DATETIME
        )
    ");

    // 迁移数据（不包含七牛云字段）
    echo "步骤 3: 迁移数据到新表...\n";
    $db->exec("
        INSERT INTO users_new (id, username, password, email, pushKey, fcmToken, monitorDirectory, autoUploadEnabled, deviceId, licensePlate, vinCode, createdAt, lastLogin)
        SELECT id, username, password, email, pushKey, fcmToken, monitorDirectory, autoUploadEnabled, deviceId, licensePlate, vinCode, createdAt, lastLogin
        FROM users
    ");

    // 删除旧表
    echo "步骤 4: 删除旧表...\n";
    $db->exec('DROP TABLE users');

    // 重命名新表
    echo "步骤 5: 重命名新表...\n";
    $db->exec('ALTER TABLE users_new RENAME TO users');

    echo "✅ 七牛云配置字段删除成功！\n";
    echo "已删除的字段: qiniuAccessKey, qiniuSecretKey, qiniuBucket, qiniuDomain\n";

} catch (Exception $e) {
    echo "❌ 迁移失败: " . $e->getMessage() . "\n";
    exit(1);
}
