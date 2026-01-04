<?php
/**
 * 文件扫描诊断工具
 * 帮助诊断为什么找不到视频文件
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$userId = $_GET['userId'] ?? '';
$scanResult = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userId = $_POST['userId'] ?? '';
        
        if (empty($userId)) {
            throw new Exception('请选择用户');
        }
        
        // 获取用户信息
        $stmt = $db->prepare("SELECT id, username, monitorDirectory FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('用户不存在');
        }
        
        $monitorDirectory = $user['monitorDirectory'];
        
        if (empty($monitorDirectory)) {
            throw new Exception('用户未设置监控目录');
        }
        
        // 扫描文件
        $scanResult = [
            'userId' => $userId,
            'username' => $user['username'],
            'monitorDirectory' => $monitorDirectory,
            'directoryExists' => false,
            'directoryReadable' => false,
            'totalFiles' => 0,
            'videoFiles' => [],
            'warnFiles' => [],
            'recentFiles' => [],
            'serviceStartTime' => time() * 1000 - 300000, // 假设服务5分钟前启动
            'issues' => []
        ];
        
        // 检查目录是否存在
        if (strpos($monitorDirectory, 'content://') === 0) {
            // DocumentFile URI
            $scanResult['directoryExists'] = true;
            $scanResult['directoryType'] = 'DocumentFile URI';
        } else {
            // 普通文件路径
            $scanResult['directoryExists'] = is_dir($monitorDirectory);
            $scanResult['directoryReadable'] = is_readable($monitorDirectory);
            $scanResult['directoryType'] = '普通文件路径';
            
            if ($scanResult['directoryExists'] && $scanResult['directoryReadable']) {
                // 扫描文件
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($monitorDirectory)
                );
                
                $now = time();
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $scanResult['totalFiles']++;
                        $fileName = $file->getFilename();
                        $fileSize = $file->getSize();
                        $fileTime = $file->getMTime();
                        
                        // 检查是否是视频文件
                        $isVideo = preg_match('/\.(mp4|avi|mov|mkv|flv|3gp)$/i', $fileName);
                        if ($isVideo) {
                            $scanResult['videoFiles'][] = [
                                'name' => $fileName,
                                'size' => $fileSize,
                                'sizeFormatted' => formatSize($fileSize),
                                'time' => $fileTime,
                                'timeFormatted' => date('Y-m-d H:i:s', $fileTime),
                                'isWarn' => stripos($fileName, 'warn') !== false,
                                'isRecent' => ($now - $fileTime) < 3600 // 1小时内
                            ];
                            
                            if (stripos($fileName, 'warn') !== false) {
                                $scanResult['warnFiles'][] = $fileName;
                            }
                            
                            if (($now - $fileTime) < 3600) {
                                $scanResult['recentFiles'][] = $fileName;
                            }
                        }
                    }
                }
            }
        }
        
        // 分析问题
        if (!$scanResult['directoryExists']) {
            $scanResult['issues'][] = '❌ 监控目录不存在';
        } elseif (!$scanResult['directoryReadable']) {
            $scanResult['issues'][] = '❌ 监控目录不可读';
        } elseif (empty($scanResult['videoFiles'])) {
            $scanResult['issues'][] = '❌ 目录中没有视频文件';
        } elseif (empty($scanResult['warnFiles'])) {
            $scanResult['issues'][] = '⚠️ 没有包含"warn"的视频文件（只处理包含warn的文件）';
            $scanResult['issues'][] = '💡 提示：当前有 ' . count($scanResult['videoFiles']) . ' 个视频文件，但都不包含"warn"';
        } else {
            $scanResult['issues'][] = '✅ 找到 ' . count($scanResult['warnFiles']) . ' 个包含warn的视频文件';
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// 获取所有用户
$users = [];
try {
    $stmt = $db->query("SELECT id, username, monitorDirectory FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文件扫描诊断</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #1976D2; }
        .result {
            margin-top: 20px;
        }
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }
        .info-card h3 { color: #333; margin-bottom: 10px; }
        .info-card p { color: #666; margin: 5px 0; }
        .issues {
            margin-top: 15px;
        }
        .issue-item {
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 4px;
            background: #fff3cd;
            color: #856404;
        }
        .file-list {
            margin-top: 20px;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #ddd;
        }
        .file-item.warn { border-left-color: #ff9800; }
        .file-item.recent { border-left-color: #4CAF50; }
        .file-name { font-weight: 600; color: #333; }
        .file-meta { color: #666; font-size: 13px; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge-warn { background: #fff3e0; color: #f57c00; }
        .badge-recent { background: #e8f5e9; color: #2e7d32; }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-info { background: #e3f2fd; color: #0d47a1; }
        .no-users {
            background: #fff3cd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 文件扫描诊断</h1>
        
        <div class="alert alert-info">
            <p><strong>功能说明：</strong>此工具帮助诊断为什么APP找不到视频文件。它会扫描用户的监控目录，分析文件情况，并给出诊断建议。</p>
        </div>
        
        <?php if (empty($users)): ?>
            <div class="no-users">
                <p>⚠️ 当前没有用户！请先注册一个用户。</p>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="userId">选择用户:</label>
                    <select id="userId" name="userId" required>
                        <option value="">-- 请选择用户 --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                    <?php echo $userId == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?> 
                                (ID: <?php echo $user['id']; ?>)
                                <?php if (!empty($user['monitorDirectory'])): ?>
                                    - 已设置监控目录
                                <?php else: ?>
                                    - 未设置监控目录
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">扫描目录</button>
            </form>
        <?php endif; ?>

        <?php if ($error !== null): ?>
            <div class="alert alert-error">
                <h3>❌ 发生错误</h3>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($scanResult !== null): ?>
            <div class="result">
                <div class="info-card">
                    <h3>📋 扫描结果</h3>
                    <p><strong>用户:</strong> <?php echo htmlspecialchars($scanResult['username']); ?></p>
                    <p><strong>监控目录:</strong> <?php echo htmlspecialchars($scanResult['monitorDirectory']); ?></p>
                    <p><strong>目录类型:</strong> <?php echo $scanResult['directoryType']; ?></p>
                    <p><strong>目录存在:</strong> <?php echo $scanResult['directoryExists'] ? '✅ 是' : '❌ 否'; ?></p>
                    <?php if (isset($scanResult['directoryReadable'])): ?>
                        <p><strong>目录可读:</strong> <?php echo $scanResult['directoryReadable'] ? '✅ 是' : '❌ 否'; ?></p>
                    <?php endif; ?>
                    <p><strong>总文件数:</strong> <?php echo $scanResult['totalFiles']; ?></p>
                    <p><strong>视频文件数:</strong> <?php echo count($scanResult['videoFiles']); ?></p>
                    <p><strong>包含warn的视频:</strong> <?php echo count($scanResult['warnFiles']); ?></p>
                </div>
                
                <div class="issues">
                    <h3>🔍 诊断结果</h3>
                    <?php foreach ($scanResult['issues'] as $issue): ?>
                        <div class="issue-item"><?php echo $issue; ?></div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!empty($scanResult['videoFiles'])): ?>
                    <div class="file-list">
                        <h3>📹 视频文件列表</h3>
                        <?php foreach ($scanResult['videoFiles'] as $file): ?>
                            <div class="file-item <?php echo $file['isWarn'] ? 'warn' : ''; ?> <?php echo $file['isRecent'] ? 'recent' : ''; ?>">
                                <div>
                                    <div class="file-name"><?php echo htmlspecialchars($file['name']); ?></div>
                                    <div class="file-meta">
                                        大小: <?php echo $file['sizeFormatted']; ?> | 
                                        时间: <?php echo $file['timeFormatted']; ?>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($file['isWarn']): ?>
                                        <span class="badge badge-warn">Warn</span>
                                    <?php endif; ?>
                                    <?php if ($file['isRecent']): ?>
                                        <span class="badge badge-recent">最近</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>