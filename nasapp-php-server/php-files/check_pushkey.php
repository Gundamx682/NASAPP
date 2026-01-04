<?php
/**
 * 检查用户的 PushKey 配置
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$selectedUserId = $_GET['userId'] ?? '';
$users = [];
$userInfo = null;

// 获取所有用户
try {
    $stmt = $db->query("SELECT id, username, pushKey FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

// 检查选中的用户
if (!empty($selectedUserId)) {
    $stmt = $db->prepare("SELECT id, username, pushKey, email, monitorDirectory FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $userInfo = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>检查PushKey配置</title>
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
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #1976D2; }
        .user-info {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #2196F3;
        }
        .user-info h3 { color: #333; margin-bottom: 15px; }
        .user-info p {
            margin: 8px 0;
            color: #555;
        }
        .user-info .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
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
        <h1>🔍 检查PushKey配置</h1>
        
        <?php if (empty($users)): ?>
            <div class="no-users">
                <p>⚠️ 当前没有用户！请先注册一个用户。</p>
            </div>
        <?php else: ?>
            <form method="GET">
                <div class="form-group">
                    <label for="userId">选择用户:</label>
                    <select id="userId" name="userId" required>
                        <option value="">-- 请选择用户 --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                    <?php echo $selectedUserId == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?> 
                                (ID: <?php echo $user['id']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">检查配置</button>
            </form>
        <?php endif; ?>

        <?php if ($userInfo !== null): ?>
            <div class="user-info">
                <h3>用户信息</h3>
                <p><strong>用户ID:</strong> <?php echo $userInfo['id']; ?></p>
                <p><strong>用户名:</strong> <?php echo htmlspecialchars($userInfo['username']); ?></p>
                <p><strong>邮箱:</strong> <?php echo htmlspecialchars($userInfo['email'] ?? '未设置'); ?></p>
                <p><strong>监控目录:</strong> <?php echo htmlspecialchars($userInfo['monitorDirectory'] ?? '未设置'); ?></p>
                <p><strong>PushKey状态:</strong> 
                    <?php if (empty($userInfo['pushKey'])): ?>
                        <span class="status status-error">❌ 未配置</span>
                    <?php else: ?>
                        <span class="status status-success">✅ 已配置</span>
                    <?php endif; ?>
                </p>
                <?php if (!empty($userInfo['pushKey'])): ?>
                    <p><strong>PushKey:</strong> <?php echo substr($userInfo['pushKey'], 0, 20); ?>...</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>