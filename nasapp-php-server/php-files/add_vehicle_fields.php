<?php
/**
 * 数据库迁移脚本 - 添加车辆信息字段
 * 添加 deviceId, licensePlate, vinCode 字段到 users 表
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>数据库迁移 - 添加车辆信息字段</h2>";

try {
    // 检查字段是否已存在
    $columnsQuery = $db->query("PRAGMA table_info(users)");
    $columns = $columnsQuery->fetchAll();
    $columnNames = array_column($columns, 'name');

    echo "<h3>当前 users 表字段：</h3>";
    echo "<ul>";
    foreach ($columnNames as $col) {
        echo "<li>$col</li>";
    }
    echo "</ul>";

    $alterStatements = [];

    // 添加 deviceId 字段
    if (!in_array('deviceId', $columnNames)) {
        $alterStatements[] = "ALTER TABLE users ADD COLUMN deviceId TEXT DEFAULT ''";
        echo "<p>✅ 将添加 deviceId 字段</p>";
    } else {
        echo "<p>ℹ️  deviceId 字段已存在，跳过</p>";
    }

    // 添加 licensePlate 字段
    if (!in_array('licensePlate', $columnNames)) {
        $alterStatements[] = "ALTER TABLE users ADD COLUMN licensePlate TEXT DEFAULT ''";
        echo "<p>✅ 将添加 licensePlate 字段</p>";
    } else {
        echo "<p>ℹ️  licensePlate 字段已存在，跳过</p>";
    }

    // 添加 vinCode 字段
    if (!in_array('vinCode', $columnNames)) {
        $alterStatements[] = "ALTER TABLE users ADD COLUMN vinCode TEXT DEFAULT ''";
        echo "<p>✅ 将添加 vinCode 字段</p>";
    } else {
        echo "<p>ℹ️  vinCode 字段已存在，跳过</p>";
    }

    // 执行 ALTER TABLE 语句
    if (!empty($alterStatements)) {
        echo "<h3>执行迁移...</h3>";
        foreach ($alterStatements as $sql) {
            try {
                $db->exec($sql);
                echo "<p>✅ 执行成功: $sql</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ 执行失败: $sql</p>";
                echo "<p style='color: red;'>错误: " . $e->getMessage() . "</p>";
            }
        }

        echo "<h3>迁移完成！</h3>";
        echo "<p><a href='admin.php'>返回管理界面</a></p>";
    } else {
        echo "<h3>所有字段已存在，无需迁移</h3>";
        echo "<p><a href='admin.php'>返回管理界面</a></p>";
    }

    // 显示更新后的表结构
    echo "<h3>更新后的 users 表字段：</h3>";
    $columnsQuery = $db->query("PRAGMA table_info(users)");
    $columns = $columnsQuery->fetchAll();
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li>{$col['name']} ({$col['type']})</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px;'>错误: " . $e->getMessage() . "</p>";
    echo "<p><a href='javascript:history.back()'>返回</a></p>";
}
?>