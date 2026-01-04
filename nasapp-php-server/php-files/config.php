<?php
/**
 * 哨兵模式视频监控 - 配置文件
 */

// 数据库配置（使用SQLite）
define('DB_FILE', '/volume1/web/sentinel/database/sentinel.db');

// 上传目录
define('UPLOAD_DIR', '/volume1/web/sentinel/uploads');

// 缩略图目录
define('THUMBNAIL_DIR', '/volume1/web/sentinel/thumbnails');

// 基础URL（请修改为你的NAS IP）
define('BASE_URL', 'http://shadowext.cn:9665');

// PushDeer 推送配置
define('PUSHDEER_API', 'https://api2.pushdeer.com/message/push');

// 最大文件大小（500MB）
define('MAX_FILE_SIZE', 500 * 1024 * 1024);

// 视频保留时间（7天，单位：秒）
define('VIDEO_RETENTION_TIME', 604800);

// 允许的文件类型
define('ALLOWED_TYPES', ['video/mp4', 'video/avi', 'video/mov', 'video/mkv', 'video/webm']);

// 数据库连接
try {
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

// 创建表（如果不存在）
$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        email TEXT,
        pushKey TEXT,
        fcmToken TEXT,
        monitorDirectory TEXT,
        autoUploadEnabled INTEGER DEFAULT 0,
        deviceId TEXT DEFAULT '',
        licensePlate TEXT DEFAULT '',
        vinCode TEXT DEFAULT '',
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        lastLogin DATETIME
    )
");

// 添加autoUploadEnabled字段（如果不存在）
try {
    $db->exec("ALTER TABLE users ADD COLUMN autoUploadEnabled INTEGER DEFAULT 0");
} catch (Exception $e) {
    // 字段已存在，忽略错误
}

$db->exec("
    CREATE TABLE IF NOT EXISTS videos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        userId INTEGER NOT NULL,
        deviceId TEXT NOT NULL,
        originalName TEXT NOT NULL,
        filename TEXT NOT NULL,
        path TEXT NOT NULL,
        size INTEGER NOT NULL,
        uploadTime DATETIME NOT NULL,
        expireTime DATETIME NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userId) REFERENCES users(id)
    )
");

// 创建目录
$dirs = [UPLOAD_DIR, THUMBNAIL_DIR, dirname(DB_FILE)];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// 创建索引
$db->exec("CREATE INDEX IF NOT EXISTS idx_videos_userId ON videos(userId)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_videos_uploadTime ON videos(uploadTime)");

/**
 * 获取基础URL
 */
function getBaseUrl() {
    return rtrim(BASE_URL, '/');
}

/**
 * 生成缩略图
 */
function generateThumbnail($videoPath, $thumbnailPath) {
    $ffmpeg = '/usr/bin/ffmpeg';
    
    if (!file_exists($ffmpeg)) {
        error_log('ffmpeg不可用，跳过缩略图生成');
        return false;
    }
    
    $command = escapeshellcmd("$ffmpeg -i " . escapeshellarg($videoPath) . " -ss 00:00:01 -vframes 1 -vf scale=320:-1 " . escapeshellarg($thumbnailPath) . " 2>&1");
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        error_log('生成缩略图失败: ' . implode("\n", $output));
        return false;
    }
    
    return true;
}

/**
 * 推送通知
 */
function sendPushNotification($userId, $data) {
    return sendPushDeerNotification($userId, $data);
}

/**
 * 发送 PushDeer 通知
 */
function sendPushDeerNotification($userId, $data) {
    global $db;
    
    $stmt = $db->prepare("SELECT pushKey FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || empty($user['pushKey'])) {
        error_log("用户 {$userId} 的 PushKey 不存在，跳过推送");
        return false;
    }

    $pushKey = $user['pushKey'];
    $title = $data['title'] ?? '哨兵预警';
    $content = $data['body'] ?? '有预警，请立刻查看';
    $desp = $data['desp'] ?? '';

    $postData = [
        'pushkey' => $pushKey,
        'text' => $title,
        'desp' => $desp ?: $content,
        'type' => 'text'
    ];

    $ch = curl_init(PUSHDEER_API);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        error_log("PushDeer 推送成功: " . $response);
        return true;
    } else {
        error_log("PushDeer 推送失败: HTTP {$httpCode} - " . $response);
        return false;
    }
}

/**
 * 返回JSON响应
 */
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// 处理OPTIONS请求（CORS预检）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
    exit;
}
?>