<?php
/**
 * MoeHome 后台管理 - 站点设置
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
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $data = [
        'site_name' => trim($_POST['site_name'] ?? ''),
        'site_tagline' => trim($_POST['site_tagline'] ?? ''),
        'site_url' => trim($_POST['site_url'] ?? ''),
        'site_og_image' => trim($_POST['site_og_image'] ?? ''),
        'profile_name' => trim($_POST['profile_name'] ?? ''),
        'profile_tagline_prefix' => trim($_POST['profile_tagline_prefix'] ?? ''),
        'profile_tagline_highlight' => trim($_POST['profile_tagline_highlight'] ?? ''),
        'profile_avatar' => trim($_POST['profile_avatar'] ?? ''),
        'seo_title' => trim($_POST['seo_title'] ?? ''),
        'seo_description' => trim($_POST['seo_description'] ?? ''),
        'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
        'footer_copyright_year' => trim($_POST['footer_copyright_year'] ?? ''),
        'footer_copyright_name' => trim($_POST['footer_copyright_name'] ?? ''),
        'footer_copyright_url' => trim($_POST['footer_copyright_url'] ?? ''),
        'footer_icp_enabled' => isset($_POST['footer_icp_enabled']) ? '1' : '0',
        'footer_icp_number' => trim($_POST['footer_icp_number'] ?? ''),
    ];

    foreach ($data as $key => $value) {
        $category = strpos($key, 'seo_') === 0 ? 'seo' : (strpos($key, 'profile_') === 0 ? 'profile' : (strpos($key, 'footer_') === 0 ? 'footer' : 'site'));
        $configKey = str_replace(['site_', 'profile_', 'seo_', 'footer_'], '', $key);
        setConfig($category, $configKey, $value);
    }

    $message = '站点设置已保存';
    $messageType = 'success';
}

$site = [
    'name' => getConfig('site', 'name', ''),
    'tagline' => getConfig('site', 'tagline', ''),
    'url' => getConfig('site', 'url', ''),
    'og_image' => getConfig('site', 'og_image', ''),
];

$profile = [
    'name' => getConfig('profile', 'name', ''),
    'tagline_prefix' => getConfig('profile', 'tagline_prefix', '🐾'),
    'tagline_highlight' => getConfig('profile', 'tagline_highlight', ''),
    'avatar' => getConfig('profile', 'avatar', 'images/avatar.webp'),
];

$seo = [
    'title' => getConfig('seo', 'title', ''),
    'description' => getConfig('seo', 'description', ''),
    'keywords' => getConfig('seo', 'keywords', ''),
];

$footer = [
    'copyright_year' => getConfig('footer', 'copyright_year', date('Y')),
    'copyright_name' => getConfig('footer', 'copyright_name', $site['name']),
    'copyright_url' => getConfig('footer', 'copyright_url', ''),
    'icp_enabled' => (bool)getConfig('footer', 'icp_enabled', false),
    'icp_number' => getConfig('footer', 'icp_number', ''),
];
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>站点设置 - MoeHome 管理后台</title>

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
                    <i class="fas fa-cog"></i>
                    站点设置
                </h1>
                <div class="topbar-actions">
                    <button class="btn btn-secondary btn-sm" data-theme-toggle>
                        <i class="fas fa-adjust"></i>
                    </button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo h($messageType); ?> fade-in">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo h($message); ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-globe"></i>
                                站点信息
                            </h2>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="site_name">站点名称</label>
                                <input type="text" id="site_name" name="site_name" class="form-input" value="<?php echo h($site['name']); ?>" placeholder="YourName" />
                                <div class="form-hint">导航栏和页面标题使用的名称</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="site_tagline">站点标语</label>
                                <input type="text" id="site_tagline" name="site_tagline" class="form-input" value="<?php echo h($site['tagline']); ?>" placeholder="技术博主 / 开源爱好者" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="site_url">站点 URL</label>
                                <input type="url" id="site_url" name="site_url" class="form-input" value="<?php echo h($site['url']); ?>" placeholder="https://example.com" />
                                <div class="form-hint">网站完整 URL，结尾不带斜杠</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="site_og_image">社交分享图片</label>
                                <input type="url" id="site_og_image" name="site_og_image" class="form-input" value="<?php echo h($site['og_image']); ?>" placeholder="https://example.com/images/og.png" />
                                <div class="form-hint">分享到社交平台时显示的图片</div>
                            </div>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user"></i>
                                个人资料
                            </h2>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="profile_name">显示名称</label>
                                <input type="text" id="profile_name" name="profile_name" class="form-input" value="<?php echo h($profile['name']); ?>" placeholder="YourName" />
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="profile_avatar">头像路径</label>
                                <input type="text" id="profile_avatar" name="profile_avatar" class="form-input" value="<?php echo h($profile['avatar']); ?>" placeholder="images/avatar.webp" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="profile_tagline_prefix">标语前缀</label>
                                <input type="text" id="profile_tagline_prefix" name="profile_tagline_prefix" class="form-input" value="<?php echo h($profile['tagline_prefix']); ?>" placeholder="🐾" />
                                <div class="form-hint">显示在标语前的图标或文字</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="profile_tagline_highlight">标语高亮文字</label>
                                <input type="text" id="profile_tagline_highlight" name="profile_tagline_highlight" class="form-input" value="<?php echo h($profile['tagline_highlight']); ?>" placeholder="欢迎来到我的主页！" />
                            </div>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-search"></i>
                                SEO 设置
                            </h2>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="seo_title">页面标题</label>
                            <input type="text" id="seo_title" name="seo_title" class="form-input" value="<?php echo h($seo['title']); ?>" placeholder="YourName - 技术博主" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="seo_description">页面描述</label>
                            <textarea id="seo_description" name="seo_description" class="form-textarea" placeholder="个人主页的简短描述..."><?php echo h($seo['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="seo_keywords">关键词</label>
                            <input type="text" id="seo_keywords" name="seo_keywords" class="form-input" value="<?php echo h(is_array($seo['keywords']) ? implode(', ', $seo['keywords']) : $seo['keywords']); ?>" placeholder="关键词1, 关键词2, 关键词3" />
                            <div class="form-hint">多个关键词用英文逗号分隔</div>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-copyright"></i>
                                页脚设置
                            </h2>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="footer_copyright_year">版权年份</label>
                                <input type="text" id="footer_copyright_year" name="footer_copyright_year" class="form-input" value="<?php echo h($footer['copyright_year']); ?>" placeholder="2024" />
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="footer_copyright_name">版权名称</label>
                                <input type="text" id="footer_copyright_name" name="footer_copyright_name" class="form-input" value="<?php echo h($footer['copyright_name']); ?>" placeholder="YourName" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="footer_copyright_url">版权链接</label>
                            <input type="url" id="footer_copyright_url" name="footer_copyright_url" class="form-input" value="<?php echo h($footer['copyright_url']); ?>" placeholder="https://example.com" />
                        </div>

                        <div class="form-switch">
                            <div class="form-switch-info">
                                <div class="form-switch-title">显示 ICP 备案号</div>
                                <div class="form-switch-desc">在页脚显示 ICP 备案信息</div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="footer_icp_enabled" value="1" <?php echo $footer['icp_enabled'] ? 'checked' : ''; ?> />
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="form-group" style="margin-top: 16px;">
                            <label class="form-label" for="footer_icp_number">ICP 备案号</label>
                            <input type="text" id="footer_icp_number" name="footer_icp_number" class="form-input" value="<?php echo h($footer['icp_number']); ?>" placeholder="京ICP备XXXXXXXX号" />
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo"></i>
                                重置
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                保存设置
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>
