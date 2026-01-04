<?php
/**
 * 添加 monitorDirectory 字段到 users 表
 */

require_once 'config.php';

echo "=== 添加 monitorDirectory 字段 ===\n\n";

try {
    // 检查字段是否已存在
    $stmt = $db->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll();

    $columnExists = false;
    foreach ($columns as $column) {
        if ($column['name'] === 'monitorDirectory') {
            $columnExists = true;
            break;
        }
    }

    if ($columnExists) {
        echo "✅ monitorDirectory 字段已存在，无需添加\n";
    } else {
        // 添加字段
        $db->exec("ALTER TABLE users ADD COLUMN monitorDirectory TEXT DEFAULT ''");
        echo "✅ monitorDirectory 字段添加成功\n";
    }

    // 验证字段
    $stmt = $db->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll();

    echo "\n当前 users 表结构:\n";
    foreach ($columns as $column) {
        $type = $column['type'];
        $notNull = $column['notnull'] ? 'NOT NULL' : '';
        $default = $column['dflt_value'] !== null ? "DEFAULT {$column['dflt_value']}" : '';
        echo "- {$column['name']}: $type $notNull $default\n";
    }

} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

echo "\n=== 完成 ===\n";
?>