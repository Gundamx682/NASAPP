#!/bin/bash

################################################################################
# 一键删除 NASAPP 服务器部署
# 警告：此操作将删除所有部署文件，不可恢复！
################################################################################

echo "========================================"
echo "  ⚠️  警告：删除 NASAPP 部署"
echo "========================================"
echo ""
echo "此操作将删除以下内容："
echo "  - 应用目录: /var/www/html/sentinel"
echo "  - 数据库文件"
echo "  - 上传的视频文件"
echo "  - Apache 配置"
echo "  - 所有数据都将丢失！"
echo ""
echo "========================================"
echo ""

# 确认删除
read -p "确定要删除吗？输入 'YES' 确认: " confirm

if [ "$confirm" != "YES" ]; then
    echo "操作已取消"
    exit 0
fi

echo ""
echo "开始删除..."
echo ""

# 1. 停止服务
echo "步骤 1/5: 停止服务..."
systemctl stop httpd 2>/dev/null || true
systemctl stop php80-php-fpm 2>/dev/null || true
echo "✓ 服务已停止"

# 2. 删除应用目录
echo "步骤 2/5: 删除应用目录..."
rm -rf /var/www/html/sentinel
echo "✓ 应用目录已删除"

# 3. 删除 Apache 配置
echo "步骤 3/5: 删除 Apache 配置..."
rm -f /etc/httpd/conf.d/sentinel.conf
rm -f /etc/httpd/conf.d/php-session.conf
echo "✓ Apache 配置已删除"

# 4. 删除防火墙规则
echo "步骤 4/5: 删除防火墙规则..."
firewall-cmd --permanent --remove-port=9665/tcp 2>/dev/null || true
firewall-cmd --reload 2>/dev/null || true
echo "✓ 防火墙规则已删除"

# 5. 显示删除结果
echo "步骤 5/5: 验证删除..."
echo ""
echo "========================================"
echo "  删除完成！"
echo "========================================"
echo ""
echo "已删除："
echo "  ✓ 应用目录 /var/www/html/sentinel"
echo "  ✓ 数据库文件"
echo "  ✓ 上传的视频文件"
echo "  ✓ Apache 配置"
echo "  ✓ 防火墙规则"
echo ""
echo "如需重新部署，请执行 install.sh"
echo "========================================"