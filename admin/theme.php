<?php
/**
 * MoeHome 后台管理 - 主题设置
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $theme = [
        'default' => $_POST['default'] ?? 'light',
        'defaultScheme' => [
            'light' => $_POST['lightScheme'] ?? 'coralOrange',
            'dark' => $_POST['darkScheme'] ?? 'cyberGreen',
        ]
    ];

    setConfig('theme', 'default', $theme['default']);
    setConfig('theme', 'defaultScheme', $theme['defaultScheme'], 'json');

    $message = '主题设置已保存';
}

$themeDefault = getConfig('theme', 'default', 'light');
$themeDefaultScheme = getConfig('theme', 'defaultScheme', ['light' => 'coralOrange', 'dark' => 'cyberGreen']);
if (is_string($themeDefaultScheme)) {
    $themeDefaultScheme = json_decode($themeDefaultScheme, true) ?: ['light' => 'coralOrange', 'dark' => 'cyberGreen'];
}

$lightSchemes = [
    'coralOrange' => ['#ff6b4a', '珊瑚橙'],
    'mintGreen' => ['#10b981', '薄荷绿'],
    'oceanBlue' => ['#3b82f6', '海洋蓝'],
    'lavender' => ['#8b5cf6', '薰衣草紫'],
    'sunset' => ['#f59e0b', '日落橙'],
    'berry' => ['#ec4899', '浆果粉'],
    'forest' => ['#22c55e', '森林绿'],
    'slate' => ['#64748b', '石板灰'],
];

$darkSchemes = [
    'cyberGreen' => ['#00ff9f', '赛博绿'],
    'neonPink' => ['#ff0080', '霓虹粉'],
    'electricBlue' => ['#00d4ff', '电光蓝'],
    'sunset' => ['#ff6b4a', '日落橙'],
    'aurora' => ['#10b981', '极光绿'],
    'midnight' => ['#6366f1', '午夜紫'],
    'matrix' => ['#22c55e', '矩阵绿'],
    'hacker' => ['#0ea5e9', '黑客蓝'],
];
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>主题设置 - MoeHome 管理后台</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/admin.css" />

    <style>
        .scheme-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }

        .scheme-option {
            padding: 12px;
            background: var(--bg-secondary);
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .scheme-option:hover {
            border-color: var(--border);
        }

        .scheme-option.selected {
            border-color: var(--accent);
            background: var(--accent-dim);
        }

        .scheme-preview {
            display: flex;
            gap: 4px;
            margin-bottom: 8px;
        }

        .scheme-color {
            width: 24px;
            height: 24px;
            border-radius: 4px;
        }

        .scheme-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .theme-preview {
            padding: 24px;
            background: var(--bg-secondary);
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .preview-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .preview-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--accent);
        }

        .preview-name {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .preview-tagline {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <h1 class="topbar-title">
                    <i class="fas fa-palette"></i>
                    主题设置
                </h1>
                <div class="topbar-actions">
                    <button class="btn btn-secondary btn-sm" data-theme-toggle>
                        <i class="fas fa-adjust"></i>
                    </button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($message): ?>
                <div class="alert alert-success fade-in">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo h($message); ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-sun"></i>
                                默认模式
                            </h2>
                        </div>

                        <div style="display: flex; gap: 16px;">
                            <label class="scheme-option <?php echo $themeDefault === 'light' ? 'selected' : ''; ?>" style="flex: 1; text-align: center;">
                                <input type="radio" name="default" value="light" <?php echo $themeDefault === 'light' ? 'checked' : ''; ?> style="display: none;" />
                                <i class="fas fa-sun" style="font-size: 2rem; color: var(--text-primary); margin-bottom: 8px;"></i>
                                <div class="scheme-name">浅色模式</div>
                            </label>

                            <label class="scheme-option <?php echo $themeDefault === 'dark' ? 'selected' : ''; ?>" style="flex: 1; text-align: center;">
                                <input type="radio" name="default" value="dark" <?php echo $themeDefault === 'dark' ? 'checked' : ''; ?> style="display: none;" />
                                <i class="fas fa-moon" style="font-size: 2rem; color: var(--text-primary); margin-bottom: 8px;"></i>
                                <div class="scheme-name">深色模式</div>
                            </label>

                            <label class="scheme-option <?php echo $themeDefault === 'auto' ? 'selected' : ''; ?>" style="flex: 1; text-align: center;">
                                <input type="radio" name="default" value="auto" <?php echo $themeDefault === 'auto' ? 'checked' : ''; ?> style="display: none;" />
                                <i class="fas fa-adjust" style="font-size: 2rem; color: var(--text-primary); margin-bottom: 8px;"></i>
                                <div class="scheme-name">跟随系统</div>
                            </label>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-palette"></i>
                                浅色模式配色
                            </h2>
                        </div>

                        <input type="hidden" name="lightScheme" id="lightSchemeInput" value="<?php echo h($themeDefaultScheme['light'] ?? 'coralOrange'); ?>" />

                        <div class="scheme-grid">
                            <?php foreach ($lightSchemes as $key => $scheme): ?>
                            <div class="scheme-option <?php echo ($themeDefaultScheme['light'] ?? '') === $key ? 'selected' : ''; ?>" onclick="selectScheme(this, 'light', '<?php echo h($key); ?>')">
                                <div class="scheme-preview">
                                    <div class="scheme-color" style="background: <?php echo h($scheme[0]); ?>;"></div>
                                </div>
                                <div class="scheme-name"><?php echo h($scheme[1]); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-moon"></i>
                                深色模式配色
                            </h2>
                        </div>

                        <input type="hidden" name="darkScheme" id="darkSchemeInput" value="<?php echo h($themeDefaultScheme['dark'] ?? 'cyberGreen'); ?>" />

                        <div class="scheme-grid">
                            <?php foreach ($darkSchemes as $key => $scheme): ?>
                            <div class="scheme-option <?php echo ($themeDefaultScheme['dark'] ?? '') === $key ? 'selected' : ''; ?>" onclick="selectScheme(this, 'dark', '<?php echo h($key); ?>')">
                                <div class="scheme-preview">
                                    <div class="scheme-color" style="background: <?php echo h($scheme[0]); ?>;"></div>
                                </div>
                                <div class="scheme-name"><?php echo h($scheme[1]); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="theme-preview" id="themePreview">
                            <div class="preview-header">
                                <div class="preview-avatar"></div>
                                <div>
                                    <div class="preview-name">预览名称</div>
                                    <div class="preview-tagline">🐾 欢迎来到我的主页</div>
                                </div>
                            </div>
                            <div style="padding: 16px; background: var(--bg-tertiary); border-radius: 8px; font-family: var(--font-mono); font-size: 0.875rem;">
                                <div style="margin-bottom: 4px;"><span style="color: var(--accent);">$</span> whoami</div>
                                <div style="padding-left: 16px; color: var(--text-secondary);">开源爱好者 / 开发者</div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                保存主题设置
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        var selectedSchemes = {
            light: '<?php echo h($themeDefaultScheme['light'] ?? 'coralOrange'); ?>',
            dark: '<?php echo h($themeDefaultScheme['dark'] ?? 'cyberGreen'); ?>'
        };

        var schemes = <?php echo json_encode([
            'light' => $lightSchemes,
            'dark' => $darkSchemes
        ]); ?>;

        function selectScheme(element, mode, key) {
            var parent = element.closest('.card');
            parent.querySelectorAll('.scheme-option').forEach(function(opt) {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');

            selectedSchemes[mode] = key;
            document.getElementById(mode + 'SchemeInput').value = key;

            updatePreview();
        }

        function updatePreview() {
            var mode = document.querySelector('input[name="default"]:checked').value;
            if (mode === 'auto') {
                mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            var scheme = schemes[mode][selectedSchemes[mode]];
            if (scheme) {
                document.documentElement.style.setProperty('--accent', scheme[0]);
                document.getElementById('themePreview').style.setProperty('--accent', scheme[0]);
            }
        }

        document.querySelectorAll('input[name="default"]').forEach(function(radio) {
            radio.addEventListener('change', updatePreview);
        });

        updatePreview();
    </script>
</body>
</html>
