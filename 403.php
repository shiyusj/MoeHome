<?php
/**
 * MoeHome 虚拟主机版 - 403 禁止访问页面
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

$config = [];
$configFile = __DIR__ . '/api/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/api/config.example.php';
}

$siteName = htmlspecialchars($config['site']['name'] ?? 'MoeHome', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="zh-CN">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>403 - 禁止访问 | <?php echo $siteName; ?></title>
        <style>
            :root {
                --bg-primary: #ffffff;
                --text-primary: #1a1a2e;
                --text-secondary: #6b7280;
                --accent: #ff6b4a;
            }
            @media (prefers-color-scheme: dark) {
                :root {
                    --bg-primary: #0f0f1a;
                    --text-primary: #e5e7eb;
                    --text-secondary: #9ca3af;
                    --accent: #00ff9f;
                }
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: system-ui, -apple-system, sans-serif;
                background: var(--bg-primary);
                color: var(--text-primary);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container { text-align: center; }
            .error-code {
                font-size: clamp(6rem, 20vw, 12rem);
                font-weight: 700;
                background: linear-gradient(135deg, var(--text-primary), var(--accent));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .error-message {
                font-size: 1.5rem;
                color: var(--text-secondary);
                margin: 20px 0 40px;
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
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-code">403</div>
            <p class="error-message">抱歉，您没有权限访问此页面</p>
            <a href="index.php" class="home-link">
                <span>←</span> 返回首页
            </a>
        </div>
    </body>
</html>
