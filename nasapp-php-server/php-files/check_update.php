<?php
/**
 * 版本检查接口
 * APP调用此接口检查是否有新版本
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // 获取APP当前版本
    $currentVersionCode = isset($_GET['versionCode']) ? intval($_GET['versionCode']) : 1;
    
    // 读取版本信息文件
    $versionFile = __DIR__ . '/version.json';
    
    if (!file_exists($versionFile)) {
        echo json_encode([
            'success' => false,
            'message' => '版本信息文件不存在'
        ]);
        exit;
    }
    
    $versionInfo = json_decode(file_get_contents($versionFile), true);
    
    // 检查是否需要更新
    $hasUpdate = $currentVersionCode < $versionInfo['versionCode'];
    
    echo json_encode([
        'success' => true,
        'hasUpdate' => $hasUpdate,
        'latestVersion' => $versionInfo
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '检查更新失败：' . $e->getMessage()
    ]);
}
?>