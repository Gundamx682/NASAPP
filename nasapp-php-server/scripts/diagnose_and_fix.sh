#!/bin/bash

################################################################################
# 完整诊断和修复 admin.php 问题
# 自动检测问题并修复
################################################################################

echo "========================================"
echo "  完整诊断和修复 admin.php"
echo "========================================"
echo ""

# 1. 测试简单 PHP 页面
echo "步骤 1/8: 测试 PHP 基础功能..."
cat > /var/www/html/sentinel/test_simple.php <<'EOF'
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Session ID: " . session_id() . "\n";
session_start();
$_SESSION['test'] = 'hello';
echo "Session OK\n";
?>
EOF

echo "=== 测试简单 PHP 页面 ==="
curl -s http://localhost:9665/test_simple.php
echo ""

# 2. 检查 admin.php 语法
echo "步骤 2/8: 检查 admin.php 语法..."
php -l /var/www/html/sentinel/admin.php
echo ""

# 3. 查看 admin.php 前几行
echo "步骤 3/8: 检查 admin.php 配置..."
head -30 /var/www/html/sentinel/admin.php | grep -E "session_start|require_once|error_reporting"
echo ""

# 4. 检查 session 目录
echo "步骤 4/8: 检查 session 目录..."
ls -la /var/opt/remi/php80/lib/php/session/ | head -5
echo ""

# 5. 重新下载并修复 admin.php
echo "步骤 5/8: 重新下载并修复 admin.php..."
curl -fsSL https://raw.githubusercontent.com/Gundamx682/NASAPP/main/nas-setup/php/admin.php -o /var/www/html/sentinel/admin.php.new

# 使用 Python 修复
python3 <<'PYTHON_SCRIPT'
import re

with open('/var/www/html/sentinel/admin.php.new', 'r', encoding='utf-8') as f:
    content = f.read()

# 添加错误报告
lines = content.split('\n')
if not lines[0].startswith('<?php'):
    lines.insert(0, '<?php')

# 在 session_start 后添加错误报告
insert_pos = 0
for i, line in enumerate(lines):
    if 'session_start()' in line and not line.strip().startswith('//'):
        insert_pos = i + 1
        break

if insert_pos > 0:
    lines.insert(insert_pos, "error_reporting(E_ALL);")
    lines.insert(insert_pos + 1, "ini_set('display_errors', 1);")

# 修复 REQUEST_METHOD
content = '\n'.join(lines)
content = re.sub(
    r'\$_SERVER\["REQUEST_METHOD"\]',
    r'isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "GET"',
    content
)

# 修复 POST 检查
content = re.sub(
    r'if \(\$_SERVER\["REQUEST_METHOD"\] === \'POST\'',
    r'if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === \'POST\'',
    content
)

with open('/var/www/html/sentinel/admin.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("✓ admin.php 已修复")
PYTHON_SCRIPT

# 6. 设置权限
echo "步骤 6/8: 设置权限..."
chmod 777 /var/www/html/sentinel/admin.php
chown apache:apache /var/www/html/sentinel/admin.php
echo "✓ 权限已设置"

# 7. 重启服务
echo "步骤 7/8: 重启服务..."
systemctl restart php80-php-fpm
systemctl restart httpd
echo "✓ 服务已重启"

# 8. 测试登录流程
echo "步骤 8/8: 测试登录流程..."
echo ""
echo "=== 测试登录页面 ==="
curl -I http://localhost:9665/admin.php | head -10

echo ""
echo "=== 测试登录 ==="
curl -c /tmp/diag_cookies.txt -X POST http://localhost:9665/admin.php -d "password=admin123" -v 2>&1 | grep -E "HTTP|Location|Set-Cookie"

echo ""
echo "=== 测试保持登录 ==="
curl -b /tmp/diag_cookies.txt -I http://localhost:9665/admin.php | head -10

echo ""
echo "=== 检查 session 文件 ==="
ls -lt /var/opt/remi/php80/lib/php/session/ | head -3

echo ""
echo "=== 检查错误日志 ==="
echo "Apache 错误日志:"
tail -n 5 /var/log/httpd/error_log

echo ""
echo "PHP-FPM 错误日志:"
tail -n 5 /var/opt/remi/php80/log/php-fpm/error.log

echo ""
echo "========================================"
echo "  诊断完成！"
echo "========================================"
echo ""
echo "如果看到 500 错误，请查看上面的错误日志"
echo "如果登录页面正常，请尝试在浏览器中访问"
echo "http://45.130.146.21:9665/admin.php"