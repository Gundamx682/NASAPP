<?php
/**
 * URL 修复测试脚本
 */

require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$baseUrl = getBaseUrl();
$testUserId = 1;

// 测试视频列表 URL
$videosUrl = $baseUrl . '/videos.php?userId=' . $testUserId;

// 测试视频播放 URL
$videoUrl = $baseUrl . '/video.php?id=1';

echo json_encode([
    'success' => true,
    'BASE_URL' => BASE_URL,
    'getBaseUrl' => $baseUrl,
    'videosUrl' => $videosUrl,
    'videoUrl' => $videoUrl,
    'hasDoubleSlash' => strpos($baseUrl, '//') !== false,
    'hasTrailingSlash' => substr($baseUrl, -1) === '/'
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>