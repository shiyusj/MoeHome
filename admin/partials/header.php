<?php
/**
 * MoeHome 后台管理 - 头部模板
 */

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$navItems = [
    'index' => ['icon' => 'fa-home', 'label' => '仪表盘', 'url' => 'index.php'],
    'settings' => ['icon' => 'fa-cog', 'label' => '站点设置', 'url' => 'settings.php'],
    'modules' => ['icon' => 'fa-puzzle-piece', 'label' => '模块管理', 'url' => 'modules.php'],
    'theme' => ['icon' => 'fa-palette', 'label' => '主题设置', 'url' => 'theme.php'],
];

$systemItems = [
    'cache' => ['icon' => 'fa-broom', 'label' => '清理缓存', 'url' => 'api/cache.php?action=clear'],
    'password' => ['icon' => 'fa-key', 'label' => '修改密码', 'url' => 'password.php'],
];
?>
<div class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-logo">
            <span class="prompt">$</span>
            <span>MoeHome</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">主菜单</div>
            <?php foreach ($navItems as $page => $item): ?>
            <a href="<?php echo h($item['url']); ?>" class="nav-item <?php echo $currentPage === $page ? 'active' : ''; ?>">
                <i class="fas <?php echo h($item['icon']); ?>"></i>
                <span><?php echo h($item['label']); ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">系统</div>
            <?php foreach ($systemItems as $page => $item): ?>
            <a href="<?php echo h($item['url']); ?>" class="nav-item <?php echo $currentPage === $page ? 'active' : ''; ?>" <?php echo $page === 'cache' ? 'onclick="return confirm(\'确定要清理所有缓存吗？\')"' : ''; ?>>
                <i class="fas <?php echo h($item['icon']); ?>"></i>
                <span><?php echo h($item['label']); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo h(substr($currentUser['username'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo h($currentUser['username'] ?? 'Admin'); ?></div>
                <div class="user-role"><?php echo h($currentUser['role'] ?? 'admin'); ?></div>
            </div>
        </div>
        <div style="margin-top: 12px;">
            <a href="logout.php" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                <i class="fas fa-sign-out-alt"></i>
                <span>退出登录</span>
            </a>
        </div>
    </div>
</div>
