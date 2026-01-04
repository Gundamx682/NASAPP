#!/bin/bash

################################################################################
# NASAPP 哨兵模式视频监控系统 - 一键安装脚本（全新版）
# 适用于 CentOS 7
# PHP 版本：8.0
# 作者：熊哥和SS联合开发
################################################################################

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# 配置变量
SERVER_IP="45.130.146.21"
SERVER_PORT="9665"
INSTALL_DIR="/var/www/html/sentinel"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_FILES_DIR="$SCRIPT_DIR/php-files"

# 打印函数
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# 检查 root 权限
if [ "$EUID" -ne 0 ]; then
    print_error "请使用 root 用户运行此脚本"
    exit 1
fi

# 显示欢迎信息
echo ""
echo "========================================"
echo "  NASAPP 哨兵模式视频监控系统"
echo "  一键安装脚本（全新版）"
echo "========================================"
echo ""
echo "服务器信息:"
echo "  IP 地址: $SERVER_IP"
echo "  端口: $SERVER_PORT"
echo "  PHP 版本: 8.0"
echo "  安装目录: $INSTALL_DIR"
echo ""
echo "按 Ctrl+C 取消安装，或按 Enter 继续..."
read

# 步骤 1: 检查系统
print_step "步骤 1/10: 检查系统环境..."
if [ ! -f /etc/redhat-release ]; then
    print_error "此脚本仅适用于 CentOS/RHEL 系统"
    exit 1
fi
print_info "系统版本: $(cat /etc/redhat-release)"

# 步骤 2: 安装仓库
print_step "步骤 2/10: 安装软件仓库..."
yum install -y epel-release > /dev/null 2>&1
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm > /dev/null 2>&1
yum-config-manager --enable remi-php80 > /dev/null 2>&1
print_info "仓库安装完成"

# 步骤 3: 安装 Apache 和 PHP 8.0
print_step "步骤 3/10: 安装 Apache 和 PHP 8.0..."
yum install -y httpd php80 php80-php-pdo php80-php-sqlite3 php80-php-gd php80-php-xml php80-php-mbstring > /dev/null 2>&1
print_info "Apache 和 PHP 8.0 安装完成"

# 步骤 4: 配置 PHP 8.0
print_step "步骤 4/10: 配置 PHP 8.0..."
ln -sf /usr/bin/php80 /usr/bin/php
ln -sf /usr/bin/php80 /usr/local/bin/php
print_info "PHP 8.0 符号链接已创建"

# 步骤 5: 配置 Apache
print_step "步骤 5/10: 配置 Apache..."
sed -i 's/AllowOverride None/AllowOverride All/g' /etc/httpd/conf/httpd.conf
print_info "Apache mod_rewrite 已启用"

# 步骤 6: 创建目录结构
print_step "步骤 6/10: 创建目录结构..."
mkdir -p $INSTALL_DIR/{uploads,database,thumbnails}
print_info "目录创建完成"

# 步骤 7: 复制 PHP 文件
print_step "步骤 7/10: 复制 PHP 文件..."
if [ -d "$PHP_FILES_DIR" ]; then
    cp -r $PHP_FILES_DIR/* $INSTALL_DIR/
    print_info "PHP 文件复制完成"
else
    print_error "未找到 php-files 目录"
    print_error "请确保脚本与 php-files 目录在同一位置"
    exit 1
fi

# 步骤 8: 配置 config.php
print_step "步骤 8/10: 配置 config.php..."
cat > $INSTALL_DIR/config.php <<'EOF'
<?php
/**
 * NASAPP 配置文件
 */

// 数据库配置
define('DB_FILE', '/var/www/html/sentinel/database/sentinel.db');
define('DB_PATH', DB_FILE);

// 上传目录配置
define('UPLOAD_DIR', '/var/www/html/sentinel/uploads');
define('THUMBNAIL_DIR', '/var/www/html/sentinel/thumbnails');

// 服务器配置
define('BASE_URL', 'http://45.130.146.21:9665');

