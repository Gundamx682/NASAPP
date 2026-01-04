# NASAPP PHP 服务器版 - 项目总结

## 📁 项目结构

```
nasapp-php-server/              # 全新项目根目录
├── install.sh                  # 一键安装脚本（在服务器上运行）
├── upload.sh                   # 上传脚本（在本地运行）
├── README.md                   # 详细文档（完整说明）
├── QUICKSTART.md               # 快速开始指南（3步部署）
├── PROJECT_SUMMARY.md          # 本文件（项目总结）
├── .gitignore                  # Git 忽略文件
│
├── php-files/                  # PHP 源代码目录（56个文件）
│   ├── 核心 API 文件
│   │   ├── health.php          # 健康检查
│   │   ├── register.php        # 用户注册
│   │   ├── login.php           # 用户登录
│   │   ├── user.php            # 用户信息
│   │   ├── upload.php          # 视频上传
│   │   ├── report_video.php    # 视频上报
│   │   ├── videos.php          # 视频列表
│   │   ├── video.php           # 视频播放
│   │   ├── video_info.php      # 视频详情
│   │   └── delete_video.php    # 删除视频
│   │
│   ├── 配置和管理文件
│   │   ├── config.php          # 配置文件
│   │   ├── create_database.php # 数据库初始化
│   │   ├── admin.php           # 管理页面
│   │   ├── admin_password.php  # 管理员密码
│   │   ├── save_config.php     # 保存配置
│   │   ├── get_config.php      # 获取配置
│   │   └── update_pushkey.php  # 更新 PushKey
│   │
│   ├── 测试工具（9个）
│   │   ├── test.php            # PHP 环境测试
│   │   ├── test_pushdeer.php   # PushDeer 推送测试
│   │   ├── test_pushdeer_simple.php # 简单推送测试
│   │   ├── test_video.php      # 视频上传测试
│   │   ├── test_report_video.php # 视频上报测试
│   │   ├── test_url.php        # URL 测试
│   │   ├── test_timestamp.php  # 时间戳测试
│   │   ├── test_time_conversion.php # 时间转换测试
│   │   └── test_vendor.php     # 供应商测试
│   │
│   ├── 诊断工具（10个）
│   │   ├── diagnostic.php      # 系统诊断
│   │   ├── diagnose_database.php # 数据库诊断
│   │   ├── diagnose_file_scan.php # 文件扫描诊断
│   │   ├── simple_diagnose.php # 简化诊断
│   │   ├── check_php_config.php # PHP 配置检查
│   │   ├── check_videos.php    # 检查视频
│   │   ├── check_pushkey.php   # 检查 PushKey
│   │   ├── check_recent_reports.php # 检查最近上报
│   │   ├── check_update.php    # 检查更新
│   │   └── check_vehicle_fields.php # 检查车辆字段
│   │
│   ├── 管理工具（8个）
│   │   ├── database_manager.html # 数据库可视化管理
│   │   ├── cleanup.php         # 清理过期视频
│   │   ├── auto_scan.php       # 自动扫描
│   │   ├── scan_videos.php     # 扫描视频
│   │   ├── ensure_test_user.php # 确保测试用户
│   │   ├── reset_database.php  # 重置数据库
│   │   ├── simple_reset.php    # 简单重置
│   │   └── view_upload_log.php # 查看上传日志
│   │
│   ├── 辅助工具（10个）
│   │   ├── index.php           # 首页
│   │   ├── download_apk.php    # APK 下载
│   │   ├── save_fcm_token.php  # 保存 FCM Token
│   │   ├── debug_report_video.php # 调试上报
│   │   ├── add_monitor_directory_column.php # 添加监控目录字段
│   │   ├── add_vehicle_fields.php # 添加车辆字段
│   │   ├── remove_qiniu_fields.php # 移除七牛字段
│   │   ├── check_all_users.php # 检查所有用户
│   │   ├── check_files.php     # 检查文件
│   │   └── check_latest_video.php # 检查最新视频
│   │
│   ├── 配置文件
│   │   ├── .htaccess           # Apache URL 重写规则
│   │   └── version.json        # 版本信息
│   │
│   └── 其他工具
│       ├── check_video_storage.php # 检查视频存储
│       └── FOREIGN_SERVER_GUIDE.md # 外部服务器指南
│
└── scripts/                    # 辅助脚本目录（预留）
```

## 📊 文件统计

| 类别 | 数量 | 说明 |
|------|------|------|
| 核心 API | 10 | 注册、登录、上传、上报等 |
| 配置管理 | 7 | 配置文件、数据库初始化等 |
| 测试工具 | 9 | 各种功能测试 |
| 诊断工具 | 10 | 系统诊断和检查 |
| 管理工具 | 8 | 数据库管理、清理等 |
| 辅助工具 | 10 | 各种辅助功能 |
| 脚本文件 | 2 | install.sh, upload.sh |
| 文档文件 | 4 | README, QUICKSTART 等 |
| **总计** | **60+** | 完整的项目文件 |

## 🚀 部署流程

### 方式 1：使用 upload.sh（推荐）

```bash
# 1. 在本地执行上传脚本
cd E:\999\NASAPP\nasapp-php-server
bash upload.sh

# 2. SSH 登录服务器
ssh root@45.130.146.21

# 3. 执行安装
cd /root/nasapp-php-server
chmod +x install.sh
./install.sh
```

### 方式 2：手动上传

