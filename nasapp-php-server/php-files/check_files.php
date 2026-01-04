<?php
/**
 * 简化的文件检查工具
 */
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>文件位置检查</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px; overflow-x: auto; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #007bff; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📁 文件位置检查工具</h1>
        
        <div class='section'>
            <h2>1. 当前诊断脚本位置</h2>
            <p><strong>__DIR__:</strong> " . __DIR__ . "</p>
            <p><strong>当前文件:</strong> " . __FILE__ . "</p>
            <p><strong>当前URL:</strong> " . $_SERVER['PHP_SELF'] . "</p>
        </div>
        
        <div class='section'>
            <h2>2. 当前目录文件列表</h2>
            <pre>";
            
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $fullPath = __DIR__ . '/' . $file;
        $type = is_dir($fullPath) ? '[目录]' : '[文件]';
        $size = is_file($fullPath) ? filesize($fullPath) : '';
        echo "$type $file $size\n";
    }
}
            
echo "</pre>
        </div>
        
        <div class='section'>
            <h2>3. 检查api子目录</h2>";
            
$apiDir = __DIR__ . '/api';
if (is_dir($apiDir)) {
    echo "<p class='success'>✅ api目录存在</p>";
    echo "<pre>";
    $apiFiles = scandir($apiDir);
    foreach ($apiFiles as $file) {
        if ($file != '.' && $file != '..') {
            $fullPath = $apiDir . '/' . $file;
            $type = is_dir($fullPath) ? '[目录]' : '[文件]';
            $size = is_file($fullPath) ? filesize($fullPath) : '';
            echo "$type $file $size\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p class='error'>❌ api目录不存在</p>";
    echo "<p class='info'>💡 请创建api目录: mkdir " . $apiDir . "</p>";
}
            
echo "</div>
        
        <div class='section'>
            <h2>4. 检查关键文件</h2>";
            
$requiredFiles = [
    'config.php',
    'login.php',
    'get_config.php',
    'save_config.php',
    'diagnose_qiniu_api.php',
    'api/qiniu_token.php',
    'api/report_qiniu_video.php',
];

echo "<table>";
echo "<tr><th>文件</th><th>路径</th><th>状态</th></tr>";

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    $exists = file_exists($fullPath);
    $status = $exists ? "<span class='success'>✅ 存在</span>" : "<span class='error'>❌ 不存在</span>";
    echo "<tr><td>$file</td><td>$fullPath</td><td>$status</td></tr>";
}

echo "</table>";
echo "</div>
        
        <div class='section'>
            <h2>5. 修复建议</h2>";
            
$missing = [];
foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (!file_exists($fullPath)) {
        $missing[] = $file;
    }
}

if (empty($missing)) {
    echo "<p class='success'>✅ 所有文件都存在！</p>";
} else {
    echo "<p class='error'>❌ 缺少的文件：</p>";
    echo "<ul>";
    foreach ($missing as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
    echo "<p class='info'>💡 请将这些文件上传到: " . __DIR__ . "</p>";
}
            
echo "</div>
        
        <div class='section'>
            <h2>6. 手动命令</h2>";
            
echo "<p>如果您有SSH访问权限，可以运行以下命令：</p>";
echo "<pre>";
echo "# 进入sentinel目录\n";
echo "cd " . __DIR__ . "\n\n";
echo "# 查看当前文件\n";
echo "ls -la\n\n";
echo "# 检查api目录\n";
echo "ls -la api/\n\n";
echo "# 如果文件在其他位置，移动到这里\n";
echo "# 例如：mv /path/to/config.php .\n";
echo "</pre>";
echo "</div>
    </div>
</body>
</html>";
?>