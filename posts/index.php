<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

$config = [];
$configFile = __DIR__ . '/../api/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/../api/config.example.php';
}

$site = $config['site'] ?? [];
$pages = $config['pages'] ?? [];
$postsConfig = $config['posts'] ?? [];
$theme = $config['theme'] ?? [];

$pageConfig = $pages['posts'] ?? [];
$pageTitle = htmlspecialchars(($pageConfig['title'] ?? '博客') . ' | ' . ($site['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$pageDescription = htmlspecialchars($pageConfig['description'] ?? '博客文章动态，记录技术分享与生活感悟', ENT_QUOTES, 'UTF-8');
$pageKeywords = htmlspecialchars(implode(',', $pageConfig['keywords'] ?? ['博客', '文章', '技术分享']), ENT_QUOTES, 'UTF-8');
$pageTagline = htmlspecialchars($pageConfig['tagline'] ?? '', ENT_QUOTES, 'UTF-8');

$themeDefault = $theme['default'] ?? 'light';
$themeDefaultScheme = $theme['defaultScheme'] ?? ['light' => 'coralOrange', 'dark' => 'cyberGreen'];
$themeDefaultSchemeData = $themeDefaultScheme[$themeDefault] ?? 'coralOrange';

$categories = $postsConfig['categories'] ?? [];

$currentCategory = isset($_GET['category']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', trim($_GET['category'])) : '';
$categoryName = '全部';
foreach ($categories as $cat) {
    if ($cat['slug'] === $currentCategory) {
        $categoryName = $cat['name'];
        break;
    }
}

$themeDefaultSchemeJson = json_encode($themeDefaultScheme, JSON_UNESCAPED_UNICODE);

$themeInitScript = <<<HTML
<script>
(function(){
    var dm='{$themeDefault}',ds=JSON.parse('{$themeDefaultSchemeJson}');
    var s=null;
    try{var t=localStorage.getItem('moehome-theme');if(t)s=JSON.parse(t)}catch(e){}
    var m=s?.mode||dm,sc=s?.scheme||(ds[m]||'coralOrange');
    document.documentElement.setAttribute('data-theme',m);
    document.documentElement.setAttribute('data-scheme',sc);
})();
</script>
HTML;

$canonicalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . '/posts/';
if (!empty($currentCategory)) {
    $canonicalUrl .= '?category=' . urlencode($currentCategory);
}

$ogImage = htmlspecialchars($site['url'] ?? '', ENT_QUOTES, 'UTF-8') . '/og-image.png';
?>
<!doctype html>
<html lang="zh-CN" data-theme="<?php echo $themeDefault; ?>" data-scheme="<?php echo $themeDefaultSchemeData; ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, shrink-to-fit=no" />

        <title><?php echo $pageTitle; ?></title>
        <meta name="description" content="<?php echo $pageDescription; ?>" />
        <meta name="keywords" content="<?php echo $pageKeywords; ?>" />
        <meta name="author" content="<?php echo htmlspecialchars($site['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        <meta name="robots" content="index, follow" />
        <link rel="canonical" href="<?php echo $canonicalUrl; ?>" />

        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?php echo $pageTitle; ?>" />
        <meta property="og:description" content="<?php echo $pageDescription; ?>" />
        <meta property="og:url" content="<?php echo $canonicalUrl; ?>" />
        <meta property="og:site_name" content="<?php echo htmlspecialchars($site['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
        <meta property="og:image" content="<?php echo $ogImage; ?>" />
        <meta property="og:locale" content="zh_CN" />

        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="<?php echo $pageTitle; ?>" />
        <meta name="twitter:description" content="<?php echo $pageDescription; ?>" />
        <meta name="twitter:image" content="<?php echo $ogImage; ?>" />

        <?php echo $themeInitScript; ?>

        <link rel="preconnect" href="https://fonts.googleapis.cn" />
        <link rel="preconnect" href="https://fonts.gstatic.cn" crossorigin />
        <link href="https://fonts.googleapis.cn/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <link rel="preload" href="../style.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
        <noscript><link rel="stylesheet" href="../style.css" /></noscript>

        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📝</text></svg>" />
    </head>
    <body>
        <a href="#actual-content" class="skip-link">跳到主要内容</a>

        <nav class="navbar" id="navbar">
            <div class="navbar-inner">
                <a href="../index.php" class="navbar-brand">
                    <span class="prompt">$</span>
                    <span class="brand-name"><?php echo htmlspecialchars($site['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
                <div class="navbar-menu" id="navbar-menu">
                    <a href="../index.php" class="nav-link">首页</a>
                    <a href="index.php" class="nav-link active">博客</a>
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
            <a href="../index.php" class="nav-link">首页</a>
            <a href="index.php" class="nav-link active">博客</a>
        </div>

        <main class="container" id="actual-content" role="main">
            <div class="posts-page-wrapper">
                <header class="posts-page-header">
                    <h1 class="posts-page-title">博客</h1>
                    <p class="posts-page-tagline"><?php echo $pageTagline; ?></p>
                </header>

                <div class="divider"></div>

                <nav class="posts-filter-bar" aria-label="文章分类筛选">
                    <div class="posts-filter" id="posts-filter" role="tablist">
                        <button class="filter-item <?php echo empty($currentCategory) ? 'active' : ''; ?>" data-category="" role="tab" aria-selected="<?php echo empty($currentCategory) ? 'true' : 'false'; ?>">
                            <i class="fas fa-layer-group"></i>
                            <span>全部</span>
                        </button>
                        <?php foreach ($categories as $cat): ?>
                        <button class="filter-item <?php echo $currentCategory === $cat['slug'] ? 'active' : ''; ?>" data-category="<?php echo htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8'); ?>" role="tab" aria-selected="<?php echo $currentCategory === $cat['slug'] ? 'true' : 'false'; ?>">
                            <i class="<?php echo htmlspecialchars($cat['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                            <span><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </nav>

                <section class="posts-container" id="posts-container" aria-label="文章列表">
                    <div class="posts-loading" aria-live="polite">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>加载中...</span>
                    </div>
                </section>

                <nav class="posts-pagination" id="posts-pagination" aria-label="分页导航">
                    <button class="pagination-btn prev" id="pagination-prev" disabled aria-label="上一页">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="pagination-info" id="pagination-info" aria-live="polite">第 1 页</span>
                    <button class="pagination-btn next" id="pagination-next" disabled aria-label="下一页">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>

                <footer class="footer">
                    <div class="footer-content">
                        <p class="footer-copyright">
                            &copy; <?php echo date('Y'); ?>
                            <a href="<?php echo htmlspecialchars($site['url'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($site['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></a>
                        </p>
                    </div>
                </footer>
            </div>
        </main>

        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Blog",
            "name": "<?php echo htmlspecialchars($site['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>",
            "description": "<?php echo $pageDescription; ?>",
            "url": "<?php echo $canonicalUrl; ?>"
        }
        </script>

        <script>
            window.POSTS_CONFIG = <?php echo json_encode([
                'apiBase' => '../api',
                'currentCategory' => $currentCategory
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        </script>

        <script src="../media-manager.js" defer></script>
        <script src="../theme-data.js" defer></script>
        <script src="../theme-utils.js" defer></script>
        <script src="../app.js" defer></script>
        <script src="../posts.js" defer></script>
    </body>
</html>