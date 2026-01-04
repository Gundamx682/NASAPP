<?php
/**
 * 测试时间戳转换
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>时间戳转换测试</h1>";

// 模拟客户端发送的毫秒时间戳
$timestamp = time() * 1000; // 当前时间的毫秒时间戳

echo "<p>客户端发送的毫秒时间戳: $timestamp</p>";

// 错误的计算方式（旧代码）
$uploadTimeWrong = date('Y-m-d H:i:s', $timestamp);

echo "<h2>错误的计算方式（直接使用毫秒）</h2>";
echo "<p>上传时间: $uploadTimeWrong</p>";
echo "<p style='color: red;'>这个时间是错误的！因为 date() 函数期望的是秒级时间戳</p>";

// 正确的计算方式（新代码）
$timestampSeconds = $timestamp / 1000;
$uploadTimeCorrect = date('Y-m-d H:i:s', $timestampSeconds);

echo "<h2>正确的计算方式（转换为秒）</h2>";
echo "<p>秒级时间戳: $timestampSeconds</p>";
echo "<p>上传时间: $uploadTimeCorrect</p>";
echo "<p style='color: green;'>这个时间是正确的！</p>";

echo "<h2>当前服务器时间</h2>";
echo "<p>" . date('Y-m-d H:i:s') . "</p>";
echo "<p>秒级时间戳: " . time() . "</p>";
echo "<p>毫秒时间戳: " . (time() * 1000) . "</p>";

// 测试一个具体的时间戳
echo "<h2>测试具体时间戳</h2>";
$testTimestamp = 1735734000000; // 2025-01-01 12:00:00 的毫秒时间戳
echo "<p>测试毫秒时间戳: $testTimestamp</p>";

$testTimestampSeconds = $testTimestamp / 1000;
$testTime = date('Y-m-d H:i:s', $testTimestampSeconds);
echo "<p>转换后的时间: $testTime</p>";
echo "<p>预期时间: 2025-01-01 12:00:00</p>";

if ($testTime === '2025-01-01 12:00:00') {
    echo "<p style='color: green;'>✅ 转换正确！</p>";
} else {
    echo "<p style='color: red;'>❌ 转换错误！</p>";
}
?>