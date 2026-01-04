<?php
/**
 * 测试时间戳计算
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>时间戳测试</h1>";

// 模拟客户端发送的毫秒时间戳
$timestamp = time() * 1000; // 当前时间的毫秒时间戳

echo "<p>客户端发送的毫秒时间戳: $timestamp</p>";

// 新的计算方式（正确）
$timestampSeconds = $timestamp / 1000;
$uploadTime = date('Y-m-d H:i:s', $timestampSeconds);
$expireTime = date('Y-m-d H:i:s', $timestampSeconds + 604800); // 7天

echo "<h2>计算结果</h2>";
echo "<p>上传时间: $uploadTime</p>";
echo "<p>过期时间: $expireTime</p>";

echo "<h2>当前服务器时间</h2>";
echo "<p>" . date('Y-m-d H:i:s') . "</p>";

echo "<h2>转换回毫秒时间戳</h2>";
echo "<p>uploadTime (毫秒): " . (strtotime($uploadTime) * 1000) . "</p>";
echo "<p>expireTime (毫秒): " . (strtotime($expireTime) * 1000) . "</p>";
?>