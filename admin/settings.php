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

$email = [
    'smtp_host' => getConfig('email', 'smtp_host', ''),
    'smtp_port' => getConfig('email', 'smtp_port', 465),
    'smtp_secure' => getConfig('email', 'smtp_secure', 'ssl'),
    'smtp_user' => getConfig('email', 'smtp_user', ''),
    'smtp_password' => getConfig('email', 'smtp_password', ''),
    'from_email' => getConfig('email', 'from_email', ''),
    'from_name' => getConfig('email', 'from_name', $site['name']),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    if (isset($_POST['save_email'])) {
        $emailData = [
            'smtp_host' => trim($_POST['smtp_host'] ?? ''),
            'smtp_port' => (int)($_POST['smtp_port'] ?? 465),
            'smtp_secure' => trim($_POST['smtp_secure'] ?? 'ssl'),
            'smtp_user' => trim($_POST['smtp_user'] ?? ''),
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'from_email' => trim($_POST['from_email'] ?? ''),
            'from_name' => trim($_POST['from_name'] ?? ''),
        ];

        foreach ($emailData as $key => $value) {
            setConfig('email', $key, $value);
        }

        $email['smtp_host'] = $emailData['smtp_host'];
        $email['smtp_port'] = $emailData['smtp_port'];
        $email['smtp_secure'] = $emailData['smtp_secure'];
        $email['smtp_user'] = $emailData['smtp_user'];
        $email['smtp_password'] = $emailData['smtp_password'];
        $email['from_email'] = $emailData['from_email'];
        $email['from_name'] = $emailData['from_name'];

        $message = '邮箱设置已保存';
        $messageType = 'success';
    } elseif (isset($_POST['save_site'])) {
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

        $site['name'] = $data['site_name'];
        $site['tagline'] = $data['site_tagline'];
        $site['url'] = $data['site_url'];
        $site['og_image'] = $data['site_og_image'];
        $profile['name'] = $data['profile_name'];
        $profile['tagline_prefix'] = $data['profile_tagline_prefix'];
        $profile['tagline_highlight'] = $data['profile_tagline_highlight'];
        $profile['avatar'] = $data['profile_avatar'];
        $seo['title'] = $data['seo_title'];
        $seo['description'] = $data['seo_description'];
        $seo['keywords'] = $data['seo_keywords'];
        $footer['copyright_year'] = $data['footer_copyright_year'];
        $footer['copyright_name'] = $data['footer_copyright_name'];
        $footer['copyright_url'] = $data['footer_copyright_url'];
        $footer['icp_enabled'] = $data['footer_icp_enabled'] === '1';
        $footer['icp_number'] = $data['footer_icp_number'];

        $message = '站点设置已保存';
        $messageType = 'success';
    }
}
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

                <form method="POST" action="" id="siteForm">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                    <input type="hidden" name="save_site" value="1" />

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

                <form method="POST" action="" id="emailForm" class="fade-in">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                    <input type="hidden" name="save_email" value="1" />

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-envelope"></i>
                                邮箱设置
                            </h2>
                        </div>

                        <div class="form-group">
                            <div class="form-alert">
                                <i class="fas fa-info-circle"></i>
                                配置 SMTP 邮箱服务后，用户可通过邮箱找回密码
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="smtp_host">SMTP 服务器</label>
                                <input type="text" id="smtp_host" name="smtp_host" class="form-input" value="<?php echo h($email['smtp_host']); ?>" placeholder="smtp.example.com" />
                                <div class="form-hint">邮件服务提供商提供的 SMTP 服务器地址</div>
                            </div>

                            <div class="form-group" style="max-width: 150px;">
                                <label class="form-label" for="smtp_port">SMTP 端口</label>
                                <input type="number" id="smtp_port" name="smtp_port" class="form-input" value="<?php echo h((string)$email['smtp_port']); ?>" placeholder="465" />
                                <div class="form-hint">常用端口: 465 (SSL), 587 (TLS)</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="smtp_secure">加密方式</label>
                            <select id="smtp_secure" name="smtp_secure" class="form-select">
                                <option value="ssl" <?php echo $email['smtp_secure'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="tls" <?php echo $email['smtp_secure'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="" <?php echo $email['smtp_secure'] === '' ? 'selected' : ''; ?>>无</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="smtp_user">SMTP 用户名</label>
                                <input type="text" id="smtp_user" name="smtp_user" class="form-input" value="<?php echo h($email['smtp_user']); ?>" placeholder="your@email.com" autocomplete="username" />
                                <div class="form-hint">通常是完整的邮箱地址</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="smtp_password">SMTP 密码</label>
                                <input type="password" id="smtp_password" name="smtp_password" class="form-input" value="<?php echo h($email['smtp_password']); ?>" placeholder="留空则保持不变" autocomplete="new-password" />
                                <div class="form-hint">部分邮箱需要使用专用授权码</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="from_email">发件人邮箱</label>
                                <input type="email" id="from_email" name="from_email" class="form-input" value="<?php echo h($email['from_email']); ?>" placeholder="noreply@example.com" />
                                <div class="form-hint">与 SMTP 用户名保持一致可提高送达率</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="from_name">发件人名称</label>
                                <input type="text" id="from_name" name="from_name" class="form-input" value="<?php echo h($email['from_name']); ?>" placeholder="<?php echo h($site['name']); ?>" />
                                <div class="form-hint">邮件中显示的发件人名称</div>
                            </div>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="form-hint">
                                <i class="fas fa-shield-alt"></i>
                                密码不会明文保存，留空则保持原设置
                            </div>
                            <div style="display: flex; gap: 12px;">
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i>
                                    重置
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    保存邮箱设置
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <style>
        .form-alert {
            background: var(--accent-dim);
            color: var(--accent);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-alert i {
            font-size: 1rem;
        }

        .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }
    </style>

    <script src="assets/js/admin.js"></script>
</body>
</html>
