#!/bin/bash

################################################################################
# 修复数据库管理工具的 JSON 问题
################################################################################

echo "========================================"
echo "  修复数据库管理工具"
echo "========================================"
echo ""

# 1. 检查 diagnose_database.php
echo "步骤 1/3: 检查 diagnose_database.php..."
php -l /var/www/html/sentinel/diagnose_database.php
echo ""

# 2. 检查 reset_database.php
echo "步骤 2/3: 检查 reset_database.php..."
php -l /var/www/html/sentinel/reset_database.php
echo ""

# 3. 测试 API 响应
echo "步骤 3/3: 测试 API 响应..."
echo "=== 测试 diagnose_database.php ==="
curl -s http://localhost:9665/diagnose_database.php

echo ""
echo "=== 测试 reset_database.php ==="
curl -s http://localhost:9665/reset_database.php

echo ""
echo "=== 查看错误日志 ==="
tail -n 10 /var/log/httpd/error_log

echo ""
echo "========================================"
echo "  诊断完成"
echo "========================================"