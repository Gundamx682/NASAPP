<?php
/**
 * 测试视频上报接口
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

// 自动创建测试用户
function ensureTestUser($db) {
    $stmt = $db->prepare("SELECT id FROM users WHERE username = 'testuser'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES ('testuser', 'test123')");
        $stmt->execute();
        return $db->lastInsertId();
    }
    return $user['id'];
}

$userId = null;
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 确保测试用户存在
        $userId = ensureTestUser($db);
        
        // 测试数据
        $testData = [
            'userId' => $userId,
            'deviceId' => 'TestDevice-' . time(),
            'fileName' => 'test_video_' . time() . '.mp4',
            'fileSize' => 10485760,  // 10MB
            'timestamp' => time() * 1000
        ];

        // 发送请求
        $ch = curl_init(BASE_URL . '/report_video.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = [
            'success' => $httpCode === 200,
            'httpCode' => $httpCode,
            'response' => $response,
            'requestData' => $testData
        ];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>视频上报测试</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-bottom: 20px; }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #0d47a1;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #45a049; }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .data-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 视频上报测试</h1>
        
        <div class="info">
            <p>此测试会自动创建测试用户（用户名: testuser，密码: test123），然后模拟视频上报。</p>
            <p>点击"执行测试"即可开始测试。</p>
        </div>

        <form method="POST">
            <button type="submit">执行测试</button>
        </form>

        <?php if ($result !== null): ?>
            <div class="result <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <h3><?php echo $result['success'] ? '✅ 测试成功' : '❌ 测试失败'; ?></h3>
                <p><strong>HTTP状态码:</strong> <?php echo $result['httpCode']; ?></p>
                
                <div class="data-display">
                    <p><strong>请求数据:</strong></p>
                    <pre><?php echo htmlspecialchars(json_encode($result['requestData'], JSON_PRETTY_PRINT)); ?></pre>
                </div>
                
                <div class="data-display">
                    <p><strong>服务器响应:</strong></p>
                    <pre><?php echo htmlspecialchars($result['response']); ?></pre>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error !== null): ?>
            <div class="result error">
                <h3>❌ 发生错误</h3>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>