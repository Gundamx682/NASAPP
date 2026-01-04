<?php
/**
 * PushDeer 推送测试工具
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$pushkey = $_GET['pushkey'] ?? '';

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PushDeer 推送测试</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📱 PushDeer 推送测试</h1>

        <div class="info">
            <h3>📋 使用说明</h3>
            <ol>
                <li>访问 <a href="https://www.pushdeer.com/" target="_blank">PushDeer 官网</a> 获取 PushKey</li>
                <li>在下方输入框中输入你的 PushKey</li>
                <li>点击"发送测试推送"按钮</li>
                <li>检查你的微信是否收到测试消息</li>
            </ol>
        </div>

        <form method="GET">
            <div class="form-group">
                <label for="pushkey">PushKey:</label>
                <input type="text" id="pushkey" name="pushkey" placeholder="请输入你的 PushKey，例如：PDU1234567890abcdef..." value="<?php echo htmlspecialchars($pushkey); ?>">
            </div>
            <button type="submit">发送测试推送</button>
        </form>

        <?php
        if (!empty($pushkey)) {
            $postData = [
                'pushkey' => $pushkey,
                'text' => '🧪 PushDeer 测试推送',
                'desp' => "这是一条来自哨兵监控系统的测试推送\n\n如果你收到这条消息，说明 PushDeer 配置成功！\n\n时间: " . date('Y-m-d H:i:s'),
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

            echo '<div class="result ' . ($httpCode === 200 ? 'success' : 'error') . '" style="display: block;">';
            echo '<h3>' . ($httpCode === 200 ? '✅ 发送成功' : '❌ 发送失败') . '</h3>';
            echo '<p><strong>HTTP 状态码:</strong> ' . $httpCode . '</p>';
            echo '<p><strong>服务器响应:</strong></p>';
            echo '<pre>' . htmlspecialchars($response) . '</pre>';
            echo '</div>';

            if ($httpCode === 200) {
                echo '<div class="info">';
                echo '<h3>🎉 配置成功！</h3>';
                echo '<p>现在你可以在 APP 的设置中保存这个 PushKey，当有新视频上传时，你会收到推送通知。</p>';
                echo '</div>';
            }
        }
        ?>

        <div class="info">
            <h3>🔧 配置 API 地址</h3>
            <p>当前 PushDeer API 地址: <code><?php echo PUSHDEER_API; ?></code></p>
            <p>如果需要自托管 PushDeer，请修改 <code>config.php</code> 中的 <code>PUSHDEER_API</code> 常量。</p>
        </div>
    </div>
</body>
</html>
?>