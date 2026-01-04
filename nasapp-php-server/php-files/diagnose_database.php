<?php
/**
 * 数据库诊断脚本
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$diagnostics = [];

try {
    // 检查数据库文件是否存在
    $diagnostics['database_file_exists'] = file_exists(DB_FILE);
    $diagnostics['database_file_path'] = DB_FILE;
    $diagnostics['database_file_size'] = file_exists(DB_FILE) ? filesize(DB_FILE) : 0;
    $diagnostics['database_file_readable'] = is_readable(DB_FILE);
    $diagnostics['database_file_writable'] = is_writable(DB_FILE);
    
    // 检查目录权限
    $diagnostics['database_dir_exists'] = is_dir(dirname(DB_FILE));
    $diagnostics['database_dir_writable'] = is_writable(dirname(DB_FILE));
    
    // 检查上传目录
    $diagnostics['upload_dir_exists'] = is_dir(UPLOAD_DIR);
    $diagnostics['upload_dir_writable'] = is_writable(UPLOAD_DIR);
    
    // 检查缩略图目录
    $diagnostics['thumbnail_dir_exists'] = is_dir(THUMBNAIL_DIR);
    $diagnostics['thumbnail_dir_writable'] = is_writable(THUMBNAIL_DIR);
    
    // 检查表是否存在
    $tables = [];
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    while ($row = $result->fetch()) {
        $tables[] = $row['name'];
    }
    $diagnostics['tables'] = $tables;
    
    // 检查用户表结构
    if (in_array('users', $tables)) {
        $result = $db->query("PRAGMA table_info(users)");
        $columns = [];
        while ($row = $result->fetch()) {
            $columns[] = [
                'name' => $row['name'],
                'type' => $row['type'],
                'notnull' => $row['notnull'],
                'pk' => $row['pk']
            ];
        }
        $diagnostics['users_table_structure'] = $columns;
        
        // 检查是否有 pushKey 字段
        $hasPushKey = false;
        foreach ($columns as $column) {
            if ($column['name'] === 'pushKey') {
                $hasPushKey = true;
                break;
            }
        }
        $diagnostics['users_has_pushKey'] = $hasPushKey;
        
        // 统计用户数量
        $result = $db->query("SELECT COUNT(*) as count FROM users");
        $diagnostics['users_count'] = $result->fetch()['count'];
    }
    
    // 检查视频表结构
    if (in_array('videos', $tables)) {
        $result = $db->query("PRAGMA table_info(videos)");
        $columns = [];
        while ($row = $result->fetch()) {
            $columns[] = [
                'name' => $row['name'],
                'type' => $row['type'],
                'notnull' => $row['notnull'],
                'pk' => $row['pk']
            ];
        }
        $diagnostics['videos_table_structure'] = $columns;
        
        // 统计视频数量
        $result = $db->query("SELECT COUNT(*) as count FROM videos");
        $diagnostics['videos_count'] = $result->fetch()['count'];
    }
    
    // 测试插入
    try {
        $testUsername = 'test_user_' . time();
        $db->exec("INSERT INTO users (username, password) VALUES ('$testUsername', 'test123')");
        $db->exec("DELETE FROM users WHERE username = '$testUsername'");
        $diagnostics['database_insert_test'] = '成功';
    } catch (Exception $e) {
        $diagnostics['database_insert_test'] = '失败: ' . $e->getMessage();
    }
    
    echo json_encode([
        'success' => true,
        'diagnostics' => $diagnostics
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '诊断失败: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
}
?>