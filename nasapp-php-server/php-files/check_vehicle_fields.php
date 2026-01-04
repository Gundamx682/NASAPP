<?php
/**
 * 检查车辆信息字段诊断工具
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>车辆信息字段诊断</h2>";

try {
    // 检查表结构
    $columnsQuery = $db->query("PRAGMA table_info(users)");
    $columns = $columnsQuery->fetchAll();
    $columnNames = array_column($columns, 'name');

    echo "<h3>1. users 表字段检查</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>字段名</th><th>类型</th><th>状态</th></tr>";

    $requiredFields = ['deviceId', 'licensePlate', 'vinCode'];
    foreach ($requiredFields as $field) {
        $exists = in_array($field, $columnNames);
        $status = $exists ? "<span style='color: green;'>✅ 存在</span>" : "<span style='color: red;'>❌ 不存在</span>";
        $type = '';
        foreach ($columns as $col) {
            if ($col['name'] === $field) {
                $type = $col['type'];
                break;
            }
        }
        echo "<tr><td>{$field}</td><td>{$type}</td><td>{$status}</td></tr>";
    }
    echo "</table>";

    // 检查用户数据
    echo "<h3>2. 用户数据检查</h3>";
    $stmt = $db->query("SELECT id, username, deviceId, licensePlate, vinCode FROM users");
    $users = $stmt->fetchAll();

    if (empty($users)) {
        echo "<p>⚠️ 没有用户数据</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>用户名</th><th>设备ID</th><th>车牌号</th><th>车架号</th></tr>";

        foreach ($users as $user) {
            $deviceId = $user['deviceId'] ?? '';
            $licensePlate = $user['licensePlate'] ?? '';
            $vinCode = $user['vinCode'] ?? '';

            $deviceIdDisplay = empty($deviceId) ? "<span style='color: red;'>未设置</span>" : $deviceId;
            $licensePlateDisplay = empty($licensePlate) ? "<span style='color: red;'>未设置</span>" : $licensePlate;
            $vinCodeDisplay = empty($vinCode) ? "<span style='color: red;'>未设置</span>" : $vinCode;

            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$deviceIdDisplay}</td>";
            echo "<td>{$licensePlateDisplay}</td>";
            echo "<td>{$vinCodeDisplay}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // 诊断建议
    echo "<h3>3. 诊断建议</h3>";

    if (!in_array('deviceId', $columnNames) || !in_array('licensePlate', $columnNames) || !in_array('vinCode', $columnNames)) {
        echo "<p style='color: red;'><strong>❌ 数据库字段缺失</strong></p>";
        echo "<p>请执行数据库迁移脚本：<a href='add_vehicle_fields.php'>点击这里执行迁移</a></p>";
    } else {
        echo "<p style='color: green;'><strong>✅ 数据库字段完整</strong></p>";

        $hasEmptyFields = false;
        foreach ($users as $user) {
            if (empty($user['licensePlate']) || empty($user['vinCode'])) {
                $hasEmptyFields = true;
                break;
            }
        }

        if ($hasEmptyFields) {
            echo "<p style='color: orange;'><strong>⚠️ 部分用户未设置车牌号和车架号</strong></p>";
            echo "<p>请在APP中设置车牌号和车架号，然后点击"保存配置"</p>";
        } else {
            echo "<p style='color: green;'><strong>✅ 所有用户都已设置车牌号和车架号</strong></p>";
        }
    }

    echo "<hr>";
    echo "<p><a href='admin.php'>返回管理界面</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px;'>错误: " . $e->getMessage() . "</p>";
    echo "<p><a href='javascript:history.back()'>返回</a></p>";
}
?>