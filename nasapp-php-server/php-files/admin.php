<?php
/**
 * 系统管理界面
 * 整合所有测试、诊断和管理功能
 */

session_start();
require_once 'config.php';

// 从配置文件读取管理员密码
$adminPasswordFile = __DIR__ . '/admin_password.php';
if (file_exists($adminPasswordFile)) {
    define('ADMIN_PASSWORD', require $adminPasswordFile);
} else {
    // 默认密码
    define('ADMIN_PASSWORD', 'admin123');
}

// 检查是否已登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 处理登录
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = '密码错误';
        }
    }
    
    // 显示登录页面
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统管理 - 登录</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 400px;
                width: 100%;
            }
            h1 {
                color: #333;
                margin-bottom: 30px;
                text-align: center;
                font-size: 28px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #555;
            }
            input[type="password"] {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            button:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }
            .error {
                background: #fee;
                color: #c33;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                font-size: 14px;
            }
            .success {
                background: #d4edda;
                color: #155724;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                font-size: 14px;
            }
            .info {
                text-align: center;
                color: #666;
                font-size: 14px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🔐 系统管理</h1>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['password_changed']) && $_GET['password_changed'] == '1'): ?>
                <div class="success">密码修改成功！请使用新密码登录。</div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">管理员密码</label>
                    <input type="password" id="password" name="password" placeholder="请输入密码" required autofocus>
                </div>
                <button type="submit">登录</button>
            </form>
            <div class="info">
                默认密码: admin123
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 处理登出
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// 处理修改密码
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($oldPassword !== ADMIN_PASSWORD) {
        $error = '旧密码错误';
    } elseif (empty($newPassword)) {
        $error = '新密码不能为空';
    } elseif (strlen($newPassword) < 6) {
        $error = '新密码长度至少6位';
    } elseif ($newPassword !== $confirmPassword) {
        $error = '两次输入的新密码不一致';
    } else {
        // 保存新密码到文件
        $passwordContent = "<?php\n/**\n * 管理员密码配置文件\n * 请勿直接修改此文件，请通过admin.php界面修改密码\n */\nreturn '" . addslashes($newPassword) . "';\n";
        
        if (file_put_contents($adminPasswordFile, $passwordContent)) {
            $message = '密码修改成功！下次登录请使用新密码。';
            // 清除session，要求重新登录
            session_destroy();
            header('Location: admin.php?password_changed=1');
            exit;
        } else {
            $error = '密码修改失败，请检查文件权限';
        }
    }
}

// 处理重置数据库
if (isset($_POST['action']) && $_POST['action'] === 'reset_database') {
    try {
        // 删除数据库文件
        if (file_exists(DB_FILE)) {
            unlink(DB_FILE);
        }
        // 重新创建数据库
        require_once 'config.php';
        $message = '数据库已重置成功';
    } catch (Exception $e) {
        $error = '重置失败: ' . $e->getMessage();
    }
}

// 获取系统信息
$dbTables = [];
$userCount = 0;
$videoCount = 0;
$uploadSize = 0;

