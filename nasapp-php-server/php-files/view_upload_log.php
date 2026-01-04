<?php
/**
 * 查看上传日志
 */

require_once 'config.php';

echo "=== 哨兵监控上传日志（最近20条）===\n\n";

// 读取日志文件
$logFile = '/volume1/web/sentinel/upload.log';

if (!file_exists($logFile)) {
    die("日志文件不存在: $logFile\n");
}

// 读取最后20行
$lines = array_slice(file($logFile), -20);

if (empty($lines)) {
    echo "日志文件为空\n";
    exit;
}

// 反转数组，显示最新的日志在前
$lines = array_reverse($lines);

foreach ($lines as $line) {
    echo $line . "\n";
}

echo "\n=== 日志文件位置: $logFile ===\n";
?>