```bash
# 1. 使用 SCP 上传
scp -r nasapp-php-server root@45.130.146.21:/root/

# 2. SSH 登录服务器
ssh root@45.130.146.21

# 3. 执行安装
cd /root/nasapp-php-server
chmod +x install.sh
./install.sh
```

## ✅ 安装完成后

### 服务状态检查

```bash
# 查看服务状态
systemctl status httpd
systemctl status php80-php-fpm

# 查看端口监听
netstat -tlnp | grep 9665

# 测试健康检查
curl http://localhost:9665/health.php
```

### 访问地址

| 功能 | URL | 说明 |
|------|-----|------|
| 健康检查 | http://45.130.146.21:9665/health.php | 服务状态 |
| 管理页面 | http://45.130.146.21:9665/admin.php | 系统管理 |
| 数据库管理 | http://45.130.146.21:9665/database_manager.html | 可视化管理 |
| 环境测试 | http://45.130.146.21:9665/test.php | PHP 测试 |
| 系统诊断 | http://45.130.146.21:9665/diagnostic.php | 诊断工具 |

## 🎯 核心功能

### 1. 用户系统
- ✅ 用户注册（用户名、密码、邮箱）
- ✅ 用户登录
- ✅ 用户信息管理
- ✅ 配置同步

### 2. 视频管理
- ✅ 视频上传（完整文件）
- ✅ 视频上报（只上报信息）
- ✅ 视频列表（分页）
- ✅ 视频详情
- ✅ 视频播放（支持断点续传）
- ✅ 视频删除
- ✅ 自动清理（7天过期）

### 3. 推送通知
- ✅ PushDeer 推送
- ✅ 推送测试工具
- ✅ PushKey 管理

### 4. 管理工具
- ✅ 可视化数据库管理
- ✅ 系统概览
- ✅ 用户管理
- ✅ 视频管理
- ✅ 日志查看

### 5. 测试工具
- ✅ PHP 环境测试
- ✅ 数据库测试
- ✅ API 测试
- ✅ 推送测试

### 6. 诊断工具
- ✅ 系统诊断
- ✅ 数据库诊断
- ✅ 配置检查
- ✅ 文件扫描诊断

## 🔧 技术栈

### 服务器环境
- **操作系统**: CentOS 7
- **Web 服务器**: Apache 2.4
- **PHP 版本**: 8.0
- **数据库**: SQLite3
- **进程管理**: PHP-FPM

### PHP 扩展
- php-pdo（数据库抽象层）
- php-sqlite3（SQLite3 支持）
- php-gd（图像处理）
- php-xml（XML 处理）
- php-mbstring（多字节字符串）

### 配置
- **端口**: 9665
- **最大文件大小**: 500MB
- **视频保留时间**: 7天
- **时区**: Asia/Shanghai

## 📝 配置说明

### config.php 主要配置

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

## 🆚 与旧版本对比

| 特性 | 旧版本 (centos-setup/) | 新版本 (nasapp-php-server/) |
|------|------------------------|----------------------------|
| **文件组织** | 分散在多个目录 | 集中在一个文件夹 |
| **PHP 版本** | 5.x | 8.0 |
| **安装脚本** | 10+ 个脚本 | 1 个一键安装脚本 |
| **文档** | 分散 | 集中完整文档 |
| **易用性** | 需要选择脚本 | 自动化一键安装 |
| **功能完整性** | 部分 | 完整（60+ 文件） |
| **管理工具** | 部分 | 完整（管理页面+可视化） |
| **测试工具** | 部分 | 完整（9个测试工具） |
| **诊断工具** | 部分 | 完整（10个诊断工具） |

## 🔒 安全特性

- ✅ 密码加密（MD5）
- ✅ Session 管理
- ✅ 文件类型验证
- ✅ 文件大小限制
- ✅ 路径安全检查
- ✅ SQL 注入防护（PDO）
- ✅ CORS 配置
- ✅ 防火墙配置

## 📈 性能优化

- ✅ PHP 8.0 JIT 编译器
- ✅ 数据库索引
- ✅ 视频断点续传
- ✅ 静态文件缓存
- ✅ PHP-FPM 进程管理
- ✅ 自动清理过期文件

## 🎨 特色功能

### 1. 上报模式
- 只上报文件信息（几KB）
- 节省流量和存储空间
- 响应更快

### 2. 上传模式
- 完整上传视频文件
- 支持在线播放
- 最大 500MB

### 3. 管理页面
- 可视化界面
- 实时统计
- 一键操作

### 4. 数据库管理
- HTML5 界面
- SQL 查询
- 数据导出

## 📞 技术支持

- **项目维护**：熊哥和SS联合开发
- **GitHub**: https://github.com/Gundamx682/NASAPP
- **服务器**: 45.130.146.21:9665

## 📅 版本信息

- **版本号**: 2.0.0
- **发布日期**: 2026-01-05
- **作者**: 熊哥和SS联合开发

## 🎉 总结

这是一个**完整、清晰、易用**的 NASAPP PHP 服务器版：

✅ **文件组织清晰**：所有文件集中在一个文件夹  
✅ **一键安装**：自动化安装脚本，无需选择  
✅ **文档完整**：README + QUICKSTART + PROJECT_SUMMARY  
✅ **功能齐全**：60+ 文件，涵盖所有功能  
✅ **技术先进**：PHP 8.0 + Apache 2.4  
✅ **管理方便**：可视化界面 + 丰富的工具  
✅ **安全可靠**：多层安全防护  

**开始使用吧！** 🚀

---

**创建日期**: 2026-01-05  
**最后更新**: 2026-01-05