# NASAPP 哨兵模式视频监控系统 - PHP 服务器版

## 项目简介

这是一个完整的哨兵模式视频监控系统，用于自动监控车机哨兵模式录制的视频文件，并提供灵活的处理方式。

**技术栈**：
- PHP 8.0
- Apache 2.4
- SQLite3
- CentOS 7

## 目录结构

```
nasapp-php-server/
├── install.sh              # 一键安装脚本
├── README.md               # 本文档
├── php-files/              # PHP 源代码文件
│   ├── admin.php           # 管理页面
│   ├── config.php          # 配置文件（自动生成）
│   ├── create_database.php # 数据库初始化
│   ├── health.php          # 健康检查
│   ├── login.php           # 用户登录
│   ├── register.php        # 用户注册
│   ├── upload.php          # 视频上传
│   ├── report_video.php    # 视频上报
│   ├── videos.php          # 视频列表
│   ├── video.php           # 视频播放
│   ├── test.php            # 环境测试
│   ├── diagnostic.php      # 系统诊断
│   ├── database_manager.html # 数据库管理
│   └── ...                 # 其他工具文件
└── scripts/                # 辅助脚本
```

## 快速开始

### 前提条件

- CentOS 7 服务器
- Root 权限
- 服务器 IP：45.130.146.21
- 端口：9665

### 安装步骤

#### 1. 上传文件到服务器

将整个 `nasapp-php-server` 文件夹上传到服务器的 `/root/` 目录：

```bash
# 使用 SCP 上传（在本地执行）
scp -r nasapp-php-server root@45.130.146.21:/root/
```

#### 2. SSH 登录服务器

```bash
ssh root@45.130.146.21
```

#### 3. 执行一键安装脚本

```bash
cd /root/nasapp-php-server
chmod +x install.sh
./install.sh
```

安装过程大约需要 5-10 分钟，脚本会自动完成：
- ✅ 安装 Apache 和 PHP 8.0
- ✅ 配置 PHP 8.0 环境
- ✅ 复制所有 PHP 文件
- ✅ 初始化数据库
- ✅ 配置防火墙
- ✅ 启动服务

#### 4. 验证安装

```bash
# 测试健康检查
curl http://localhost:9665/health.php

# 查看服务状态
systemctl status httpd
systemctl status php80-php-fpm
```

## 访问地址

安装完成后，可以通过以下地址访问：

| 功能 | 地址 |
|------|------|
| 健康检查 | http://45.130.146.21:9665/health.php |
| 管理页面 | http://45.130.146.21:9665/admin.php |
| 数据库管理 | http://45.130.146.21:9665/database_manager.html |
| 环境测试 | http://45.130.146.21:9665/test.php |
| 系统诊断 | http://45.130.146.21:9665/diagnostic.php |
| 用户注册 | http://45.130.146.21:9665/register.php |
| 用户登录 | http://45.130.146.21:9665/login.php |

## 测试账号

- **用户名**: test
- **密码**: 123456

## 管理功能

### 管理页面 (admin.php)

默认密码：`admin123`

管理页面提供以下功能：
- 📊 系统概览（用户数、视频数、存储空间）
- 🔐 修改管理员密码
- 🧪 测试工具（PHP 环境、PushDeer 推送、视频上报等）
- 🔍 诊断工具（系统诊断、数据库诊断、配置检查等）
- ⚙️ 管理工具（数据库管理、清理过期视频等）
- ⚠️ 危险操作（重置数据库等）

### 数据库管理 (database_manager.html)

可视化的数据库管理工具，支持：
- 查看所有用户
- 查看所有视频
- 删除数据
- 执行 SQL 查询

## 常用命令

### 服务管理

```bash
# 启动服务
systemctl start httpd
systemctl start php80-php-fpm

# 停止服务
systemctl stop httpd
systemctl stop php80-php-fpm

# 重启服务
systemctl restart httpd
systemctl restart php80-php-fpm

# 查看状态
systemctl status httpd
systemctl status php80-php-fpm

# 开机自启
systemctl enable httpd
systemctl enable php80-php-fpm
```

### 日志查看

```bash
# Apache 错误日志
tail -f /var/log/httpd/error_log

# Apache 访问日志
tail -f /var/log/httpd/access_log

# Sentinel 日志
tail -f /var/log/httpd/sentinel_error.log
tail -f /var/log/httpd/sentinel_access.log

# PHP-FPM 日志
tail -f /var/opt/remi/php80/log/php-fpm/error.log
```

### 数据库管理

