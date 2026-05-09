<?php
/**
 * MoeHome 后台管理 - 模块管理
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
    $module = $_POST['module'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $enabled = isset($_POST['enabled']) && $_POST['enabled'] === '1';
        setConfig('modules', $module . '_enabled', $enabled ? '1' : '0', 'boolean');
        $message = '模块设置已更新';
    } elseif ($action === 'save') {
        $data = $_POST['data'] ?? [];
        foreach ($data as $key => $value) {
            setConfig($module, $key, $value);
        }
        setConfig('modules', $module . '_enabled', '1', 'boolean');
        $message = '模块配置已保存';
    }

    header('Location: modules.php?saved=1&module=' . urlencode($module));
    exit;
}

if (isset($_GET['saved'])) {
    $message = '模块设置已保存';
}

$modules = [
    'rss' => [
        'name' => 'RSS 聚合',
        'icon' => 'fa-rss',
        'desc' => '显示博客的最新文章列表',
        'fields' => [
            'url' => ['type' => 'url', 'label' => 'RSS 订阅地址', 'placeholder' => 'https://yourblog.com/rss.xml'],
            'count' => ['type' => 'number', 'label' => '显示数量', 'placeholder' => '4'],
        ]
    ],
    'projects' => [
        'name' => 'GitHub 项目',
        'icon' => 'fa-github',
        'desc' => '展示 GitHub 公开仓库',
        'fields' => [
            'github_user' => ['type' => 'text', 'label' => 'GitHub 用户名', 'placeholder' => 'username'],
            'count' => ['type' => 'number', 'label' => '显示数量', 'placeholder' => '5'],
        ]
    ],
    'moments' => [
        'name' => 'Memos 动态',
        'icon' => 'fa-bolt',
        'desc' => '显示 Memos 的碎片化内容',
        'fields' => [
            'memos_url' => ['type' => 'url', 'label' => 'Memos 实例地址', 'placeholder' => 'https://memos.example.com/'],
            'count' => ['type' => 'number', 'label' => '显示数量', 'placeholder' => '10'],
        ]
    ],
    'guestbook' => [
        'name' => '留言板',
        'icon' => 'fa-comments',
        'desc' => '访客留言功能（Waline/Artalk）',
        'fields' => [
            'provider' => ['type' => 'select', 'label' => '评论服务', 'options' => ['waline' => 'Waline', 'artalk' => 'Artalk']],
            'server' => ['type' => 'url', 'label' => '服务端地址', 'placeholder' => 'https://your-waline.vercel.app'],
            'placeholder' => ['type' => 'text', 'label' => '占位提示', 'placeholder' => '欢迎留言...'],
        ]
    ],
    'music' => [
        'name' => '音乐播放器',
        'icon' => 'fa-music',
        'desc' => '页面背景音乐播放',
        'fields' => [
            'mode' => ['type' => 'select', 'label' => '播放模式', 'options' => ['meting' => 'Meting API', 'local' => '本地音乐']],
            'server' => ['type' => 'select', 'label' => '音乐服务器', 'options' => ['netease' => '网易云', 'tencent' => 'QQ音乐', 'kugou' => '酷狗', 'xiami' => '虾米']],
            'playlist_id' => ['type' => 'text', 'label' => '歌单 ID', 'placeholder' => '10046455237'],
            'volume' => ['type' => 'range', 'label' => '默认音量', 'min' => 0, 'max' => 100],
        ]
    ],
    'donation' => [
        'name' => '赞赏支持',
        'icon' => 'fa-heart',
        'desc' => '显示赞赏二维码',
        'fields' => [
            'message' => ['type' => 'textarea', 'label' => '提示文字', 'placeholder' => '如果我的内容对你有帮助，欢迎请我喝杯咖啡~'],
        ]
    ],
];

$moduleData = [];
foreach ($modules as $key => $module) {
    $moduleData[$key] = [
        'enabled' => (bool)getConfig('modules', $key . '_enabled', false),
        'config' => getAllConfig($key)
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>模块管理 - MoeHome 管理后台</title>

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
                    <i class="fas fa-puzzle-piece"></i>
                    模块管理
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

                <?php foreach ($modules as $key => $module): ?>
                <div class="card fade-in" id="module-<?php echo h($key); ?>">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas <?php echo h($module['icon']); ?>"></i>
                            <?php echo h($module['name']); ?>
                        </h2>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                            <input type="hidden" name="module" value="<?php echo h($key); ?>" />
                            <input type="hidden" name="action" value="toggle" />
                            <input type="hidden" name="enabled" value="<?php echo $moduleData[$key]['enabled'] ? '0' : '1'; ?>" />
                            <button type="submit" class="btn <?php echo $moduleData[$key]['enabled'] ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                                <i class="fas <?php echo $moduleData[$key]['enabled'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                <?php echo $moduleData[$key]['enabled'] ? '已启用' : '已禁用'; ?>
                            </button>
                        </form>
                    </div>

                    <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 20px;">
                        <?php echo h($module['desc']); ?>
                    </p>

                    <?php if (!empty($module['fields'])): ?>
                    <form method="POST" data-ajax>
                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                        <input type="hidden" name="module" value="<?php echo h($key); ?>" />
                        <input type="hidden" name="action" value="save" />

                        <div class="form-row">
                            <?php foreach ($module['fields'] as $fieldKey => $field): ?>
                            <div class="form-group">
                                <label class="form-label" for="<?php echo h($key . '_' . $fieldKey); ?>">
                                    <?php echo h($field['label']); ?>
                                </label>

                                <?php if ($field['type'] === 'select'): ?>
                                <select id="<?php echo h($key . '_' . $fieldKey); ?>" name="data[<?php echo h($fieldKey); ?>]" class="form-select">
                                    <?php foreach ($field['options'] as $optVal => $optLabel): ?>
                                    <option value="<?php echo h($optVal); ?>" <?php echo ($moduleData[$key]['config'][$fieldKey] ?? '') === $optVal ? 'selected' : ''; ?>>
                                        <?php echo h($optLabel); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>

                                <?php elseif ($field['type'] === 'textarea'): ?>
                                <textarea id="<?php echo h($key . '_' . $fieldKey); ?>" name="data[<?php echo h($fieldKey); ?>]" class="form-textarea" placeholder="<?php echo h($field['placeholder'] ?? ''); ?>"><?php echo h($moduleData[$key]['config'][$fieldKey] ?? ''); ?></textarea>

                                <?php elseif ($field['type'] === 'range'): ?>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <input type="range" id="<?php echo h($key . '_' . $fieldKey); ?>" name="data[<?php echo h($fieldKey); ?>]" min="<?php echo h($field['min'] ?? 0); ?>" max="<?php echo h($field['max'] ?? 100); ?>" value="<?php echo h($moduleData[$key]['config'][$fieldKey] ?? 50); ?>" style="flex: 1;" />
                                    <span id="<?php echo h($key . '_' . $fieldKey); ?>_value" style="min-width: 40px; text-align: right; font-family: var(--font-mono);"><?php echo h($moduleData[$key]['config'][$fieldKey] ?? 50); ?></span>
                                </div>

                                <?php else: ?>
                                <input type="<?php echo h($field['type']); ?>" id="<?php echo h($key . '_' . $fieldKey); ?>" name="data[<?php echo h($fieldKey); ?>]" class="form-input" value="<?php echo h($moduleData[$key]['config'][$fieldKey] ?? ''); ?>" placeholder="<?php echo h($field['placeholder'] ?? ''); ?>" />
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                保存配置
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        document.querySelectorAll('input[type="range"]').forEach(function(input) {
            input.addEventListener('input', function() {
                var valueDisplay = document.getElementById(this.id + '_value');
                if (valueDisplay) {
                    valueDisplay.textContent = this.value;
                }
            });
        });
    </script>
</body>
</html>
