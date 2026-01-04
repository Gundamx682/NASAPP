#!/bin/bash

################################################################################
# 完整修复 admin.php 的问题
# 解决登录后刷新无法保持登录状态的问题
################################################################################

echo "========================================"
echo "  修复 admin.php 问题"
echo "========================================"
echo ""

# 1. 备份原文件
echo "步骤 1/4: 备份 admin.php..."
cp /var/www/html/sentinel/admin.php /var/www/html/sentinel/admin.php.backup3
echo "✓ 备份完成"

# 2. 从 GitHub 重新下载 admin.php
echo "步骤 2/4: 重新下载 admin.php..."
curl -fsSL https://raw.githubusercontent.com/Gundamx682/NASAPP/main/nas-setup/php/admin.php -o /var/www/html/sentinel/admin.php
echo "✓ admin.php 已重新下载"

# 3. 修复 REQUEST_METHOD 检查
echo "步骤 3/4: 修复 REQUEST_METHOD 检查..."
# 使用 Python 来精确替换
python3 <<'PYTHON_SCRIPT'
import re

with open('/var/www/html/sentinel/admin.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 修复所有 REQUEST_METHOD 检查
content = content.replace(
    '$_SERVER[\'REQUEST_METHOD\']',
    'isset($_SERVER[\'REQUEST_METHOD\']) ? $_SERVER[\'REQUEST_METHOD\'] : "GET"'
)

# 修复其他可能的问题
content = content.replace(
    'if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\'',
    'if (isset($_SERVER[\'REQUEST_METHOD\']) && $_SERVER[\'REQUEST_METHOD\'] === \'POST\''
)

with open('/var/www/html/sentinel/admin.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("✓ REQUEST_METHOD 检查已修复")
PYTHON_SCRIPT

# 4. 测试修复
echo "步骤 4/4: 测试修复..."
echo ""
echo "=== 测试 admin.php 语法 ==="
php -l /var/www/html/sentinel/admin.php

echo ""
echo "=== 测试登录 ==="
curl -c /tmp/fix_cookies.txt -X POST http://localhost:9665/admin.php -d "password=admin123" -I | head -10

echo ""
echo "=== 测试保持登录 ==="
curl -b /tmp/fix_cookies.txt -I http://localhost:9665/admin.php | head -10

echo ""
echo "========================================"
echo "  修复完成！"
echo "========================================"
echo ""
echo "请尝试重新登录 admin.php"