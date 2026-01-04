<?php
/**
 * PHP配置诊断脚本
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>PHP上传配置诊断</h1>";
echo "<h2>当前PHP配置</h2>";

$configs = [
    'post_max_size' => 'POST请求最大大小',
    'upload_max_filesize' => '上传文件最大大小',
    'memory_limit' => '内存限制',
    'max_execution_time' => '最大执行时间（秒）',
    'max_input_time' => '最大输入时间（秒）',
    'file_uploads' => '是否允许文件上传',
    'upload_tmp_dir' => '上传临时目录'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>配置项</th><th>说明</th><th>当前值</th><th>建议值</th></tr>";

foreach ($configs as $key => $desc) {
    $current = ini_get($key);
    $recommended = '';
    
    switch ($key) {
        case 'post_max_size':
            $recommended = '512M';
            break;
        case 'upload_max_filesize':
            $recommended = '512M';
            break;
        case 'memory_limit':
            $recommended = '512M';
            break;
        case 'max_execution_time':
            $recommended = '300';
            break;
        case 'max_input_time':
            $recommended = '300';
            break;
        case 'file_uploads':
            $recommended = 'On';
            break;
    }
    
    $status = '';
    if ($key === 'file_uploads') {
        $status = $current === '1' || $current === 'On' ? '✅' : '❌';
    } else {
        $currentBytes = return_bytes($current);
        $recBytes = return_bytes($recommended);
        $status = $currentBytes >= $recBytes ? '✅' : '⚠️';
    }
    
    echo "<tr><td>$key</td><td>$desc</td><td>$current $status</td><td>$recommended</td></tr>";
}

echo "</table>";

echo "<h2>测试上传</h2>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file' required><br><br>";
echo "<input type='submit' value='测试上传'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>上传测试结果</h3>";
    $file = $_FILES['test_file'];
    echo "文件名: " . htmlspecialchars($file['name']) . "<br>";
    echo "文件大小: " . format_bytes($file['size']) . "<br>";
    echo "上传错误: " . $file['error'] . " (" . get_upload_error_message($file['error']) . ")<br>";
    echo "POST参数: " . (isset($_POST['test']) ? '正常' : '空') . "<br>";
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function get_upload_error_message($code) {
    $errors = [
        UPLOAD_ERR_OK => '没有错误',
        UPLOAD_ERR_INI_SIZE => '文件超过php.ini中upload_max_filesize设置',
        UPLOAD_ERR_FORM_SIZE => '文件超过表单中MAX_FILE_SIZE设置',
        UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
        UPLOAD_ERR_NO_FILE => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败',
        UPLOAD_ERR_EXTENSION => 'PHP扩展停止了文件上传'
    ];
    return $errors[$code] ?? '未知错误';
}
?>