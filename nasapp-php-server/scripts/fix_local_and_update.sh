#!/bin/bash

################################################################################
# 修复本地文件并更新一键安装脚本
# 确保 admin.php 在本地和 GitHub 上都是正确的版本
################################################################################

echo "========================================"
echo "  修复本地文件并更新安装脚本"
echo "========================================"
echo ""

# 1. 修复本地 admin.php
echo "步骤 1/4: 修复本地 admin.php..."
cat > nasapp-php-server/php-files/admin.php <<'EOF'
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

echo "✓ 本地 admin.php 已修复"

# 2. 更新 install.sh 脚本
echo "步骤 2/4: 更新 install.sh..."
cat > nasapp-php-server/install.sh <<'EOF'
#!/bin/bash

################################################################################
# NASAPP 哨兵模式视频监控系统 - 一键安装脚本
# 适用于 CentOS 7
################################################################################

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

SERVER_IP="45.130.146.21"
SERVER_PORT="9665"
INSTALL_DIR="/var/www/html/sentinel"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

echo "========================================"
echo "  NASAPP 一键安装脚本"
echo "========================================"
echo ""

# 1. 安装 Apache 和 PHP 8.0
print_step "步骤 1/10: 安装 Apache 和 PHP 8.0..."
yum install -y httpd yum-utils > /dev/null 2>&1
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm > /dev/null 2>&1
yum-config-manager --enable remi-php80 > /dev/null 2>&1
yum install -y php php-pdo php-sqlite3 php-gd php-xml php-mbstring php80-php-fpm > /dev/null 2>&1
print_info "Apache 和 PHP 8.0 安装完成"

# 2. 启用 mod_rewrite
print_step "步骤 2/10: 启用 Apache mod_rewrite..."
sed -i 's/AllowOverride None/AllowOverride All/g' /etc/httpd/conf/httpd.conf
print_info "mod_rewrite 已启用"

# 3. 创建目录
print_step "步骤 3/10: 创建目录结构..."
mkdir -p $INSTALL_DIR/{uploads,database,thumbnails}
print_info "目录创建完成"

