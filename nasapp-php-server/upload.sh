#!/bin/bash

################################################################################
# 文件上传脚本 - 将 nasapp-php-server 上传到服务器
################################################################################

SERVER="root@45.130.146.21"
REMOTE_DIR="/root/nasapp-php-server"
LOCAL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "========================================"
echo "  NASAPP PHP 服务器 - 文件上传脚本"
echo "========================================"
echo ""

# 检查 SSH 连接
echo "检查 SSH 连接..."
if ! ssh -o ConnectTimeout=5 $SERVER "echo 'SSH 连接成功'" 2>/dev/null; then
    echo "错误: 无法连接到服务器 $SERVER"
    echo "请检查:"
    echo "  1. 服务器地址是否正确"
    echo "  2. SSH 服务是否运行"
    echo "  3. 网络连接是否正常"
    exit 1
fi

echo "SSH 连接成功"
echo ""

# 创建远程目录
echo "创建远程目录..."
ssh $SERVER "mkdir -p $REMOTE_DIR"

echo ""
echo "========================================"
echo "  开始上传文件"
echo "========================================"
echo ""

# 上传整个目录
echo "上传 nasapp-php-server 目录..."
rsync -avz --progress \
    --exclude '.git' \
    --exclude '*.log' \
    --exclude 'node_modules' \
    "$LOCAL_DIR/" "$SERVER:$REMOTE_DIR/"

echo ""
echo "========================================"
echo "  文件上传完成！"
echo "========================================"
echo ""
echo "下一步："
echo "  1. SSH 登录服务器: ssh $SERVER"
echo "  2. 进入目录: cd $REMOTE_DIR"
echo "  3. 执行安装: chmod +x install.sh && ./install.sh"
echo ""
echo "========================================"