```bash
# 备份数据库
cp /var/www/html/sentinel/database/sentinel.db /root/sentinel_backup_$(date +%Y%m%d).db

# 查看数据库
sqlite3 /var/www/html/sentinel/database/sentinel.db

# 查看 SQL
.tables
SELECT * FROM users;
SELECT * FROM videos;

# 退出
.quit
```

### 防火墙管理

```bash
# 查看开放的端口
firewall-cmd --list-ports

# 开放端口
firewall-cmd --permanent --add-port=9665/tcp
firewall-cmd --reload

# 关闭端口
firewall-cmd --permanent --remove-port=9665/tcp
firewall-cmd --reload
```

## 配置文件

### config.php

主要配置项：

```php
// 数据库路径
define('DB_FILE', '/var/www/html/sentinel/database/sentinel.db');

// 上传目录
define('UPLOAD_DIR', '/var/www/html/sentinel/uploads');

// 服务器地址
define('BASE_URL', 'http://45.130.146.21:9665');

// PushDeer API
define('PUSHDEER_API', 'https://api2.pushdeer.com/message/push');

// 最大文件大小（500MB）
define('MAX_FILE_SIZE', 500 * 1024 * 1024);

// 视频保留时间（7天）
define('VIDEO_RETENTION_TIME', 604800);
```

## API 接口

### 健康检查

```
GET /health.php
```

### 用户注册

```
POST /register.php
Content-Type: application/json

{
  "username": "user",
  "password": "password",
  "email": "user@example.com"
}
```

### 用户登录

```
POST /login.php
Content-Type: application/json

{
  "username": "test",
  "password": "123456"
}
```

### 视频上报

```
POST /report_video.php
Content-Type: application/json

{
  "userId": 1,
  "deviceId": "Xiaomi14",
  "fileName": "2026-01-01_12-00-00.mp4",
  "fileSize": 10485760,
  "timestamp": 1704110400000
}
```

### 视频上传

```
POST /upload.php
Content-Type: multipart/form-data

userId: 1
deviceId: Xiaomi14
timestamp: 1704110400000
video: [视频文件]
```

## 故障排查

### 服务无法启动

```bash
# 检查服务状态
systemctl status httpd
systemctl status php80-php-fpm

# 查看错误日志
tail -n 50 /var/log/httpd/error_log
tail -n 50 /var/opt/remi/php80/log/php-fpm/error.log
```

### 端口无法访问

```bash
# 检查端口监听
netstat -tlnp | grep 9665

# 检查防火墙
firewall-cmd --list-ports

# 测试本地访问
curl http://localhost:9665/health.php
```

### PHP 错误

```bash
# 检查 PHP 版本
php -v

# 测试 PHP 语法
php -l /var/www/html/sentinel/admin.php

# 查看 PHP 配置
php -i | grep error_reporting
```

### 数据库问题

```bash
# 检查数据库文件
ls -la /var/www/html/sentinel/database/

# 删除锁定文件
rm -f /var/www/html/sentinel/database/sentinel.db-shm
rm -f /var/www/html/sentinel/database/sentinel.db-wal

# 重新初始化数据库
rm -f /var/www/html/sentinel/database/sentinel.db
php /var/www/html/sentinel/create_database.php
```

## 卸载

```bash
# 停止服务
systemctl stop httpd
systemctl stop php80-php-fpm

# 删除安装目录
rm -rf /var/www/html/sentinel

# 删除配置文件
rm -f /etc/httpd/conf.d/sentinel.conf

# 重启服务
systemctl restart httpd
```

## 更新

```bash
# 备份当前版本
cp -r /var/www/html/sentinel /var/www/html/sentinel_backup

# 上传新文件
cd /root/nasapp-php-server
cp -r php-files/* /var/www/html/sentinel/

# 设置权限
chmod -R 777 /var/www/html/sentinel
chown -R apache:apache /var/www/html/sentinel

# 重启服务
systemctl restart httpd
systemctl restart php80-php-fpm
```

## 安全建议

1. **修改默认密码**：登录管理页面后立即修改管理员密码
2. **配置防火墙**：只开放必要端口
3. **定期备份**：定期备份数据库和上传文件
4. **监控日志**：定期查看日志文件
5. **更新系统**：定期更新系统和依赖包
6. **使用 HTTPS**：生产环境建议使用 HTTPS

## 技术支持

- **项目维护**：熊哥和SS联合开发
- **GitHub**: https://github.com/Gundamx682/NASAPP

## 版本信息

- **版本号**: 2.0.0
- **PHP 版本**: 8.0
- **发布日期**: 2026-01-05

---

**最后更新**: 2026年1月5日