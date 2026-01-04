<?php
/**
 * 健康检查接口
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => '服务器运行正常',
    'timestamp' => date('c')
], JSON_UNESCAPED_UNICODE);
?>