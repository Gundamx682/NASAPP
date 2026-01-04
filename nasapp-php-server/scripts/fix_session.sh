#!/bin/bash

################################################################################
# 修复 Session 问题
# 解决登录后刷新无法保持登录状态的问题
################################################################################

echo "========================================"
echo "  修复 Session 问题"
echo "========================================"
echo ""

# 1. 检查并修复 session 目录
echo "步骤 1/5: 配置 session 目录..."
mkdir -p /var/lib/php/session
chmod 777 /var/lib/php/session
chown -R apache:apache /var/lib/php/session
echo "✓ Session 目录已配置"

# 2. 确保 PHP-FPM session 配置正确
echo "步骤 2/5: 配置 PHP-FPM session..."
sed -i 's|;php_value\[session.save_handler\]|php_value[session.save_handler]|g' /etc/opt/remi/php80/php-fpm.d/www.conf 2>/dev/null || true
sed -i 's|;php_value\[session.save_path\]|php_value[session.save_path]|g' /etc/opt/remi/php80/php-fpm.d/www.conf 2>/dev/null || true
echo "✓ PHP-FPM session 配置已更新"

# 3. 确保 Apache session 配置正确
echo "步骤 3/5: 配置 Apache session..."
cat > /etc/httpd/conf.d/php-session.conf <<'EOF'
<IfModule mod_php5.c>
    php_value session.save_handler "files"
    php_value session.save_path "/var/lib/php/session"
    php_value session.cookie_httponly On
    php_value session.use_strict_mode On
</IfModule>
EOF
echo "✓ Apache session 配置已更新"

# 4. 重启服务
echo "步骤 4/5: 重启服务..."
systemctl restart php80-php-fpm
systemctl restart httpd
echo "✓ 服务已重启"

# 5. 测试 session
echo "步骤 5/5: 测试 session..."
cat > /var/www/html/sentinel/test_session.php <<'EOF'
<?php
session_start();
$_SESSION['test'] = 'hello';
echo "Session ID: " . session_id() . "\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session file: " . session_save_path() . "/sess_" . session_id() . "\n";
if (file_exists(session_save_path() . "/sess_" . session_id())) {
    echo "✓ Session file created successfully\n";
} else {
    echo "✗ Session file not created\n";
}
?>
EOF

echo ""
echo "=== 第一次访问（创建 session）==="
curl -c /tmp/cookies.txt http://localhost:9665/test_session.php

echo ""
echo "=== 第二次访问（读取 session）==="
curl -b /tmp/cookies.txt http://localhost:9665/test_session.php

echo ""
echo "=== 检查 session 文件 ==="
ls -la /var/lib/php/session/ | head -10

echo ""
echo "========================================"
echo "  修复完成！"
echo "========================================"
echo ""
echo "请尝试重新登录 admin.php"