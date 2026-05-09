<?php
/**
 * MoeHome 后台管理 - 仪表盘首页
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/api/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

requireLogin();

$csrfToken = getCsrfToken();
$currentUser = getCurrentUser();

$modules = [
    'rss' => ['name' => 'RSS 聚合', 'icon' => 'fa-rss', 'desc' => '博客文章订阅'],
    'projects' => ['name' => 'GitHub 项目', 'icon' => 'fa-github', 'desc' => '开源项目展示'],
    'moments' => ['name' => 'Memos 动态', 'icon' => 'fa-bolt', 'desc' => '碎片化分享'],
    'guestbook' => ['name' => '留言板', 'icon' => 'fa-comments', 'desc' => '访客留言'],
    'music' => ['name' => '音乐播放器', 'icon' => 'fa-music', 'desc' => '背景音乐'],
    'donation' => ['name' => '赞赏支持', 'icon' => 'fa-heart', 'desc' => '赞助二维码'],
];

$moduleStatus = [];
foreach ($modules as $key => $module) {
    $moduleStatus[$key] = [
        'enabled' => (bool)getConfig('modules', $key . '_enabled', false),
        'name' => $module['name'],
        'icon' => $module['icon'],
        'desc' => $module['desc']
    ];
}

$siteName = getConfig('site', 'name', 'MoeHome');
$siteUrl = getConfig('site', 'url', '');

$message = '';
$messageType = '';

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>仪表盘 - MoeHome 管理后台</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/admin.css" />
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <h1 class="topbar-title">
                    <i class="fas fa-home"></i>
                    仪表盘
                </h1>
                <div class="topbar-actions">
                    <button class="btn btn-secondary btn-sm" data-theme-toggle>
                        <i class="fas fa-adjust"></i>
                    </button>
                    <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">
                        <i class="fas fa-external-link-alt"></i>
                        查看站点
                    </a>
                </div>
            </header>

            <div class="page-content">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo h($messageType); ?> fade-in">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                    <span><?php echo h($message); ?></span>
                </div>
                <?php endif; ?>

                <div class="card fade-in">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-terminal"></i>
                            欢迎回来, <?php echo h($currentUser['username'] ?? 'Admin'); ?>
                        </h2>
                    </div>
                    <div class="terminal-window" style="margin: 0; border: none;">
                        <div class="terminal-body" style="padding: 0;">
                            <div style="margin-bottom: 8px;">
                                <span style="color: var(--accent);">$</span>
                                <span style="color: var(--text-primary);">cat site-info.txt</span>
                            </div>
                            <div style="padding-left: 20px; color: var(--text-secondary);">
                                <div style="margin-bottom: 4px;">
                                    <span style="color: var(--text-muted);">site_name:</span>
                                    <span><?php echo h($siteName); ?></span>
                                </div>
                                <div style="margin-bottom: 4px;">
                                    <span style="color: var(--text-muted);">site_url:</span>
                                    <span><?php echo h($siteUrl ?: '未设置'); ?></span>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted);">last_login:</span>
                                    <span><?php echo h($currentUser['last_login'] ?? '首次登录'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stats-grid fade-in">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-puzzle-piece"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo count(array_filter($moduleStatus, fn($m) => $m['enabled'])); ?></div>
                            <div class="stat-label">已启用模块</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo count($modules); ?></div>
                            <div class="stat-label">可用模块</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo date('H:i'); ?></div>
                            <div class="stat-label">服务器时间</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">v2.0</div>
                            <div class="stat-label">系统版本</div>
                        </div>
                    </div>
                </div>

                <div class="card fade-in">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-toggle-on"></i>
                            模块状态
                        </h2>
                        <a href="modules.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i>
                            管理
                        </a>
                    </div>

                    <div class="module-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                        <?php foreach ($moduleStatus as $key => $module): ?>
                        <div class="module-item" style="padding: 16px; background: var(--bg-secondary); border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 44px; height: 44px; border-radius: 8px; background: <?php echo $module['enabled'] ? 'var(--accent-dim)' : 'var(--bg-tertiary)'; ?>; display: flex; align-items: center; justify-content: center;">
                                <i class="fas <?php echo h($module['icon']); ?>" style="color: <?php echo $module['enabled'] ? 'var(--accent)' : 'var(--text-muted)'; ?>;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 500; font-size: 0.875rem; margin-bottom: 2px;"><?php echo h($module['name']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo h($module['desc']); ?></div>
                            </div>
                            <span class="badge <?php echo $module['enabled'] ? 'badge-success' : 'badge-default'; ?>">
                                <?php echo $module['enabled'] ? '已启用' : '已禁用'; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card fade-in">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-rocket"></i>
                            快速操作
                        </h2>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        <a href="settings.php" class="btn btn-primary">
                            <i class="fas fa-cog"></i>
                            站点设置
                        </a>
                        <a href="modules.php" class="btn btn-secondary">
                            <i class="fas fa-puzzle-piece"></i>
                            模块管理
                        </a>
                        <a href="theme.php" class="btn btn-secondary">
                            <i class="fas fa-palette"></i>
                            主题设置
                        </a>
                        <a href="api/cache.php?action=clear" class="btn btn-secondary" onclick="return confirm('确定要清理所有缓存吗？')">
                            <i class="fas fa-broom"></i>
                            清理缓存
                        </a>
                        <a href="../index.php" target="_blank" class="btn btn-secondary">
                            <i class="fas fa-external-link-alt"></i>
                            预览站点
                        </a>
                    </div>
                </div>

                <div class="card fade-in">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            系统信息
                        </h2>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div style="padding: 12px; background: var(--bg-secondary); border-radius: 8px;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">PHP 版本</div>
                            <div style="font-family: var(--font-mono); font-size: 0.875rem;"><?php echo PHP_VERSION; ?></div>
                        </div>
                        <div style="padding: 12px; background: var(--bg-secondary); border-radius: 8px;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">服务器软件</div>
                            <div style="font-family: var(--font-mono); font-size: 0.875rem;"><?php echo h($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></div>
                        </div>
                        <div style="padding: 12px; background: var(--bg-secondary); border-radius: 8px;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">MySQL 版本</div>
                            <div style="font-family: var(--font-mono); font-size: 0.875rem;">
                                <?php
                                try {
                                    echo Database::getInstance()->getAttribute(PDO::ATTR_SERVER_VERSION);
                                } catch (Exception $e) {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                        <div style="padding: 12px; background: var(--bg-secondary); border-radius: 8px;">
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">时区</div>
                            <div style="font-family: var(--font-mono); font-size: 0.875rem;"><?php echo date_default_timezone_get(); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>
