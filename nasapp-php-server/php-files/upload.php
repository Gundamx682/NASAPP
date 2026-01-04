<?php
/**
 * 视频上传接口
 */

require_once 'config.php';

try {
    // 检查请求方法
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("上传失败: 非POST请求");
        jsonResponse(false, '只支持POST请求');
    }

    // 记录请求信息
    $contentType = $_SERVER['CONTENT_TYPE'] ?? 'unknown';
    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
    error_log("收到上传请求 - Content-Type: $contentType, Content-Length: $contentLength");
    error_log("POST数据: " . json_encode($_POST));
    error_log("FILES数据: " . json_encode(array_keys($_FILES)));

    // 获取参数
    $userId = $_POST['userId'] ?? '';
    $deviceId = $_POST['deviceId'] ?? '';
    $timestamp = $_POST['timestamp'] ?? time();

    // 验证参数
    if (empty($userId) || empty($deviceId)) {
        error_log("参数验证失败 - userId: " . var_export($userId, true) . ", deviceId: " . var_export($deviceId, true));
        error_log("完整的POST数据: " . json_encode($_POST));
        error_log("完整的FILES数据: " . json_encode($_FILES));

        // 检查是否是PHP配置限制导致的问题
        if (empty($_POST) && $contentLength > 0) {
            $postMaxSize = ini_get('post_max_size');
            $uploadMaxSize = ini_get('upload_max_filesize');
            error_log("警告: POST数据为空但请求有数据，可能是PHP配置限制。");
            error_log("当前配置: post_max_size=$postMaxSize, upload_max_filesize=$uploadMaxSize");
            jsonResponse(false, "请求体过大，超过服务器限制（post_max_size={$postMaxSize}, upload_max_filesize={$uploadMaxSize}）。请联系管理员修改PHP配置。");
        }

        jsonResponse(false, '缺少必要参数: userId和deviceId');
    }
    
    // 验证用户是否存在，并获取用户名和 pushKey
    $stmt = $db->prepare("SELECT id, username, pushKey, licensePlate, vinCode FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, '用户不存在，请先登录');
    }

    $username = $user['username'];
    $userPushKey = $user['pushKey'];
    $licensePlate = $user['licensePlate'] ?? '';
    $vinCode = $user['vinCode'] ?? '';
    
    // 检查文件
    if (!isset($_FILES['video'])) {
        jsonResponse(false, '没有上传文件');
    }
    
    $file = $_FILES['video'];
    
    // 检查上传错误
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => '文件超过php.ini中upload_max_filesize设置',
            UPLOAD_ERR_FORM_SIZE => '文件超过表单中MAX_FILE_SIZE设置',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => 'PHP扩展停止了文件上传'
        ];
        jsonResponse(false, $errorMessages[$file['error']] ?? '上传错误');
    }
    
    // 检查文件类型
    if (!in_array($file['type'], ALLOWED_TYPES)) {
        jsonResponse(false, '不支持的文件类型，只支持视频文件');
    }
    
    // 检查文件大小
    if ($file['size'] > MAX_FILE_SIZE) {
        jsonResponse(false, '文件大小超过限制（最大' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB）');
    }
    
    // 创建用户目录（使用用户名）
    $userDir = UPLOAD_DIR . '/' . $username;
    if (!file_exists($userDir)) {
        mkdir($userDir, 0777, true);
    }
    
    // 生成文件名
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'sentinel_' . $timestamp . '_' . uniqid() . '.' . $ext;
    $filepath = $userDir . '/' . $filename;
    
    // 移动文件
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        jsonResponse(false, '文件上传失败');
    }
    
    // 计算过期时间（timestamp 是毫秒，需要转换为秒）
    $timestampSeconds = $timestamp / 1000;
    $uploadTime = date('Y-m-d H:i:s', $timestampSeconds);
    $expireTime = date('Y-m-d H:i:s', $timestampSeconds + VIDEO_RETENTION_TIME);
    
    error_log("时间戳转换: 客户端timestamp=$timestamp (毫秒), 转换后timestampSeconds=$timestampSeconds (秒), uploadTime=$uploadTime");
    
    // 保存到数据库
    $stmt = $db->prepare("
        INSERT INTO videos (userId, deviceId, originalName, filename, path, size, uploadTime, expireTime)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $userId,
        $deviceId,
        $file['name'],
        $filename,
        $filepath,
        $file['size'],
        $uploadTime,
        $expireTime
    ]);
    
    $videoId = $db->lastInsertId();
    
    error_log("[$uploadTime] 视频上传成功: userId=$userId, videoId=$videoId, file=$filename, size=$file[size]");
    
    // 异步生成缩略图
    generateThumbnail($filepath, THUMBNAIL_DIR . '/' . $videoId . '.jpg');

    // 推送通知（使用 PushDeer）
    if ($userPushKey) {
        // 使用数据库中保存的 uploadTime（已经是正确的格式）
        $message = "🚨 哨兵模式预警\n\n" .
                   "检测到车辆异常\n\n" .
                   "车牌号: " . (!empty($licensePlate) ? $licensePlate : "未设置") . "\n" .
                   "车架号: " . (!empty($vinCode) ? $vinCode : "未设置") . "\n\n" .
                   "时间: {$uploadTime}\n" .
                   "文件名: {$file['name']}\n" .
                   "文件大小: " . number_format($file['size'] / 1024 / 1024, 2) . " MB\n\n" .
                   "熊哥和SS联合开发制作测试\n\n" .
                   "点击下载: " . getBaseUrl() . '/video.php?id=' . $videoId;

        sendPushNotification($userId, [
            'title' => '哨兵模式预警',
            'body' => '检测到车辆异常',
            'desp' => $message,
            'videoId' => $videoId,
            'videoUrl' => getBaseUrl() . '/video.php?id=' . $videoId
        ]);
    } else {
        error_log("[" . date('Y-m-d H:i:s') . "] 用户 {$userId} 未配置 PushKey，跳过推送通知");
    }
    
    // 生成正确的 URL
    $baseUrl = getBaseUrl();
    jsonResponse(true, '上传成功', [
        'videoId' => $videoId,
        'videoUrl' => $baseUrl . '/video.php?id=' . $videoId,
        'thumbnailUrl' => $baseUrl . '/thumbnails/' . $videoId . '.jpg',
        'uploadTime' => strtotime($uploadTime) * 1000,  // 转换为毫秒时间戳
        'expireTime' => strtotime($expireTime) * 1000,  // 转换为毫秒时间戳
        'message' => '上传成功'
    ]);
    
} catch (Exception $e) {
    error_log('上传失败: ' . $e->getMessage());
    jsonResponse(false, '上传失败: ' . $e->getMessage());
}
?>