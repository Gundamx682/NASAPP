#!/bin/bash

################################################################################
# 诊断并修复数据库问题
################################################################################

echo "========================================"
echo "  诊断并修复数据库问题"
echo "========================================"
echo ""

# 1. 检查数据库文件
echo "步骤 1/5: 检查数据库文件..."
if [ -f "/var/www/html/sentinel/database/sentinel.db" ]; then
    echo "✓ 数据库文件存在"
    ls -la /var/www/html/sentinel/database/sentinel.db
else
    echo "✗ 数据库文件不存在"
fi
echo ""

# 2. 检查数据库目录权限
echo "步骤 2/5: 检查数据库目录权限..."
ls -la /var/www/html/sentinel/database/
chmod 777 /var/www/html/sentinel/database
chown -R apache:apache /var/www/html/sentinel/database
echo "✓ 权限已设置"
echo ""

# 3. 检查 config.php 中的数据库路径
echo "步骤 3/5: 检查 config.php 配置..."
grep "DB_FILE\|DB_PATH" /var/www/html/sentinel/config.php
echo ""

# 4. 重新初始化数据库
echo "步骤 4/5: 重新初始化数据库..."
php /var/www/html/sentinel/create_database.php
echo ""

# 5. 验证数据库
echo "步骤 5/5: 验证数据库..."
if [ -f "/var/www/html/sentinel/database/sentinel.db" ]; then
    echo "✓ 数据库文件已创建"
    echo ""
    echo "=== 数据库内容 ==="
    sqlite3 /var/www/html/sentinel/database/sentinel.db ".tables"
    echo ""
    echo "=== 用户表 ==="
    sqlite3 /var/www/html/sentinel/database/sentinel.db "SELECT * FROM users;"
else
    echo "✗ 数据库创建失败"
fi

echo ""
echo "========================================"
echo "  诊断完成！"
echo "========================================"
echo ""
echo "请刷新浏览器页面查看结果"