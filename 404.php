<?php
/**
 * MoeHome 虚拟主机版 - 404 页面
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
$siteName = $site['name'] ?? 'MoeHome';

$theme = $config['theme'] ?? [];
$themeDefault = $theme['default'] ?? 'light';
$themeDefaultScheme = $theme['defaultScheme'] ?? ['light' => 'coralOrange', 'dark' => 'cyberGreen'];
$themeDefaultSchemeJson = json_encode($themeDefaultScheme, JSON_UNESCAPED_UNICODE);

$themeInitScript = <<<HTML
<script>
(function() {
    var defaultMode = '{$themeDefault}';
    var defaultScheme = {$themeDefaultSchemeJson};
    var saved = null;
    try {
        var temp = localStorage.getItem('moehome-theme');
        if (temp) saved = JSON.parse(temp);
    } catch(e) {}

    var mode = saved?.mode || defaultMode;
    var scheme = saved?.scheme || defaultScheme[mode] || 'coralOrange';
    document.documentElement.setAttribute('data-theme', mode);
    document.documentElement.setAttribute('data-scheme', scheme);
})();
</script>
HTML;
?>
<!doctype html>
<html lang="zh-CN" data-theme="<?php echo htmlspecialchars($themeDefault); ?>" data-scheme="<?php echo htmlspecialchars($themeDefaultScheme[$themeDefault] ?? 'coralOrange'); ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>404 - 页面未找到 | <?php echo htmlspecialchars($siteName); ?></title>

        <?php echo $themeInitScript; ?>

        <style>
            :root {
                --navbar-height: 56px;
                --bg-primary: #ffffff;
                --bg-secondary: #f8f9fa;
                --text-primary: #1a1a2e;
                --text-secondary: #6b7280;
                --border: #e5e7eb;
                --accent: #ff6b4a;
            }

            [data-theme="dark"] {
                --bg-primary: #0f0f1a;
                --bg-secondary: #1a1a2e;
                --text-primary: #e5e7eb;
                --text-secondary: #9ca3af;
                --border: #374151;
                --accent: #00ff9f;
            }

            *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

            body {
                font-family: "Avenir Next", "SF Pro Text", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", system-ui, sans-serif;
                background: var(--bg-primary);
                color: var(--text-primary);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .container {
                text-align: center;
                padding: 40px 20px;
            }

            .error-code {
                font-family: "JetBrains Mono", "SF Mono", monospace;
                font-size: clamp(6rem, 20vw, 12rem);
                font-weight: 700;
                line-height: 1;
                background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin-bottom: 20px;
            }

            .error-message {
                font-size: clamp(1.25rem, 3vw, 1.5rem);
                color: var(--text-secondary);
                margin-bottom: 40px;
            }

            .home-link {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 12px 32px;
                background: var(--accent);
                color: #fff;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: opacity 0.2s ease;
            }

            .home-link:hover {
                opacity: 0.9;
            }

            .terminal {
                background: var(--bg-secondary);
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 24px;
                margin-top: 40px;
                max-width: 500px;
                font-family: "JetBrains Mono", monospace;
                font-size: 14px;
                text-align: left;
            }

            .terminal-header {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 16px;
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

            .terminal-content {
                color: var(--text-secondary);
            }

            .terminal-line {
                margin-bottom: 8px;
            }

            .terminal-prompt {
                color: var(--accent);
            }

            .terminal-error {
                color: #ef4444;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-code">404</div>
            <p class="error-message">页面未找到</p>

            <a href="index.php" class="home-link">
                <i class="fas fa-home"></i>
                返回首页
            </a>

            <div class="terminal">
                <div class="terminal-header">
                    <div class="terminal-dots">
                        <span class="terminal-dot red"></span>
                        <span class="terminal-dot yellow"></span>
                        <span class="terminal-dot green"></span>
                    </div>
                </div>
                <div class="terminal-content">
                    <div class="terminal-line">
                        <span class="terminal-prompt">$</span> ls -la <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
                    </div>
                    <div class="terminal-line">
                        <span class="terminal-error">ls: cannot access '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>': No such file or directory</span>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
