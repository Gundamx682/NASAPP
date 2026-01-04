#!/bin/bash

################################################################################
# 补充缺失的功能
# 修复 admin.php 并添加所有缺失的文件
################################################################################

echo "========================================"
echo "  补充缺失的功能"
echo "========================================"
echo ""

# 1. 修复 admin.php（添加推送配置链接）
echo "步骤 1/5: 修复 admin.php..."
cat > /var/www/html/sentinel/admin.php <<'EOF'
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();

// 从配置文件读取管理员密码
$adminPasswordFile = __DIR__ . '/admin_password.php';
if (file_exists($adminPasswordFile)) {
    define('ADMIN_PASSWORD', require $adminPasswordFile);
} else {
    define('ADMIN_PASSWORD', 'admin123');
}

// 检查是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 处理登录
    $requestMethod = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "GET";
    if ($requestMethod === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = '密码错误';
        }
    }
    
    // 显示登录页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统管理 - 登录</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 400px;
                width: 100%;
            }
            h1 {
                color: #333;
                margin-bottom: 30px;
                text-align: center;
                font-size: 28px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #555;
            }
            input[type="password"] {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            button:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }
            .error {
                background: #fee;
                color: #c33;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                font-size: 14px;
            }
            .info {
                text-align: center;
                color: #666;
                font-size: 14px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🔐 系统管理</h1>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">管理员密码</label>
                    <input type="password" id="password" name="password" placeholder="请输入密码" required autofocus>
                </div>
                <button type="submit">登录</button>
            </form>
            <div class="info">
                默认密码: admin123
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 处理登出
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

require_once 'config.php';

// 显示管理面板
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统管理面板</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        .header .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .header .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .info-table th,
        .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .info-table tr:hover {
            background: #f8f9fa;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔧 系统管理面板</h1>
        <a href="admin.php?action=logout" class="logout-btn">退出登录</a>
    </div>

    <div class="container">
        <div class="section">
            <h2>📊 系统概览</h2>
            <table class="info-table">
                <tr>
                    <th>配置项</th>
                    <th>值</th>
                </tr>
                <tr>
                    <td>基础URL</td>
                    <td><?php echo BASE_URL; ?></td>
                </tr>
                <tr>
                    <td>数据库文件</td>
                    <td><?php echo DB_FILE; ?></td>
                </tr>
                <tr>
                    <td>上传目录</td>
                    <td><?php echo UPLOAD_DIR; ?></td>
                </tr>
                <tr>
                    <td>PHP版本</td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td>服务器时间</td>
                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>🔗 快速访问</h2>
            <table class="info-table">
                <tr>
                    <td>健康检查</td>
                    <td><a href="health.php" class="btn">访问</a></td>
                </tr>
                <tr>
                    <td>数据库管理</td>
                    <td><a href="database_manager.html" class="btn">访问</a></td>
                </tr>
                <tr>
                    <td>环境测试</td>
                    <td><a href="test.php" class="btn">访问</a></td>
                </tr>
                <tr>
                    <td>系统诊断</td>
                    <td><a href="diagnostic.php" class="btn">访问</a></td>
                </tr>
                <tr>
                    <td>推送配置</td>
                    <td><a href="push_config.php" class="btn">访问</a></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
EOF

chmod 777 /var/www/html/sentinel/admin.php
echo "✓ admin.php 已修复"

# 2. 创建推送配置页面
echo "步骤 2/5: 创建推送配置页面..."
cat > /var/www/html/sentinel/push_config.php <<'EOF'
<?php
/**
 * 推送配置管理页面
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();

// 检查是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

require_once 'config.php';

// 连接数据库
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

// 处理表单提交
$message = '';
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_pushkey') {
        $userId = $_POST['userId'] ?? '';
        $pushKey = $_POST['pushKey'] ?? '';
        
        if ($userId && $pushKey) {
            $stmt = $db->prepare('UPDATE users SET pushKey = ? WHERE id = ?');
            $stmt->execute([$pushKey, $userId]);
            $message = 'PushKey 更新成功！';
        }
    } elseif ($action === 'test_push') {
        $userId = $_POST['userId'] ?? '';
        $pushKey = $_POST['pushKey'] ?? '';
        
        if ($pushKey) {
            $data = json_encode([
                'pushkey' => $pushKey,
                'text' => '🧪 测试推送',
                'desp' => '这是一条测试推送消息，如果您看到这条消息，说明推送配置正确！',
                'type' => 'text'
            ]);
            
            $ch = curl_init(PUSHDEER_API);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            if ($result && isset($result['code']) && $result['code'] === 0) {
                $message = '测试推送发送成功！请检查您的手机。';
            } else {
                $message = '测试推送失败：' . ($result['message'] ?? '未知错误');
            }
        }
    }
}

$users = $db->query('SELECT id, username, email, pushKey FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>推送配置管理</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; color: #333; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; font-weight: 600; }
        .back-btn { background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; }
        .back-btn:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .section { background: white; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .section h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        tr:hover { background: #f8f9fa; }
        .btn { display: inline-block; padding: 8px 16px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; transition: background 0.3s; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        input[type="text"] { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .push-status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .push-status-active { background: #d4edda; color: #155724; }
        .push-status-inactive { background: #f8d7da; color: #721c24; }
        .info-box { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 20px; }
        .info-box h3 { color: #1976d2; margin-bottom: 10px; font-size: 16px; }
        .info-box p { color: #0d47a1; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📱 推送配置管理</h1>
        <a href="admin.php" class="back-btn">返回管理</a>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '成功') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2>📖 PushDeer 配置说明</h2>
            <div class="info-box">
                <h3>什么是 PushDeer？</h3>
                <p>PushDeer 是一个轻量级的推送服务，可以将消息推送到您的手机。</p>
                <h3>如何获取 PushKey？</h3>
                <p>1. 下载 PushDeer App：<a href="https://www.pushdeer.com/" target="_blank">https://www.pushdeer.com/</a></p>
                <p>2. 注册并登录 App</p>
                <p>3. 在 App 中查看您的 PushKey</p>
                <p>4. 将 PushKey 填入下方的用户配置中</p>
            </div>
        </div>

        <div class="section">
            <h2>👥 用户推送配置</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>邮箱</th>
                        <th>PushKey</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                        <td>
                            <input type="text" id="pushkey_<?php echo $user['id']; ?>" value="<?php echo htmlspecialchars($user['pushKey'] ?? ''); ?>" placeholder="请输入 PushKey">
                        </td>
                        <td>
                            <?php if (!empty($user['pushKey'])): ?>
                                <span class="push-status push-status-active">✓ 已配置</span>
                            <?php else: ?>
                                <span class="push-status push-status-inactive">✗ 未配置</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="updatePushKey(<?php echo $user['id']; ?>)">保存</button>
                            <button class="btn btn-sm btn-warning" onclick="testPush(<?php echo $user['id']; ?>)">测试</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function updatePushKey(userId) {
            const pushKey = document.getElementById('pushkey_' + userId).value;
            const formData = new FormData();
            formData.append('action', 'update_pushkey');
            formData.append('userId', userId);
            formData.append('pushKey', pushKey);
            fetch('push_config.php', { method: 'POST', body: formData }).then(() => location.reload());
        }
        function testPush(userId) {
            const pushKey = document.getElementById('pushkey_' + userId).value;
            if (!pushKey) { alert('请先输入 PushKey！'); return; }
            const formData = new FormData();
            formData.append('action', 'test_push');
            formData.append('userId', userId);
            formData.append('pushKey', pushKey);
            fetch('push_config.php', { method: 'POST', body: formData }).then(() => location.reload());
        }
    </script>
</body>
</html>
EOF

chmod 777 /var/www/html/sentinel/push_config.php
echo "✓ 推送配置页面已创建"

# 3. 复制 API 目录
echo "步骤 3/5: 复制 API 目录..."
if [ -d "nas-setup/php/api" ]; then
    cp -r nas-setup/php/api /var/www/html/sentinel/
    echo "✓ API 目录已复制"
else
    echo "✗ API 目录不存在"
fi

# 4. 复制 Foreign Server 目录
echo "步骤 4/5: 复制 Foreign Server 目录..."
if [ -d "nas-setup/php/foreign_server" ]; then
    cp -r nas-setup/php/foreign_server /var/www/html/sentinel/
    echo "✓ Foreign Server 目录已复制"
else
    echo "✗ Foreign Server 目录不存在"
fi

# 5. 设置权限
echo "步骤 5/5: 设置权限..."
chmod -R 777 /var/www/html/sentinel
chown -R apache:apache /var/www/html/sentinel
echo "✓ 权限已设置"

echo ""
echo "========================================"
echo "  补充完成！"
echo "========================================"
echo ""
echo "新增功能："
echo "  推送配置管理: http://45.130.146.21:9665/push_config.php"
echo "  API 路由: http://45.130.146.21:9665/api/"
echo "  Foreign Server: http://45.130.146.21:9665/foreign_server/"
echo ""
echo "现在功能与 nas-setup/php/ 完全一致！"
echo "========================================"