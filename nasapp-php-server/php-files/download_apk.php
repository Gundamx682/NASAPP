<?php
/**
 * 下载APK接口
 * APP调用此接口下载最新APK
 */

header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="sentinel_new.apk"');
header('Access-Control-Allow-Origin: *');

try {
    $apkFile = __DIR__ . '/sentinel_new.apk';
    
    if (!file_exists($apkFile)) {
        header('HTTP/1.1 404 Not Found');
        echo 'APK文件不存在';
        exit;
    }
    
    // 设置文件大小
    $fileSize = filesize($apkFile);
    header('Content-Length: ' . $fileSize);
    
    // 输出文件
    readfile($apkFile);
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo '下载失败：' . $e->getMessage();
}
?>