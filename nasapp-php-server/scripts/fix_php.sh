#!/bin/bash

################################################################################
# 修复 Apache PHP 版本问题
# 解决 Apache 使用 PHP 5.4 而不是 PHP 8.0 的问题
################################################################################

echo "========================================"
echo "  修复 Apache PHP 版本问题"
echo "========================================"
echo ""

# 1. 禁用系统 PHP 模块
echo "步骤 1/5: 禁用系统 PHP 模块..."
mv /etc/httpd/conf.modules.d/10-php.conf /etc/httpd/conf.modules.d/10-php.conf.disabled 2>/dev/null || true
echo "✓ 系统 PHP 模块已禁用"

# 2. 配置 Apache 使用 PHP-FPM
echo "步骤 2/5: 配置 Apache 使用 PHP-FPM..."
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
echo "✓ Apache 配置已更新"

# 3. 确保 session 目录存在
echo "步骤 3/5: 配置 session 目录..."
mkdir -p /var/lib/php/session
chmod 777 /var/lib/php/session
chown apache:apache /var/lib/php/session
echo "✓ Session 目录已配置"

# 4. 重启服务
echo "步骤 4/5: 重启服务..."
systemctl restart php80-php-fpm
systemctl restart httpd
echo "✓ 服务已重启"

# 5. 测试
echo "步骤 5/5: 测试服务..."
echo ""
echo "=== 测试 admin.php ==="
curl -I http://localhost:9665/admin.php

echo ""
echo "=== 测试 health.php ==="
curl http://localhost:9665/health.php

echo ""
echo "========================================"
echo "  修复完成！"
echo "========================================"