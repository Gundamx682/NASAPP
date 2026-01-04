<?php
/**
 * 定时清理过期视频
 * 可以通过群晖计划任务每小时执行一次
 */

require_once 'config.php';

echo "===========================================\n";
echo "哨兵模式视频监控 - 清理过期视频\n";
echo "===========================================\n";
echo "开始时间: " . date('Y-m-d H:i:s') . "\n";

try {
    // 查询过期视频
    $stmt = $db->prepare("SELECT id, path, size FROM videos WHERE expireTime < datetime('now')");
    $stmt->execute();
    $expiredVideos = $stmt->fetchAll();
    
    $deletedCount = 0;
    $totalSize = 0;
    
    echo "找到 " . count($expiredVideos) . " 个过期视频\n\n";
    
    foreach ($expiredVideos as $video) {
        $deleted = false;
        
        // 删除视频文件
        if (file_exists($video['path'])) {
            $size = filesize($video['path']);
            if (unlink($video['path'])) {
                echo "✓ 删除视频: {$video['path']} (" . number_format($size / 1024 / 1024, 2) . "MB)\n";
                $totalSize += $size;
                $deleted = true;
            } else {
                echo "✗ 删除视频失败: {$video['path']}\n";
            }
        } else {
            echo "○ 视频文件不存在: {$video['path']}\n";
        }
        
        // 删除缩略图
        $thumbnailPath = THUMBNAIL_DIR . '/' . $video['id'] . '.jpg';
        if (file_exists($thumbnailPath)) {
            if (unlink($thumbnailPath)) {
                echo "✓ 删除缩略图: {$thumbnailPath}\n";
                $deleted = true;
            } else {
                echo "✗ 删除缩略图失败: {$thumbnailPath}\n";
            }
        }
        
        if ($deleted) {
            $deletedCount++;
        }
        
        echo "\n";
    }
    
    // 从数据库删除记录
    $stmt = $db->prepare("DELETE FROM videos WHERE expireTime < datetime('now')");
    $stmt->execute();
    $affectedRows = $stmt->rowCount();
    
    echo "===========================================\n";
    echo "清理完成\n";
    echo "-------------------------------------------\n";
    echo "删除视频数: {$deletedCount}\n";
    echo "数据库删除: {$affectedRows} 条记录\n";
    echo "释放空间: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";
    echo "结束时间: " . date('Y-m-d H:i:s') . "\n";
    echo "===========================================\n";
    
} catch (Exception $e) {
    echo "===========================================\n";
    echo "清理失败\n";
    echo "-------------------------------------------\n";
    echo "错误信息: " . $e->getMessage() . "\n";
    echo "结束时间: " . date('Y-m-d H:i:s') . "\n";
    echo "===========================================\n";
    error_log('清理失败: ' . $e->getMessage());
}
?>