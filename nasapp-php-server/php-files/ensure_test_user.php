<?php
/**
 * 确保测试用户存在
 * 用于测试工具自动创建测试账户
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 检查测试用户是否存在
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute(['testuser']);
        $user = $stmt->fetch();

        if ($user) {
            $result = [
                'success' => true,
                'message' => '测试用户已存在',
                'userId' => $user['id']
            ];
        } else {
            // 创建测试用户
            $stmt = $db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->execute(['testuser', 'test123', 'test@example.com']);
            $userId = $db->lastInsertId();
            
            $result = [
                'success' => true,
                'message' => '测试用户创建成功',
                'userId' => $userId
            ];
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// 检查当前状态
$stmt = $db->query("SELECT id, username, email FROM users WHERE username = 'testuser'");
$testUser = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>确保测试用户</title>
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
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #0d47a1;
        }
        .user-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 确保测试用户</h1>
        
        <div class="info">
            <p>此工具用于确保测试用户存在，以便其他测试工具可以正常运行。</p>
        </div>

        <?php if ($testUser): ?>
            <div class="user-card">
                <h3>✅ 测试用户已存在</h3>
                <p><strong>用户名:</strong> testuser</p>
                <p><strong>密码:</strong> test123</p>
                <p><strong>邮箱:</strong> <?php echo htmlspecialchars($testUser['email']); ?></p>
                <p><strong>用户ID:</strong> <?php echo $testUser['id']; ?></p>
            </div>
        <?php else: ?>
            <div class="user-card" style="border-left-color: #ff9800;">
                <h3>⚠️ 测试用户不存在</h3>
                <p>点击下方按钮创建测试用户。</p>
            </div>
        <?php endif; ?>

        <form method="POST">
            <button type="submit"><?php echo $testUser ? '重新创建测试用户' : '创建测试用户'; ?></button>
        </form>

        <?php if ($result !== null): ?>
            <div class="result success">
                <h3>✅ 操作成功</h3>
                <p><?php echo $result['message']; ?></p>
                <p>用户ID: <?php echo $result['userId']; ?></p>
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