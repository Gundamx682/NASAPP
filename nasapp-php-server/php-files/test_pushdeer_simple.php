<?php
/**
 * 简单的 PushDeer 推送测试脚本
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$pushkey = $_GET['pushkey'] ?? '';
$result = null;
$httpCode = null;

if (!empty($pushkey)) {
    $postData = [
        'pushkey' => $pushkey,
        'text' => '🧪 简单测试推送',
        'desp' => "这是一条来自哨兵监控系统的简单测试推送\n\n时间: " . date('Y-m-d H:i:s'),
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

    $result = [
        'success' => $httpCode === 200,
        'httpCode' => $httpCode,
        'response' => $response
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>简单推送测试</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
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
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #0d47a1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 简单推送测试</h1>
        
        <div class="info">
            <p>输入你的 PushKey，点击发送即可测试推送功能。</p>
        </div>

        <form method="GET">
            <div class="form-group">
                <label for="pushkey">PushKey:</label>
                <input type="text" id="pushkey" name="pushkey" 
                       placeholder="例如: PDU1234567890abcdef..." 
                       value="<?php echo htmlspecialchars($pushkey); ?>" required>
            </div>
            <button type="submit">发送测试推送</button>
        </form>

        <?php if ($result !== null): ?>
            <div class="result <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <h3><?php echo $result['success'] ? '✅ 发送成功' : '❌ 发送失败'; ?></h3>
                <p><strong>HTTP状态码:</strong> <?php echo $result['httpCode']; ?></p>
                <p><strong>服务器响应:</strong></p>
                <pre><?php echo htmlspecialchars($result['response']); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>