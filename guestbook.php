<?php
/**
 * MoeHome 虚拟主机版 - 留言板页面
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/api/config.example.php';

$site = $config['site'] ?? [];
$theme = $config['theme'] ?? [];
$guestbook = $config['guestbook'] ?? [];
$footer = $config['footer'] ?? [];

$siteUrl = $site['url'] ?? '';
$siteName = $site['name'] ?? '';
$ogImage = $site['ogImage'] ?? '';

$pages = $config['pages']['guestbook'] ?? [];
$pageTitle = ($pages['title'] ?? '留言') . ' | ' . $siteName;
$pageTagline = $pages['tagline'] ?? '欢迎在这里留下你的足迹...';
$pageDescription = $pages['description'] ?? '';
$pageKeywords = isset($pages['keywords']) && is_array($pages['keywords']) ? implode(', ', $pages['keywords']) : '';

$themeDefault = $theme['default'] ?? 'light';
$themeDefaultScheme = $theme['defaultScheme'] ?? ['light' => 'coralOrange', 'dark' => 'cyberGreen'];

$guestbookEnabled = $guestbook['enabled'] ?? false;
$guestbookProvider = $guestbook['provider'] ?? 'waline';
$guestbookServer = $guestbook['server'] ?? '';
$guestbookSite = $guestbook['site'] ?? $siteName;
$guestbookPlaceholder = $guestbook['placeholder'] ?? '欢迎留下你的信号...';
$limitsJson = json_encode($guestbook['limits'] ?? ['comments' => ['newest' => 30, 'hot' => 30], 'barrage' => ['pinned' => 5, 'hot' => 10, 'latest' => 5]], JSON_UNESCAPED_UNICODE);

$themeDefaultSchemeJson = json_encode($themeDefaultScheme, JSON_UNESCAPED_UNICODE);
$themeInitScript = <<<HTML
<script>
(function() {
    var defaultMode = '{$themeDefault}';
    var defaultScheme = {$themeDefaultSchemeJson};
    var config = {
        default: defaultMode,
        defaultScheme: defaultScheme
    };

    var saved = null;
    try {
        var temp = localStorage.getItem('moehome-theme');
        if (temp) saved = JSON.parse(temp);
    } catch(e) {}

    var mode = saved?.mode || config.default;
    var scheme = saved?.scheme || config.defaultScheme[mode] || 'coralOrange';

    document.documentElement.setAttribute('data-theme', mode);
    document.documentElement.setAttribute('data-scheme', scheme);
})();
</script>
HTML;

$commentSdk = '';
if ($guestbookEnabled && !empty($guestbookServer)) {
    if ($guestbookProvider === 'waline') {
        $commentSdk = <<<HTML
        <script src="https://cdn.jsdelivr.net/npm/@waline/client@3/dist/waline.js"></script>
HTML;
    } elseif ($guestbookProvider === 'artalk') {
        $commentSdk = <<<HTML
        <script src="https://cdn.jsdelivr.net/npm/artalk@2/dist/Artalk.js"></script>
HTML;
    }
}
?>
<!doctype html>
<html lang="zh-CN" data-theme="<?php echo htmlspecialchars($themeDefault); ?>" data-scheme="<?php echo htmlspecialchars($themeDefaultScheme[$themeDefault] ?? 'coralOrange'); ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, shrink-to-fit=no" />

        <title><?php echo htmlspecialchars($pageTitle); ?></title>

        <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>" />
        <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>" />

        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>" />
        <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>" />
        <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>" />

        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>" />
        <meta property="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>" />
        <meta property="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>" />

        <link rel="icon" type="image/webp" href="images/avatar.webp" />

        <?php echo $themeInitScript; ?>

        <link rel="preconnect" href="https://fonts.googleapis.cn" />
        <link rel="preconnect" href="https://fonts.gstatic.cn" crossorigin />
        <link href="https://fonts.googleapis.cn/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <?php if ($guestbookProvider === 'waline'): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@waline/client@3/dist/waline.css" />
        <?php elseif ($guestbookProvider === 'artalk'): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/artalk@2/dist/Artalk.css" />
        <?php endif; ?>

        <style>
            :root {
                --navbar-height: 56px;
                --navbar-bg: transparent;
                --z-dropdown: 600;
                --radius-md: 12px;
                --radius-lg: 16px;
                --radius-sm: 8px;
                --bg-primary: #ffffff;
                --bg-secondary: #f8f9fa;
                --text-primary: #1a1a2e;
                --text-secondary: #6b7280;
                --border: #e5e7eb;
                --accent: #ff6b4a;
                --accent-dim: rgba(255, 107, 74, 0.1);
                --accent-rgb: 255, 107, 74;
                --hover-bg: rgba(0, 0, 0, 0.05);
                --active-bg: rgba(255, 107, 74, 0.1);
                --glass-border-top: rgba(255, 255, 255, 0.8);
                --glass-border-bottom: rgba(0, 0, 0, 0.05);
                --glass-border-side: rgba(255, 255, 255, 0.5);
                --glass-outer-shadow: rgba(0, 0, 0, 0.1);
                --space-1: 4px;
                --space-2: 8px;
                --space-3: 12px;
                --space-4: 16px;
                --space-6: 24px;
                --space-8: 32px;
                --space-10: 40px;
                --font-mono: "JetBrains Mono", "SF Mono", "Cascadia Code", Menlo, Monaco, Consolas, monospace;
            }

            [data-theme="dark"] {
                --bg-primary: #0f0f1a;
                --bg-secondary: #1a1a2e;
                --text-primary: #e5e7eb;
                --text-secondary: #9ca3af;
                --border: #374151;
                --accent: #00ff9f;
                --accent-dim: rgba(0, 255, 159, 0.1);
                --accent-rgb: 0, 255, 159;
                --hover-bg: rgba(255, 255, 255, 0.05);
                --active-bg: rgba(0, 255, 159, 0.1);
                --glass-border-top: rgba(255, 255, 255, 0.05);
                --glass-border-bottom: rgba(0, 0, 0, 0.2);
                --glass-border-side: rgba(255, 255, 255, 0.05);
                --glass-outer-shadow: rgba(0, 0, 0, 0.3);
            }

            *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
            html { scroll-behavior: smooth; height: 100%; -webkit-text-size-adjust: 100%; }
            body {
                font-family: "Avenir Next", "SF Pro Text", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, "Noto Sans", sans-serif;
                background: var(--bg-primary);
                color: var(--text-primary);
                overflow-x: hidden;
                overflow-y: auto;
                min-height: 100%;
                position: relative;
            }

            .navbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: var(--navbar-height);
                background: var(--navbar-bg);
                z-index: 1000;
                transition: background 0.3s ease;
            }
            .navbar.scrolled {
                background: var(--bg-primary);
                border-bottom: 1px solid var(--border);
            }
            .navbar-inner {
                max-width: 720px;
                margin: 0 auto;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 24px;
                gap: 24px;
            }
            .navbar-brand {
                display: flex;
                align-items: center;
                gap: 6px;
                text-decoration: none;
                color: var(--text-primary);
                font-size: 16px;
                font-weight: 500;
            }
            .navbar-brand .prompt, .navbar-brand .brand-name { color: var(--accent); }
            .navbar-menu { display: flex; align-items: center; gap: 8px; flex: 1; justify-content: center; }
            .nav-link {
                position: relative;
                padding: 8px 16px;
                color: var(--text-secondary);
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: color 0.2s ease;
                white-space: nowrap;
            }
            .nav-link.active, .nav-link:hover { color: var(--accent); }
            .navbar-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
            .nav-theme-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                background: none;
                border: none;
                border-radius: 8px;
                color: var(--text-secondary);
                cursor: pointer;
            }
            .nav-theme-toggle:hover { color: var(--accent); }
            .navbar-toggle { display: none; width: 36px; height: 36px; background: none; border: none; border-radius: 8px; color: var(--text-secondary); cursor: pointer; }

            .theme-dropdown, .nav-mobile-dropdown {
                position: fixed;
                top: calc(var(--navbar-height) + 8px);
                z-index: var(--z-dropdown);
                min-width: 180px;
                max-width: 280px;
                background: var(--bg-primary);
                border: 1px solid var(--border);
                border-radius: var(--radius-md);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(-8px);
                transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
            }
            .theme-dropdown { right: 24px; }
            .nav-mobile-dropdown { right: 16px; }
            .theme-dropdown.is-active, .nav-mobile-dropdown.is-active {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateY(0);
            }

            @media (max-width: 768px) {
                .navbar-menu { display: none; }
                .nav-theme-toggle { display: flex; }
                .navbar-toggle { display: flex; }
                .navbar-inner { padding: 0 16px; gap: 8px; }
                .navbar-brand { font-size: 14px; }
            }
            @media (min-width: 769px) {
                .navbar-menu { display: flex; }
                .nav-theme-toggle { display: flex; }
                .navbar-toggle { display: none; }
            }

            .container {
                position: relative;
                z-index: 10;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 40px 20px;
                padding-top: calc(var(--navbar-height) + 40px);
                padding-bottom: 88px;
                width: 100%;
                max-width: 100%;
            }

            .guestbook-wrapper {
                width: 100%;
                max-width: 600px;
                text-align: center;
            }

            .guestbook-page-header {
                text-align: center;
                padding: var(--space-10) 0 var(--space-6);
            }

            .guestbook-page-header h1 {
                font-family: var(--font-mono);
                font-size: clamp(1.75rem, 5vw, 2.75rem);
                font-weight: 600;
                margin: 0;
                letter-spacing: 0.04em;
                background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .guestbook-page-header p {
                color: var(--text-secondary);
                margin: var(--space-2) auto 0;
                font-size: 1rem;
                font-weight: 300;
                line-height: 1.6;
                letter-spacing: 0.02em;
                max-width: 340px;
            }

            .divider {
                width: 100%;
                height: 1px;
                background: linear-gradient(
                    90deg,
                    transparent 0%,
                    var(--border) 15%,
                    var(--border) 50%,
                    var(--border) 85%,
                    transparent 100%
                );
                margin: var(--space-6) 0;
            }

            .signal-terminal {
                background: var(--bg-secondary);
                border-radius: var(--radius-lg);
                overflow: hidden;
                text-align: left;
                margin-bottom: var(--space-8);
                box-shadow:
                    inset 0 1px 0 0 var(--glass-border-top),
                    inset 0 -1px 0 0 var(--glass-border-bottom),
                    inset 1px 0 0 0 var(--glass-border-side),
                    inset -1px 0 0 0 var(--glass-border-side),
                    0 4px 20px -4px var(--glass-outer-shadow);
            }

            .signal-terminal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: var(--space-2) var(--space-3);
                background: var(--bg-primary);
                border-bottom: none;
                box-shadow: inset 0 -1px 0 0 var(--glass-border-bottom);
                font-family: var(--font-mono);
            }

            .signal-terminal-left {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .terminal-dots {
                display: flex;
                gap: 6px;
            }

            .terminal-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
            }

            .terminal-dot.red { background: #ff5f57; }
            .terminal-dot.yellow { background: #febc2e; }
            .terminal-dot.green { background: #28c840; }

            .signal-terminal-title {
                color: var(--text-secondary);
                font-size: 0.875rem;
                font-weight: 500;
            }

            .signal-terminal-status {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                color: var(--accent);
                font-size: 0.875rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .signal-terminal-status::before {
                content: '';
                width: 6px;
                height: 6px;
                background: var(--accent);
                border-radius: 50%;
                animation: signal-pulse 1.5s ease-in-out infinite;
            }

            @keyframes signal-pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(0.8); }
            }

            .signal-stream {
                position: relative;
                width: 100%;
                height: 240px;
                min-height: 240px;
                max-height: 240px;
                overflow: hidden;
                flex-shrink: 0;
            }

            @media (max-width: 640px) {
                .signal-stream {
                    height: 320px;
                    min-height: 320px;
                    max-height: 320px;
                }
            }

            .signal-canvas {
                position: absolute;
                inset: 0;
                overflow: hidden;
                -webkit-mask-image: linear-gradient(
                    to right,
                    transparent 0%,
                    black 50px,
                    black calc(100% - 50px),
                    transparent 100%
                );
                mask-image: linear-gradient(
                    to right,
                    transparent 0%,
                    black 50px,
                    black calc(100% - 50px),
                    transparent 100%
                );
            }

            @media (max-width: 640px) {
                .signal-canvas {
                    display: flex;
                    flex-direction: column;
                    padding: var(--space-3);
                    -webkit-mask-image: linear-gradient(
                        to bottom,
                        transparent 0%,
                        black 40px,
                        black calc(100% - 40px),
                        transparent 100%
                    );
                    mask-image: linear-gradient(
                        to bottom,
                        transparent 0%,
                        black 40px,
                        black calc(100% - 40px),
                        transparent 100%
                    );
                }
            }

            .signal-terminal-divider {
                height: 1px;
                background: linear-gradient(
                    90deg,
                    transparent 0%,
                    var(--border) 20%,
                    var(--accent) 50%,
                    var(--border) 80%,
                    transparent 100%
                );
                margin: 0 var(--space-3);
            }

            .signal-editor {
                padding: 0;
                box-shadow: inset 0 1px 0 0 var(--glass-border-top);
            }

            .comments-section {
                text-align: left;
            }

            #comments-container {
                min-height: 300px;
            }

            .footer {
                text-align: center;
                margin-top: var(--space-8);
                color: var(--text-secondary);
                font-size: 0.875rem;
            }

            .footer a {
                color: var(--accent);
                text-decoration: none;
            }

            .skeleton {
                background: linear-gradient(90deg, var(--bg-secondary) 25%, var(--border) 50%, var(--bg-secondary) 75%);
                background-size: 200% 100%;
                animation: skeleton-loading 1.5s ease-in-out infinite;
            }

            @keyframes skeleton-loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            .comments-loading, .comments-error {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 200px;
                color: var(--text-secondary);
            }

            @media (max-width: 768px) {
                .guestbook-page-header {
                    padding: var(--space-6) 0 var(--space-4);
                }
                .guestbook-page-header p {
                    font-size: 0.875rem;
                    max-width: 280px;
                }
            }
        </style>

        <link rel="preload" href="style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="style.css"></noscript>

        <?php echo $commentSdk; ?>

        <script>
            window.MOEHOME_GUESTBOOK_CONFIG = {
                provider: '<?php echo htmlspecialchars($guestbookProvider); ?>',
                server: '<?php echo htmlspecialchars($guestbookServer); ?>',
                site: '<?php echo htmlspecialchars($guestbookSite); ?>',
                placeholder: '<?php echo htmlspecialchars($guestbookPlaceholder); ?>',
                limits: <?php echo $limitsJson; ?>
            };
        </script>
    </head>
    <body>
        <a href="#actual-content" class="skip-link">跳到主要内容</a>

        <nav class="navbar" id="navbar">
            <div class="navbar-inner">
                <a href="index.php" class="navbar-brand">
                    <span class="prompt">$</span>
                    <span class="brand-name"><?php echo htmlspecialchars($siteName); ?></span>
                </a>
                <div class="navbar-menu" id="navbar-menu">
                    <a href="index.php" class="nav-link">首页</a>
                    <a href="moments.php" class="nav-link">动态</a>
                    <a href="guestbook.php" class="nav-link active">留言</a>
                </div>
                <div class="navbar-actions">
                    <button class="nav-theme-toggle" id="theme-toggle" aria-label="切换主题">
                        <i class="fas fa-palette"></i>
                    </button>
                    <button class="navbar-toggle" id="navbar-toggle" aria-label="菜单">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </nav>

        <div class="theme-dropdown" id="theme-dropdown">
            <div class="theme-section">
                <div class="theme-section-label">模式</div>
                <div class="theme-mode-list">
                    <button class="theme-mode-item" data-mode="auto"><i class="fas fa-adjust"></i><span>跟随系统</span></button>
                    <button class="theme-mode-item" data-mode="light"><i class="fas fa-sun"></i><span>浅色</span></button>
                    <button class="theme-mode-item" data-mode="dark"><i class="fas fa-moon"></i><span>深色</span></button>
                </div>
            </div>
            <div class="theme-divider"></div>
            <div class="theme-section">
                <div class="theme-section-label">配色方案</div>
                <div class="theme-scheme-list" id="theme-scheme-list"></div>
            </div>
        </div>

        <div class="nav-mobile-dropdown" id="nav-mobile-dropdown">
            <a href="index.php" class="nav-link">首页</a>
            <a href="moments.php" class="nav-link">动态</a>
            <a href="guestbook.php" class="nav-link active">留言</a>
        </div>

        <div class="container">
            <div class="guestbook-wrapper">
                <div class="guestbook-page-header">
                    <h1><?php echo htmlspecialchars($pages['title'] ?? '留言'); ?></h1>
                    <p><?php echo htmlspecialchars($pageTagline); ?></p>
                </div>

                <div class="divider"></div>

                <?php if ($guestbookEnabled && !empty($guestbookServer)): ?>
                <div class="signal-terminal">
                    <div class="signal-terminal-header">
                        <div class="signal-terminal-left">
                            <div class="terminal-dots">
                                <span class="terminal-dot red"></span>
                                <span class="terminal-dot yellow"></span>
                                <span class="terminal-dot green"></span>
                            </div>
                            <span class="signal-terminal-title">signal-station</span>
                        </div>
                        <span class="signal-terminal-status">LIVE</span>
                    </div>
                    <div class="signal-stream" id="signal-stream">
                        <div class="signal-canvas" id="signal-canvas">
                            <div class="comments-loading" id="signal-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>加载弹幕中...</span>
                            </div>
                        </div>
                    </div>
                    <div class="signal-terminal-divider"></div>
                    <div class="signal-editor" id="signal-editor"></div>
                </div>

                <section class="comments-section" id="actual-content">
                    <div id="comments-container">
                        <div class="comments-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>加载评论中...</span>
                        </div>
                    </div>
                </section>
                <?php else: ?>
                <div class="comments-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>留言板功能已禁用或未配置</p>
                </div>
                <?php endif; ?>

                <footer class="footer">
                    <p>&copy; <?php echo date('Y'); ?> <a href="<?php echo htmlspecialchars($footer['copyright']['url'] ?? '#'); ?>"><?php echo htmlspecialchars($footer['copyright']['name'] ?? $siteName); ?></a></p>
                </footer>
            </div>
        </div>

        <script src="media-manager.js" defer></script>
        <script src="theme-data.js" defer></script>
        <script src="theme-utils.js" defer></script>
        <script src="comments-standalone.js" defer></script>
        <script src="guestbook.js" defer></script>
        <script src="app.js" defer></script>
    </body>
</html>