// PushDeer 配置
define('PUSHDEER_API', 'https://api2.pushdeer.com/message/push');

// 文件上传配置
define('MAX_FILE_SIZE', 500 * 1024 * 1024);

// 视频保留时间（秒）- 7天
define('VIDEO_RETENTION_TIME', 604800);

// 允许的视频类型
define('ALLOWED_TYPES', 'video/mp4,video/avi,video/mov,video/mkv,video/webm');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告（生产环境关闭）
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 确保必要的目录存在
$dirs = [UPLOAD_DIR, THUMBNAIL_DIR, dirname(DB_FILE)];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// CORS 头设置
if (isset($_SERVER['REQUEST_METHOD'])) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // 处理 OPTIONS 请求
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
EOF

php -l $INSTALL_DIR/config.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    print_info "config.php 配置完成"
else
    print_error "config.php 语法错误"
    exit 1
fi

# 步骤 9: 设置权限
print_step "步骤 9/10: 设置权限..."
chmod -R 777 $INSTALL_DIR
chown -R apache:apache $INSTALL_DIR
print_info "权限设置完成"

# 步骤 10: 配置服务
print_step "步骤 10/10: 配置并启动服务..."

# 创建 Apache 虚拟主机配置
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

# 启动 PHP-FPM
systemctl start php80-php-fpm
systemctl enable php80-php-fpm > /dev/null 2>&1
print_info "PHP-FPM 服务已启动"

# 启动 Apache
systemctl restart httpd
systemctl enable httpd > /dev/null 2>&1
sleep 3
print_info "Apache 服务已启动"

# 配置防火墙
print_info "配置防火墙..."
firewall-cmd --permanent --add-port=9665/tcp > /dev/null 2>&1 || true
firewall-cmd --reload > /dev/null 2>&1 || true
print_info "防火墙配置完成"

# 初始化数据库
print_info "初始化数据库..."
if [ ! -f "$INSTALL_DIR/database/sentinel.db" ]; then
    php $INSTALL_DIR/create_database.php
    print_info "数据库初始化完成"
else
    print_warn "数据库已存在，跳过初始化"
fi

# 测试服务
echo ""
echo "========================================"
echo "  测试服务"
echo "========================================"
if curl -s http://localhost:9665/health.php > /dev/null; then
    print_info "健康检查通过"
    curl -s http://localhost:9665/health.php
else
    print_error "健康检查失败"
    systemctl status httpd
    exit 1
fi

# 显示 PHP 版本
echo ""
echo "========================================"
echo "  PHP 版本信息"
echo "========================================"
php -v

# 显示安装完成信息
echo ""
echo "========================================"
echo "  🎉 安装完成！"
echo "========================================"
echo ""
echo "服务信息:"
echo "  服务器地址: http://$SERVER_IP:$SERVER_PORT"
echo "  安装目录: $INSTALL_DIR"
echo "  PHP 版本: 8.0"
echo "  测试账号: test / 123456"
echo ""
echo "可用功能:"
echo "  健康检查: http://$SERVER_IP:$SERVER_PORT/health.php"
echo "  管理页面: http://$SERVER_IP:$SERVER_PORT/admin.php"
echo "  数据库管理: http://$SERVER_IP:$SERVER_PORT/database_manager.html"
echo "  环境测试: http://$SERVER_IP:$SERVER_PORT/test.php"
echo "  系统诊断: http://$SERVER_IP:$SERVER_PORT/diagnostic.php"
echo ""
echo "常用命令:"
echo "  查看状态: systemctl status httpd"
echo "  查看 PHP-FPM: systemctl status php80-php-fpm"
echo "  查看日志: tail -f /var/log/httpd/error_log"
echo "  重启服务: systemctl restart httpd"
echo "  查看 PHP: php -v"
echo ""
echo "管理功能:"
echo "  - 用户管理（查看所有用户）"
echo "  - 视频管理（查看所有视频）"
echo "  - 数据库可视化管理"
echo "  - 系统诊断工具"
echo "  - 测试工具集"
echo ""
echo "========================================"