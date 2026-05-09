<?php
/**
 * MoeHome 虚拟主机版 - 动态页面
 * 版本 v4.0 - CMS 内容管理系统
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

$config = [];
$configFile = __DIR__ . '/api/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/api/config.example.php';
}

$site = $config['site'] ?? [];
$theme = $config['theme'] ?? [];
$footer = $config['footer'] ?? [];

$siteUrl = $site['url'] ?? '';
$siteName = $site['name'] ?? '';
$ogImage = $site['ogImage'] ?? '';

$pageTitle = '动态 | ' . $siteName;
$pageTagline = '我的碎片化分享，这里记录分享实用经验、生活点滴、瞬间感悟。';
$pageDescription = 'MoeHome 个人主页动态页面';
$pageKeywords = '动态, 博客, 个人主页';

$themeDefault = $theme['default'] ?? 'light';
$themeDefaultScheme = $theme['defaultScheme'] ?? ['light' => 'coralOrange', 'dark' => 'cyberGreen'];
$themeDefaultSchemeData = $themeDefaultScheme[$themeDefault] ?? 'coralOrange';

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

$icpEnabled = !empty($footer['icp']['enabled']);
$icpNumber = $icpEnabled ? htmlspecialchars($footer['icp']['number'] ?? '', ENT_QUOTES, 'UTF-8') : '';
$copyrightYear = htmlspecialchars(($footer['copyright']['year'] ?? date('Y')), ENT_QUOTES, 'UTF-8');
$copyrightName = htmlspecialchars(($footer['copyright']['name'] ?? $siteName), ENT_QUOTES, 'UTF-8');
$copyrightUrl = htmlspecialchars(($footer['copyright']['url'] ?? '#'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="zh-CN" data-theme="<?php echo htmlspecialchars($themeDefault); ?>" data-scheme="<?php echo htmlspecialchars($themeDefaultSchemeData); ?>">
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

        <style>
            :root {
                --navbar-height: 56px;
                --navbar-bg: transparent;
                --z-dropdown: 600;
                --radius-md: 12px;
                --bg-primary: #ffffff;
                --bg-secondary: #f8f9fa;
                --text-primary: #1a1a2e;
                --text-secondary: #6b7280;
                --border: #e5e7eb;
                --accent: #ff6b4a;
                --accent-dim: rgba(255, 107, 74, 0.1);
                --hover-bg: rgba(0, 0, 0, 0.05);
                --active-bg: rgba(255, 107, 74, 0.1);
                --space-2: 8px;
                --space-3: 12px;
                --space-4: 16px;
                --space-6: 24px;
                --space-8: 32px;
                --space-10: 40px;
            }

            [data-theme="dark"] {
                --bg-primary: #0f0f1a;
                --bg-secondary: #1a1a2e;
                --text-primary: #e5e7eb;
                --text-secondary: #9ca3af;
                --border: #374151;
                --accent: #00ff9f;
                --accent-dim: rgba(0, 255, 159, 0.1);
                --hover-bg: rgba(255, 255, 255, 0.05);
                --active-bg: rgba(0, 255, 159, 0.1);
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

            .moments-wrapper {
                width: 100%;
                max-width: 600px;
            }

            .moments-page-header {
                text-align: center;
                padding: var(--space-10) 0 var(--space-6);
            }

            .moments-page-header h1 {
                font-family: "JetBrains Mono", monospace;
                font-size: clamp(1.75rem, 5vw, 2.75rem);
                font-weight: 600;
                margin: 0;
                letter-spacing: 0.04em;
                background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .moments-page-header p {
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

            .moments-section {
                text-align: left;
                padding: 0 0 var(--space-8);
                min-height: calc(100vh - 200px);
            }

            .moments-filter {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: center;
                margin-bottom: var(--space-6);
                padding: 0 var(--space-4);
            }

            .moments-filter .filter-tag {
                padding: 6px 16px;
                border-radius: 20px;
                border: 1px solid var(--border);
                background: var(--bg-secondary);
                color: var(--text-secondary);
                font-size: 0.875rem;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .moments-filter .filter-tag:hover {
                border-color: var(--accent);
                color: var(--accent);
            }

            .moments-filter .filter-tag.active {
                background: var(--accent);
                border-color: var(--accent);
                color: var(--bg-primary);
            }

            .moments-feed {
                display: flex;
                flex-direction: column;
                gap: var(--space-4);
            }

            .moment-card {
                background: var(--bg-secondary);
                border-radius: var(--radius-md);
                padding: var(--space-6);
                border: 1px solid var(--border);
                transition: all 0.2s ease;
            }

            .moment-card:hover {
                border-color: var(--accent);
                box-shadow: 0 4px 16px var(--accent-dim);
            }

            .moment-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: var(--space-4);
            }

            .moment-date {
                color: var(--text-secondary);
                font-size: 0.875rem;
                font-family: "JetBrains Mono", monospace;
            }

            .moment-pinned {
                padding: 2px 8px;
                background: var(--accent);
                color: var(--bg-primary);
                font-size: 0.75rem;
                border-radius: 4px;
                font-weight: 500;
            }

            .moment-content {
                color: var(--text-primary);
                font-size: 1rem;
                line-height: 1.8;
                white-space: pre-wrap;
                word-break: break-word;
            }

            .moment-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: var(--space-4);
            }

            .moment-tag {
                padding: 4px 12px;
                background: var(--accent-dim);
                color: var(--accent);
                font-size: 0.8125rem;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .moment-tag:hover {
                background: var(--accent);
                color: var(--bg-primary);
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

            .moments-loading {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 200px;
                color: var(--text-secondary);
            }

            .moments-error {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 200px;
                color: var(--text-secondary);
                text-align: center;
            }

            .moments-empty {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 200px;
                color: var(--text-secondary);
                text-align: center;
            }

            @media (max-width: 768px) {
                .moments-page-header p {
                    font-size: 0.875rem;
                    max-width: 280px;
                }
                .moment-card {
                    padding: var(--space-4);
                }
            }
        </style>
    </head>
    <body>
        <nav class="navbar" id="navbar">
            <div class="navbar-inner">
                <a href="index.php" class="navbar-brand">
                    <span class="prompt">$</span>
                    <span class="brand-name"><?php echo htmlspecialchars($siteName); ?></span>
                </a>
                <div class="navbar-menu" id="navbar-menu">
                    <a href="index.php" class="nav-link">首页</a>
                    <a href="moments.php" class="nav-link active">动态</a>
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
            <a href="moments.php" class="nav-link active">动态</a>
        </div>

        <div class="container">
            <div class="moments-wrapper">
                <header class="moments-page-header">
                    <h1>动态</h1>
                    <p><?php echo htmlspecialchars($pageTagline); ?></p>
                </header>

                <div class="divider"></div>

                <section class="moments-section">
                    <div class="moments-filter" id="moments-filter">
                        <span class="filter-tag active" data-tag="">全部</span>
                    </div>

                    <div class="moments-feed" id="moments-feed">
                        <div class="moments-loading">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 12px;"></i>
                            <span>加载中...</span>
                        </div>
                    </div>
                </section>

                <footer class="footer">
                    <p>&copy; <?php echo $copyrightYear; ?> <a href="<?php echo $copyrightUrl; ?>"><?php echo $copyrightName; ?></a></p>
                    <?php if ($icpEnabled): ?>
                    <p><a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer"><?php echo $icpNumber; ?></a></p>
                    <?php endif; ?>
                </footer>
            </div>
        </div>

        <script src="theme-data.js"></script>
        <script src="theme-utils.js"></script>
        <script>
            (function() {
                const API_BASE = 'api';
                const feedContainer = document.getElementById('moments-feed');
                const filterContainer = document.getElementById('moments-filter');
                let currentTag = '';

                async function loadMoments(tag = '') {
                    try {
                        const response = await fetch(`${API_BASE}/content.php?action=moments&count=20&tag=${encodeURIComponent(tag)}`);
                        const data = await response.json();
                        
                        if (data.moments && data.moments.length > 0) {
                            renderMoments(data.moments);
                            await loadTags();
                        } else {
                            feedContainer.innerHTML = `
                                <div class="moments-empty">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                    <p>暂无动态</p>
                                </div>
                            `;
                        }
                    } catch (error) {
                        feedContainer.innerHTML = `
                            <div class="moments-error">
                                <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 16px;"></i>
                                <p>加载失败，请稍后重试</p>
                            </div>
                        `;
                    }
                }

                function renderMoments(moments) {
                    feedContainer.innerHTML = moments.map(moment => `
                        <article class="moment-card">
                            <div class="moment-header">
                                <span class="moment-date">${moment.created_at}</span>
                                ${moment.is_pinned ? '<span class="moment-pinned">置顶</span>' : ''}
                            </div>
                            <div class="moment-content">${escapeHtml(moment.content)}</div>
                            ${moment.tags ? `
                                <div class="moment-tags">
                                    ${moment.tags.split(',').map(tag => `<span class="moment-tag" data-tag="${tag.trim()}">#${tag.trim()}</span>`).join('')}
                                </div>
                            ` : ''}
                        </article>
                    `).join('');
                }

                async function loadTags() {
                    try {
                        const response = await fetch(`${API_BASE}/content.php?action=moments&page=1&limit=100`);
                        const data = await response.json();
                        
                        const tags = new Set();
                        data.moments.forEach(m => {
                            if (m.tags) {
                                m.tags.split(',').forEach(t => tags.add(t.trim()));
                            }
                        });
                        
                        const tagButtons = Array.from(tags).filter(t => t).map(tag => 
                            `<span class="filter-tag" data-tag="${tag}">#${tag}</span>`
                        ).join('');
                        
                        filterContainer.innerHTML = `<span class="filter-tag active" data-tag="">全部</span>${tagButtons}`;
                        
                        filterContainer.querySelectorAll('.filter-tag').forEach(btn => {
                            btn.addEventListener('click', function() {
                                filterContainer.querySelectorAll('.filter-tag').forEach(b => b.classList.remove('active'));
                                this.classList.add('active');
                                currentTag = this.dataset.tag || '';
                                loadMoments(currentTag);
                            });
                        });
                    } catch (error) {
                        console.error('Failed to load tags:', error);
                    }
                }

                function escapeHtml(str) {
                    if (!str) return '';
                    return str.replace(/&/g, '&amp;')
                              .replace(/</g, '&lt;')
                              .replace(/>/g, '&gt;')
                              .replace(/"/g, '&quot;');
                }

                loadMoments();

                document.addEventListener('click', function(e) {
                    const target = e.target;
                    if (target.classList.contains('moment-tag')) {
                        filterContainer.querySelectorAll('.filter-tag').forEach(b => b.classList.remove('active'));
                        filterContainer.querySelector('[data-tag=""]').classList.remove('active');
                        const tagBtn = filterContainer.querySelector(`[data-tag="${target.dataset.tag}"]`);
                        if (tagBtn) tagBtn.classList.add('active');
                        currentTag = target.dataset.tag;
                        loadMoments(currentTag);
                    }
                });
            })();
        </script>
    </body>
</html>