try {
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    $dbTables = $tables;
    
    if (in_array('users', $tables)) {
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
    if (in_array('videos', $tables)) {
        $videoCount = $db->query("SELECT COUNT(*) FROM videos")->fetchColumn();
        $uploadSize = $db->query("SELECT SUM(size) FROM videos")->fetchColumn() ?: 0;
    }
} catch (Exception $e) {
    $error = '获取系统信息失败: ' . $e->getMessage();
}

// 计算上传目录大小
$uploadDirSize = 0;
if (is_dir(UPLOAD_DIR)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(UPLOAD_DIR));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $uploadDirSize += $file->getSize();
        }
    }
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统管理面板</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        .header .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .header .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-card .label {
            font-size: 14px;
            opacity: 0.9;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }
        .tool-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .tool-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .tool-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .tool-card p {
            color: #666;
            font-size: 13px;
            line-height: 1.5;
        }
        .tool-card .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 10px;
        }
        .badge-test {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge-diag {
            background: #fff3e0;
            color: #f57c00;
        }
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .info-table th,
        .info-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .info-table tr:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔧 系统管理面板</h1>
        <a href="admin.php?action=logout" class="logout-btn">退出登录</a>
    </div>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- 系统概览 -->
        <div class="section">
            <h2>📊 系统概览</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?php echo $userCount; ?></div>
                    <div class="label">注册用户</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $videoCount; ?></div>
                    <div class="label">视频总数</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo formatSize($uploadSize); ?></div>
                    <div class="label">数据库记录大小</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo formatSize($uploadDirSize); ?></div>
                    <div class="label">上传目录大小</div>
                </div>
            </div>
            
            <table class="info-table">
                <tr>
                    <th>配置项</th>
                    <th>值</th>
                </tr>
                <tr>
                    <td>基础URL</td>
                    <td><?php echo BASE_URL; ?></td>
                </tr>
                <tr>
                    <td>数据库文件</td>
                    <td><?php echo DB_FILE; ?></td>
                </tr>
                <tr>
                    <td>上传目录</td>
                    <td><?php echo UPLOAD_DIR; ?></td>
                </tr>
                <tr>
                    <td>PushDeer API</td>
                    <td><?php echo PUSHDEER_API; ?></td>
                </tr>
                <tr>
                    <td>最大文件大小</td>
                    <td><?php echo formatSize(MAX_FILE_SIZE); ?></td>
                </tr>
                <tr>
                    <td>视频保留时间</td>
                    <td><?php echo VIDEO_RETENTION_TIME / 86400; ?> 天</td>
                </tr>
                <tr>
                    <td>数据库表</td>
                    <td><?php echo implode(', ', $dbTables); ?></td>
                </tr>
                <tr>
                    <td>PHP版本</td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td>服务器时间</td>
                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                </tr>
            </table>
        </div>

        <!-- 修改密码 -->
        <div class="section">
            <h2>🔐 修改管理员密码</h2>
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ffc107;">
                <p style="color: #856404; margin: 0;"><strong>⚠️ 安全提示：</strong>修改密码后需要重新登录。请妥善保管新密码。</p>
            </div>
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">旧密码:</label>
                    <input type="password" name="old_password" required 
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">新密码（至少6位）:</label>
                    <input type="password" name="new_password" required minlength="6"
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">确认新密码:</label>
                    <input type="password" name="confirm_password" required minlength="6"
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                </div>
                <button type="submit" name="action" value="change_password" 
                        style="width: 100%; padding: 14px; background: #667eea; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer;">
                    修改密码
                </button>
            </form>
        </div>

        <!-- 测试工具 -->
        <div class="section">
            <h2>🧪 测试工具</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="window.open('ensure_test_user.php', '_blank')">
                    <h3>确保测试用户</h3>
                    <p>创建或检查测试用户（testuser/test123）</p>
                    <span class="badge badge-test">准备</span>
                </div>
                <div class="tool-card" onclick="window.open('test.php', '_blank')">
                    <h3>PHP环境测试</h3>
                    <p>测试PHP环境和数据库连接是否正常</p>
                    <span class="badge badge-test">测试</span>
                </div>
                <div class="tool-card" onclick="window.open('test_pushdeer.php', '_blank')">
                    <h3>PushDeer推送测试</h3>
                    <p>测试PushDeer推送功能是否正常工作</p>
                    <span class="badge badge-test">测试</span>
                </div>
                <div class="tool-card" onclick="window.open('test_pushdeer_simple.php', '_blank')">
                    <h3>简单推送测试</h3>
                    <p>快速测试PushDeer推送功能</p>
                    <span class="badge badge-test">测试</span>
                </div>
                <div class="tool-card" onclick="window.open('test_report_video.php', '_blank')">
                    <h3>视频上报测试</h3>
                    <p>测试视频上报API功能</p>
                    <span class="badge badge-test">测试</span>
                </div>
                <div class="tool-card" onclick="window.open('test_video.php', '_blank')">
                    <h3>视频上传测试</h3>
                    <p>测试视频上传API功能</p>
                    <span class="badge badge-test">测试</span>
                </div>
                <div class="tool-card" onclick="window.open('test_time_conversion.php', '_blank')">
                    <h3>时间转换测试</h3>
                    <p>测试时间戳转换功能</p>
                    <span class="badge badge-test">测试</span>
                </div>
                <div class="tool-card" onclick="window.open('test_timestamp.php', '_blank')">
                    <h3>时间戳测试</h3>
                    <p>测试时间戳处理功能</p>
                    <span class="badge badge-test">测试</span>
                </div>
                <div class="tool-card" onclick="window.open('test_url.php', '_blank')">
                    <h3>URL测试</h3>
                    <p>测试URL配置是否正确</p>
                    <span class="badge badge-test">测试</span>
                </div>
            </div>
        </div>

        <!-- 诊断工具 -->
        <div class="section">
            <h2>🔍 诊断工具</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="window.open('diagnose_file_scan.php', '_blank')">
                    <h3>文件扫描诊断</h3>
                    <p>诊断为什么找不到视频文件（推荐优先使用）</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('diagnostic.php', '_blank')">
                    <h3>系统诊断</h3>
                    <p>全面的系统状态检查和诊断</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('diagnose_database.php', '_blank')">
                    <h3>数据库诊断</h3>
                    <p>检查数据库状态和完整性</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('check_php_config.php', '_blank')">
                    <h3>PHP配置检查</h3>
                    <p>检查PHP环境配置</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('check_pushkey.php', '_blank')">
                    <h3>检查PushKey</h3>
                    <p>检查用户的PushKey配置（选择用户）</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('check_recent_reports.php', '_blank')">
                    <h3>检查最近上报</h3>
                    <p>查看最近的上报记录</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('check_videos.php', '_blank')">
                    <h3>检查视频文件</h3>
                    <p>检查视频存储状态</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('check_update.php', '_blank')">
                    <h3>检查更新</h3>
                    <p>测试更新功能</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('debug_report_video.php', '_blank')">
                    <h3>调试视频上报</h3>
                    <p>调试视频上报问题</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
                <div class="tool-card" onclick="window.open('simple_diagnose.php', '_blank')">
                    <h3>简化诊断</h3>
                    <p>快速诊断系统状态</p>
                    <span class="badge badge-diag">诊断</span>
                </div>
            </div>
        </div>

        <!-- 管理工具 -->
        <div class="section">
            <h2>⚙️ 管理工具</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="window.open('database_manager.html', '_blank')">
                    <h3>数据库管理</h3>
                    <p>可视化管理数据库数据</p>
                    <span class="badge badge-diag">管理</span>
                </div>
                <div class="tool-card" onclick="window.open('view_upload_log.php', '_blank')">
                    <h3>上传日志</h3>
                    <p>查看上传历史记录</p>
                    <span class="badge badge-diag">管理</span>
                </div>
                <div class="tool-card" onclick="window.open('scan_videos.php', '_blank')">
                    <h3>扫描视频</h3>
                    <p>手动触发视频扫描</p>
                    <span class="badge badge-diag">管理</span>
                </div>
                <div class="tool-card" onclick="window.open('auto_scan.php', '_blank')">
                    <h3>自动扫描</h3>
                    <p>执行自动扫描脚本</p>
                    <span class="badge badge-diag">管理</span>
                </div>
                <div class="tool-card" onclick="window.open('cleanup.php', '_blank')">
                    <h3>清理过期视频</h3>
                    <p>清理7天前的过期视频</p>
                    <span class="badge badge-diag">管理</span>
                </div>
                <div class="tool-card" onclick="window.open('health.php', '_blank')">
                    <h3>健康检查</h3>
                    <p>检查系统健康状态</p>
                    <span class="badge badge-diag">管理</span>
                </div>
            </div>
        </div>

        <!-- 危险操作 -->
        <div class="section">
            <h2>⚠️ 危险操作</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="window.open('simple_reset.php', '_blank')">
                    <h3>简单重置</h3>
                    <p>重置某些状态</p>
                    <span class="badge badge-danger">危险</span>
                </div>
                <div class="tool-card" onclick="window.open('reset_database.php', '_blank')">
                    <h3>重置数据库</h3>
                    <p>清空所有数据（不可恢复）</p>
                    <span class="badge badge-danger">危险</span>
                </div>
                <div class="tool-card" onclick="window.open('add_monitor_directory_column.php', '_blank')">
                    <h3>添加监控目录字段</h3>
                    <p>数据库结构升级</p>
                    <span class="badge badge-danger">升级</span>
                </div>
            </div>
            
            <div style="margin-top: 20px; padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">
                <h3 style="color: #856404; margin-bottom: 10px;">⚠️ 重置数据库</h3>
                <p style="color: #856404; margin-bottom: 15px;">此操作将删除所有用户、视频和配置数据，且不可恢复！请谨慎操作。</p>
                <form method="POST" onsubmit="return confirm('确定要重置数据库吗？此操作将删除所有数据且不可恢复！');">
                    <input type="hidden" name="action" value="reset_database">
                    <button type="submit" class="btn btn-danger">重置数据库</button>
                </form>
            </div>
        </div>

        <!-- 快速访问 -->
        <div class="section">
            <h2>🔗 快速访问</h2>
            <div class="tools-grid">
                <div class="tool-card" onclick="window.open('index.php', '_blank')">
                    <h3>首页</h3>
                    <p>系统首页</p>
                </div>
                <div class="tool-card" onclick="window.open('version.json', '_blank')">
                    <h3>版本信息</h3>
                    <p>查看当前版本</p>
                </div>
                <div class="tool-card" onclick="window.open('PUSHDEER_GUIDE.md', '_blank')">
                    <h3>PushDeer指南</h3>
                    <p>PushDeer配置说明</p>
                </div>
                <div class="tool-card" onclick="window.open('PUSHDEER_SETUP.md', '_blank')">
                    <h3>PushDeer设置</h3>
                    <p>PushDeer设置教程</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 自动刷新系统概览（每30秒）
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>