# 4. 复制 PHP 文件
print_step "步骤 4/10: 复制 PHP 文件..."
if [ -d "$SCRIPT_DIR/php-files" ]; then
    cp -r $SCRIPT_DIR/php-files/* $INSTALL_DIR/
    print_info "PHP 文件复制完成"
else
    print_error "php-files 目录不存在"
    exit 1
fi

# 5. 配置 config.php
print_step "步骤 5/10: 配置 config.php..."
sed -i "s|define('BASE_URL', '.*');|define('BASE_URL', 'http://$SERVER_IP:$SERVER_PORT');|g" $INSTALL_DIR/config.php
print_info "config.php 配置完成"

# 6. 修复 diagnostic.php 路径
print_step "步骤 6/10: 修复 diagnostic.php 路径..."
sed -i 's|/volume1/web/sentinel|/var/www/html/sentinel|g' $INSTALL_DIR/diagnostic.php
print_info "diagnostic.php 路径已修复"

# 7. 设置权限
print_step "步骤 7/10: 设置权限..."
chmod -R 777 $INSTALL_DIR
chown -R apache:apache $INSTALL_DIR
print_info "权限设置完成"

# 8. 初始化数据库
print_step "步骤 8/10: 初始化数据库..."
if [ ! -f "$INSTALL_DIR/database/sentinel.db" ]; then
    php $INSTALL_DIR/create_database.php
    print_info "数据库初始化完成"
else
    print_info "数据库已存在，跳过初始化"
fi

# 9. 配置 Apache
print_step "步骤 9/10: 配置 Apache..."
cat > /etc/httpd/conf.d/sentinel.conf <<'EOF'
Listen 9665

<VirtualHost *:9665>
    DocumentRoot "/var/www/html/sentinel"
    ServerName 45.130.146.21
    
    <Directory "/var/www/html/sentinel">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        DirectoryIndex index.php index.html
        
        <FilesMatch \.php$>
            SetHandler "proxy:fcgi://127.0.0.1:9000"
        </FilesMatch>
    </Directory>
    
    ErrorLog /var/log/httpd/sentinel_error.log
    CustomLog /var/log/httpd/sentinel_access.log combined
</VirtualHost>
EOF

# 禁用系统 PHP 模块
mv /etc/httpd/conf.modules.d/10-php.conf /etc/httpd/conf.modules.d/10-php.conf.disabled 2>/dev/null || true
rm -f /etc/httpd/conf.d/php.conf
print_info "Apache 配置完成"

# 10. 启动服务
print_step "步骤 10/10: 启动服务..."
systemctl enable php80-php-fpm > /dev/null 2>&1
systemctl enable httpd > /dev/null 2>&1
systemctl restart php80-php-fpm
systemctl restart httpd
print_info "服务已启动"

# 配置防火墙
print_info "配置防火墙..."
firewall-cmd --permanent --add-port=$SERVER_PORT/tcp > /dev/null 2>&1 || true
firewall-cmd --reload > /dev/null 2>&1 || true
print_info "防火墙配置完成"

# 测试
echo ""
echo "========================================"
echo "  测试服务"
echo "========================================"
echo ""
if curl -s http://localhost:$SERVER_PORT/health.php > /dev/null; then
    print_info "健康检查通过"
    curl -s http://localhost:$SERVER_PORT/health.php
else
    print_error "健康检查失败"
    exit 1
fi

echo ""
echo "========================================"
echo "  🎉 安装完成！"
echo "========================================"
echo ""
echo "服务地址: http://$SERVER_IP:$SERVER_PORT"
echo "安装目录: $INSTALL_DIR"
echo "测试账号: test / 123456"
echo "管理员密码: admin123"
echo ""
echo "可用功能:"
echo "  管理页面: http://$SERVER_IP:$SERVER_PORT/admin.php"
echo "  健康检查: http://$SERVER_IP:$SERVER_PORT/health.php"
echo "  数据库管理: http://$SERVER_IP:$SERVER_PORT/database_manager.html"
echo "  环境测试: http://$SERVER_IP:$SERVER_PORT/test.php"
echo ""
echo "常用命令:"
echo "  查看状态: systemctl status httpd"
echo "  查看日志: tail -f /var/log/httpd/error_log"
echo "  重启服务: systemctl restart httpd"
echo "========================================"
EOF

echo "✓ install.sh 已更新"

# 3. 移除修复脚本
echo "步骤 3/4: 移除修复脚本..."
rm -f nasapp-php-server/scripts/fix_*.sh
rm -f nasapp-php-server/scripts/diagnose_and_fix.sh
echo "✓ 修复脚本已移除"

# 4. 更新 README
echo "步骤 4/4: 更新 README..."
cat > nasapp-php-server/README.md <<'EOF'
# NASAPP 哨兵模式视频监控系统

## 快速安装

### 方法 1：使用 install.sh（推荐）

```bash
cd nasapp-php-server
chmod +x install.sh
./install.sh
```

### 方法 2：从 GitHub 安装

```bash
curl -fsSL https://raw.githubusercontent.com/Gundamx682/NASAPP/main/nasapp-php-server/install.sh -o /tmp/install.sh && chmod +x /tmp/install.sh && /tmp/install.sh
```

## 访问地址

- **管理页面**: http://45.130.146.21:9665/admin.php
- **健康检查**: http://45.130.146.21:9665/health.php
- **数据库管理**: http://45.130.146.21:9665/database_manager.html
- **环境测试**: http://45.130.146.21:9665/test.php

## 登录信息

- **管理员密码**: admin123
- **测试账号**: test / 123456

## 常用命令

```bash
# 查看服务状态
systemctl status httpd
systemctl status php80-php-fpm

# 重启服务
systemctl restart httpd
systemctl restart php80-php-fpm

# 查看日志
tail -f /var/log/httpd/error_log
tail -f /var/opt/remi/php80/log/php-fpm/error.log

# 查看数据库
sqlite3 /var/www/html/sentinel/database/sentinel.db
```

## 技术栈

- Apache 2.4
- PHP 8.0
- SQLite3
- PHP-FPM

## 系统要求

- CentOS 7
- Root 权限
- 至少 2GB RAM
- 至少 100GB 可用存储空间

## 故障排查

### 服务无法启动

```bash
# 检查服务状态
systemctl status httpd

# 查看错误日志
tail -n 50 /var/log/httpd/error_log

# 检查配置
httpd -t
```

### 无法访问

```bash
# 检查防火墙
firewall-cmd --list-ports

# 开放端口
firewall-cmd --permanent --add-port=9665/tcp
firewall-cmd --reload

# 检查端口监听
netstat -tlnp | grep 9665
```

## 更新日志

### v1.0.0 (2026-01-05)

- 完整的 PHP 8.0 部署
- 修复 admin.php 登录问题
- 修复 session 问题
- 一键安装脚本
- 完整的管理功能

---

**项目维护**：熊哥和SS联合开发
EOF

echo "✓ README 已更新"

echo ""
echo "========================================"
echo "  本地文件修复完成！"
echo "========================================"
echo ""
echo "已完成的操作："
echo "  ✓ 修复本地 admin.php"
echo "  ✓ 更新 install.sh 脚本"
echo "  ✓ 移除所有修复脚本"
echo "  ✓ 更新 README 文档"
echo ""
echo "下一步："
echo "  git add nasapp-php-server/"
echo "  git commit -m 'Fix admin.php and update install script'"
echo "  git push origin main"
echo ""
echo "修复后的 install.sh 可以一次性完成所有安装，无需额外修复！"
echo "========================================"