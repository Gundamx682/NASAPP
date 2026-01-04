#!/bin/bash

################################################################################
# 直接修复 admin.php（不依赖 GitHub）
# 在服务器上直接修复所有问题
################################################################################

echo "========================================"
echo "  直接修复 admin.php"
echo "========================================"
echo ""

# 1. 创建简化版的 admin.php
echo "步骤 1/3: 创建修复后的 admin.php..."
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
    // 默认密码
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
            .success {
                background: #d4edda;
                color: #155724;
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
            </table>
        </div>
    </div>
</body>
</html>
EOF

echo "✓ admin.php 已创建"

# 2. 设置权限
echo "步骤 2/3: 设置权限..."
chmod 777 /var/www/html/sentinel/admin.php
chown apache:apache /var/www/html/sentinel/admin.php
echo "✓ 权限已设置"

# 3. 测试
echo "步骤 3/3: 测试..."
echo ""
echo "=== 测试登录页面 ==="
curl -I http://localhost:9665/admin.php | head -10

echo ""
echo "=== 测试登录 ==="
curl -c /tmp/final_cookies.txt -X POST http://localhost:9665/admin.php -d "password=admin123" -v 2>&1 | grep -E "HTTP|Location|Set-Cookie"

echo ""
echo "=== 测试保持登录 ==="
curl -b /tmp/final_cookies.txt -I http://localhost:9665/admin.php | head -10

echo ""
echo "========================================"
echo "  修复完成！"
echo "========================================"
echo ""
echo "请在浏览器中访问："
echo "http://45.130.146.21:9665/admin.php"
echo ""
echo "默认密码：admin123"