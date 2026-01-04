#!/bin/bash

################################################################################
# 完整修复 Apache PHP 版本问题
# 自动检测并修复所有相关问题
################################################################################

echo "========================================"
echo "  完整修复 Apache PHP 版本问题"
echo "========================================"
echo ""

# 1. 禁用系统 PHP 模块
echo "步骤 1/6: 禁用系统 PHP 模块..."
mv /etc/httpd/conf.modules.d/10-php.conf /etc/httpd/conf.modules.d/10-php.conf.disabled 2>/dev/null || true
echo "✓ 系统 PHP 模块已禁用"

# 2. 删除旧的 PHP 配置文件
echo "步骤 2/6: 清理旧的 PHP 配置..."
rm -f /etc/httpd/conf.d/php.conf
rm -f /etc/httpd/conf.modules.d/15-php.conf
echo "✓ 旧的 PHP 配置已清理"

# 3. 配置 Apache 使用 PHP-FPM
echo "步骤 3/6: 配置 Apache 使用 PHP-FPM..."
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

# 4. 确保 session 目录存在
echo "步骤 4/6: 配置 session 目录..."
mkdir -p /var/lib/php/session
chmod 777 /var/lib/php/session
chown apache:apache /var/lib/php/session
echo "✓ Session 目录已配置"

# 5. 测试配置
echo "步骤 5/6: 测试 Apache 配置..."
if httpd -t; then
    echo "✓ Apache 配置测试通过"
else
    echo "✗ Apache 配置测试失败"
    echo "请检查配置文件："
    httpd -t
    exit 1
fi

# 6. 重启服务
echo "步骤 6/6: 重启服务..."
systemctl restart php80-php-fpm
if systemctl restart httpd; then
    echo "✓ 服务重启成功"
else
    echo "✗ Apache 启动失败"
    echo "查看错误："
    journalctl -xe | tail -20
    exit 1
fi

# 测试
echo ""
echo "========================================"
echo "  测试服务"
echo "========================================"
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