<?php
/**
 * 简化的数据库诊断脚本
 */

// 禁用错误输出，避免污染 JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
    // 包含配置文件
    require_once 'config.php';
    
    $result = [
        'status' => 'success',
        'message' => '诊断成功',
        'data' => []
    ];
    
    // 检查数据库文件
    $result['data'][] = [
        'check' => '数据库文件',
        'exists' => file_exists(DB_FILE),
        'path' => DB_FILE,
        'readable' => is_readable(DB_FILE),
        'writable' => is_writable(DB_FILE),
        'size' => file_exists(DB_FILE) ? filesize(DB_FILE) : 0
    ];
    
    // 检查数据库连接
    try {
        $result['data'][] = [
            'check' => '数据库连接',
            'status' => 'success'
        ];
        
        // 获取表列表
        $tables = [];
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        while ($row = $stmt->fetch()) {
            $tables[] = $row['name'];
        }
        $result['data'][] = [
            'check' => '数据库表',
            'tables' => $tables,
            'count' => count($tables)
        ];
        
        // 检查用户表
        if (in_array('users', $tables)) {
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            $result['data'][] = [
                'check' => '用户数量',
                'count' => $userCount
            ];
            
            // 检查表结构
            $columns = [];
            $stmt = $db->query("PRAGMA table_info(users)");
            while ($row = $stmt->fetch()) {
                $columns[] = $row['name'];
            }
            $result['data'][] = [
                'check' => 'users表字段',
                'columns' => $columns,
                'has_pushKey' => in_array('pushKey', $columns)
            ];
        }
        
        // 检查视频表
        if (in_array('videos', $tables)) {
            $stmt = $db->query("SELECT COUNT(*) as count FROM videos");
            $videoCount = $stmt->fetch()['count'];
            $result['data'][] = [
                'check' => '视频数量',
                'count' => $videoCount
            ];
        }
        
    } catch (Exception $e) {
        $result['data'][] = [
            'check' => '数据库连接',
            'status' => 'failed',
            'error' => $e->getMessage()
        ];
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '诊断失败: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>