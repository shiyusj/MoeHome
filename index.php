<?php
/**
 * MoeHome 虚拟主机版 - 首页
 * 版本 v3.0
 *
 * 新增模块: 图库、花架、哔哩哔哩
 * 移除模块: GitHub项目、贡献图谱
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

$startTime = microtime(true);

$config = [];
$configFile = __DIR__ . '/api/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/api/config.example.php';
}

ob_start();

$cacheVersion = 'v3.0';

$site = $config['site'] ?? [];
$seo = $config['seo'] ?? [];
$profile = $config['profile'] ?? [];
$theme = $config['theme'] ?? [];
$music = $config['music'] ?? [];
$terminal = $config['terminal'] ?? [];
$rss = $config['rss'] ?? [];
$moments = $config['moments'] ?? [];
$guestbook = $config['guestbook'] ?? [];
$gallery = $config['gallery'] ?? [];
$books = $config['books'] ?? [];
$bilibili = $config['bilibili'] ?? [];
$linksConfig = $config['linksConfig'] ?? [];
$links = $config['links'] ?? [];
$donation = $config['donation'] ?? [];
$footer = $config['footer'] ?? [];
$notice = $config['notice'] ?? [];
$analytics = $config['analytics'] ?? [];
$animation = $config['animation'] ?? [];

$identity = $config['identity'] ?? [];
$interests = $config['interests'] ?? [];
$gear = $config['gear'] ?? [];
$quotes = $config['quotes'] ?? [];

$themeDefault = $theme['default'] ?? 'light';
$themeDefaultScheme = $theme['defaultScheme'] ?? ['light' => 'coralOrange', 'dark' => 'cyberGreen'];
$themeDefaultSchemeData = $themeDefaultScheme[$themeDefault] ?? 'coralOrange';

$identityJson = json_encode($identity, JSON_UNESCAPED_UNICODE);
$interestsJson = json_encode($interests, JSON_UNESCAPED_UNICODE);
$quotesJson = json_encode($quotes, JSON_UNESCAPED_UNICODE);

$fadeInDelay = intval($animation['fadeInDelay'] ?? 1000);
$typingSpeed = intval($animation['typingSpeed'] ?? 60);
$quoteDisplayTime = intval($animation['quoteDisplayTime'] ?? 4000);
$quoteDeleteSpeed = intval($animation['quoteDeleteSpeed'] ?? 42);

$profileName = htmlspecialchars($profile['name'] ?? '', ENT_QUOTES, 'UTF-8');
$profileTaglinePrefix = htmlspecialchars($profile['tagline']['prefix'] ?? '', ENT_QUOTES, 'UTF-8');
$profileTaglineHighlight = htmlspecialchars($profile['tagline']['highlight'] ?? '', ENT_QUOTES, 'UTF-8');
$avatar = htmlspecialchars($profile['avatar'] ?? 'images/avatar.webp', ENT_QUOTES, 'UTF-8');

$pageTitle = htmlspecialchars(($seo['title'] ?? $site['name'] ?? 'Home') , ENT_QUOTES, 'UTF-8');
$pageDescription = htmlspecialchars($seo['description'] ?? '', ENT_QUOTES, 'UTF-8');
$pageKeywords = is_array($seo['keywords'] ?? null) ? htmlspecialchars(implode(', ', $seo['keywords']), ENT_QUOTES, 'UTF-8') : '';

$ogTitle = htmlspecialchars(($seo['og']['title'] ?? $pageTitle), ENT_QUOTES, 'UTF-8');
$ogDescription = htmlspecialchars(($seo['og']['description'] ?? $pageDescription), ENT_QUOTES, 'UTF-8');
$ogImg = htmlspecialchars(($seo['og']['image'] ?? $site['ogImage'] ?? $avatar), ENT_QUOTES, 'UTF-8');

$siteName = htmlspecialchars($site['name'] ?? '', ENT_QUOTES, 'UTF-8');
$terminalTitle = htmlspecialchars(($terminal['title'] ?? '🐾 user@host:~|'), ENT_QUOTES, 'UTF-8');

$musicEnabled = (bool)($music['enabled'] ?? false);
$musicHtml = '';

if ($musicEnabled) {
    $musicVolume = round(floatval($music['volume'] ?? 0.5), 1);
    $musicAutoplay = ($music['autoplay'] ?? false) ? 'true' : 'false';
    $musicPlayMode = htmlspecialchars($music['playMode'] ?? 'list', ENT_QUOTES, 'UTF-8');
    $musicData = '';

    if (($music['mode'] ?? 'meting') === 'meting' && isset($music['meting'])) {
        $meting = $music['meting'];
        $musicServer = htmlspecialchars($meting['server'] ?? 'netease', ENT_QUOTES, 'UTF-8');
        $musicType = htmlspecialchars($meting['type'] ?? 'playlist', ENT_QUOTES, 'UTF-8');
        $musicId = htmlspecialchars($meting['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $musicApi = urlencode($meting['apis'][0] ?? 'https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r');
        $musicData = "data-mode=\"meting\" data-server=\"{$musicServer}\" data-type=\"{$musicType}\" data-id=\"{$musicId}\" data-api=\"{$musicApi}\"";
    } elseif (($music['mode'] ?? '') === 'local' && isset($music['local'])) {
        $localMusic = htmlspecialchars(json_encode($music['local'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        $musicData = "data-mode=\"local\" data-songs='{$localMusic}'";
    }

    $musicHtml = <<<HTML
    <div class="music-player lazy-load" id="music-player" data-delay="1" {$musicData} data-volume="{$musicVolume}" data-autoplay="{$musicAutoplay}" data-play-mode="{$musicPlayMode}">
        <div class="music-info">
            <div class="music-title" id="music-title">♫ Music</div>
            <div class="music-artist" id="music-artist">Loading...</div>
        </div>
        <div class="music-controls">
            <button class="music-btn" id="music-prev" aria-label="上一首"><i class="fas fa-backward-step"></i></button>
            <button class="music-btn music-play" id="music-play" aria-label="播放/暂停"><i class="fas fa-play"></i></button>
            <button class="music-btn" id="music-next" aria-label="下一首"><i class="fas fa-forward-step"></i></button>
            <div class="music-volume">
                <i class="fas fa-volume-high" id="music-volume-icon"></i>
                <input type="range" class="music-volume-slider" id="music-volume" min="0" max="1" step="0.1" value="{$musicVolume}" aria-label="音量调节">
            </div>
        </div>
        <div class="music-progress">
            <span class="music-time" id="music-current">0:00</span>
            <div class="music-progress-bar">
                <div class="music-progress-fill" id="music-progress-fill"></div>
            </div>
            <span class="music-time" id="music-duration">0:00</span>
        </div>
    </div>
HTML;
} else {
    $musicHtml = '<hr class="section-divider">';
}

$rssEnabled = (bool)($rss['enabled'] ?? false);
$rssHtml = '';

if ($rssEnabled) {
    $rssUrl = urlencode($rss['url'] ?? '');
    $rssCount = intval($rss['count'] ?? 4);
    $rssTitle = htmlspecialchars($rss['title']['text'] ?? 'Recent Posts', ENT_QUOTES, 'UTF-8');
    $rssIcon = htmlspecialchars($rss['title']['icon'] ?? 'fa-solid fa-newspaper', ENT_QUOTES, 'UTF-8');
    $rssShowDate = (($rss['display']['showDate'] ?? true) ? 'true' : 'false');
    $rssShowDesc = (($rss['display']['showDescription'] ?? true) ? 'true' : 'false');
    $rssMaxDescLen = intval($rss['display']['maxDescriptionLength'] ?? 100);
    $rssOpenNewTab = (($rss['openInNewTab'] ?? true) ? 'target="_blank" rel="noopener"' : '');

    $rssHtml = <<<HTML
        <section class="section rss-section lazy-load" data-delay="4">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="{$rssIcon}"></i>
                    <span>{$rssTitle}</span>
                </h2>
            </div>
            <div class="rss-list" id="rss-list" data-url="{$rssUrl}" data-count="{$rssCount}" data-show-date="{$rssShowDate}" data-show-description="{$rssShowDesc}" data-max-description-length="{$rssMaxDescLen}" data-open-new-tab="{$rssOpenNewTab}">
                <div class="rss-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>加载中...</span>
                </div>
            </div>
        </section>
HTML;
}

$galleryEnabled = (bool)($gallery['enabled'] ?? false);
$galleryHtml = '';

if ($galleryEnabled) {
    $galleryTitle = htmlspecialchars($gallery['title']['text'] ?? '我的图库', ENT_QUOTES, 'UTF-8');
    $galleryIcon = htmlspecialchars($gallery['title']['icon'] ?? 'fa-solid fa-images', ENT_QUOTES, 'UTF-8');
    $gallerySource = htmlspecialchars($gallery['source'] ?? 'local', ENT_QUOTES, 'UTF-8');
    $galleryCount = intval($gallery['count'] ?? 8);

    $galleryHtml = <<<HTML
        <section class="section gallery-section lazy-load" data-delay="5">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="{$galleryIcon}"></i>
                    <span>{$galleryTitle}</span>
                </h2>
            </div>
            <div class="gallery-grid" id="gallery-grid" data-source="{$gallerySource}" data-count="{$galleryCount}">
                <div class="gallery-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>加载中...</span>
                </div>
            </div>
        </section>
HTML;
}

$booksEnabled = (bool)($books['enabled'] ?? false);
$booksHtml = '';

if ($booksEnabled) {
    $booksTitle = htmlspecialchars($books['title']['text'] ?? '书架', ENT_QUOTES, 'UTF-8');
    $booksIcon = htmlspecialchars($books['title']['icon'] ?? 'fa-solid fa-book', ENT_QUOTES, 'UTF-8');
    $booksCount = intval($books['count'] ?? 6);
    $booksUserId = htmlspecialchars($books['doubanId'] ?? '', ENT_QUOTES, 'UTF-8');

    $booksHtml = <<<HTML
        <section class="section books-section lazy-load" data-delay="6">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="{$booksIcon}"></i>
                    <span>{$booksTitle}</span>
                </h2>
            </div>
            <div class="books-grid" id="books-grid" data-count="{$booksCount}" data-user-id="{$booksUserId}">
                <div class="books-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>加载中...</span>
                </div>
            </div>
        </section>
HTML;
}

$bilibiliEnabled = (bool)($bilibili['enabled'] ?? false);
$bilibiliHtml = '';

if ($bilibiliEnabled) {
    $bilibiliTitle = htmlspecialchars($bilibili['title']['text'] ?? '哔哩哔哩', ENT_QUOTES, 'UTF-8');
    $bilibiliIcon = htmlspecialchars($bilibili['title']['icon'] ?? 'fa-brands fa-bilibili', ENT_QUOTES, 'UTF-8');
    $bilibiliUid = htmlspecialchars($bilibili['uid'] ?? '', ENT_QUOTES, 'UTF-8');
    $bilibiliCount = intval($bilibili['count'] ?? 4);

    $bilibiliHtml = <<<HTML
        <section class="section bilibili-section lazy-load" data-delay="7">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="{$bilibiliIcon}"></i>
                    <span>{$bilibiliTitle}</span>
                </h2>
            </div>
            <div class="bilibili-grid" id="bilibili-grid" data-uid="{$bilibiliUid}" data-count="{$bilibiliCount}">
                <div class="bilibili-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>加载中...</span>
                </div>
            </div>
        </section>
HTML;
}

$linksEnabled = (bool)($linksConfig['enabled'] ?? false);
$linksHtml = '';

if ($linksEnabled) {
    $linksTitle = htmlspecialchars($linksConfig['title']['text'] ?? 'Quick Links', ENT_QUOTES, 'UTF-8');
    $linksIcon = htmlspecialchars($linksConfig['title']['icon'] ?? 'fa-solid fa-link', ENT_QUOTES, 'UTF-8');
    $linksItemsHtml = '';

    foreach ($links as $link) {
        if (!($link['enabled'] ?? true)) continue;

        $linkName = htmlspecialchars($link['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $linkDesc = htmlspecialchars($link['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $linkUrl = htmlspecialchars($link['url'] ?? '#', ENT_QUOTES, 'UTF-8');
        $linkIcon = htmlspecialchars($link['icon'] ?? 'fa-solid fa-link', ENT_QUOTES, 'UTF-8');
        $linkColor = htmlspecialchars($link['color'] ?? 'var(--accent)', ENT_QUOTES, 'UTF-8');
        $linkExternal = (($link['external'] ?? false) ? 'target="_blank" rel="noopener noreferrer"' : '');
        $linkBrand = htmlspecialchars($link['brand'] ?? '', ENT_QUOTES, 'UTF-8');

        if (($link['antiCrawler'] ?? false) && strpos($linkUrl, 'mailto:') === 0) {
            $email = base64_encode(str_replace('mailto:', '', $linkUrl));
            $linkAttrs = "href=\"javascript:void(0)\" onclick=\"location.href='mailto:'+atob('{$email}')\"";
        } else {
            $linkAttrs = "href=\"{$linkUrl}\" {$linkExternal}";
        }

        $linksItemsHtml .= <<<HTML
            <a class="link-item" {$linkAttrs} data-brand="{$linkBrand}" style="--link-color: {$linkColor}">
                <div class="link-icon">
                    <i class="{$linkIcon}"></i>
                </div>
                <div class="link-info">
                    <span class="link-name">{$linkName}</span>
                    <span class="link-desc">{$linkDesc}</span>
                </div>
            </a>
HTML;
    }

    $linksHtml = <<<HTML
        <section class="section links-section lazy-load" data-delay="8">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="{$linksIcon}"></i>
                    <span>{$linksTitle}</span>
                </h2>
            </div>
            <div class="links-grid">
                {$linksItemsHtml}
            </div>
        </section>
HTML;
}

$donationEnabled = (bool)($donation['enabled'] ?? false);
$donationHtml = '';
$donationModalHtml = '';

if ($donationEnabled) {
    $donationTitle = htmlspecialchars($donation['title']['text'] ?? '赞助支持', ENT_QUOTES, 'UTF-8');
    $donationIcon = htmlspecialchars($donation['title']['icon'] ?? 'fa-solid fa-mug-hot', ENT_QUOTES, 'UTF-8');
    $donationMessage = htmlspecialchars($donation['message'] ?? '', ENT_QUOTES, 'UTF-8');
    $methodsHtml = '';
    $modalMethodsHtml = '';

    foreach ($donation['methods'] ?? [] as $method) {
        if (!($method['enabled'] ?? true)) continue;

        $methodName = htmlspecialchars($method['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $methodKey = htmlspecialchars($method['key'] ?? '', ENT_QUOTES, 'UTF-8');
        $methodIcon = htmlspecialchars($method['icon'] ?? 'fa-solid fa-gift', ENT_QUOTES, 'UTF-8');

        if (!empty($method['qrImage'])) {
            $qrSrc = htmlspecialchars($method['qrImage'], ENT_QUOTES, 'UTF-8');
            $methodsHtml .= <<<HTML
                <button class="donation-method" data-method="{$methodKey}" aria-label="{$methodName}">
                    <i class="{$methodIcon}"></i>
                    <span>{$methodName}</span>
                </button>
HTML;
            $modalMethodsHtml .= <<<HTML
                <div class="donation-modal-item" id="donation-{$methodKey}">
                    <img src="{$qrSrc}" alt="{$methodName}" class="donation-qr">
                </div>
HTML;
        } elseif (!empty($method['url'])) {
            $methodUrl = htmlspecialchars($method['url'], ENT_QUOTES, 'UTF-8');
            $methodsHtml .= <<<HTML
                <a class="donation-method" href="{$methodUrl}" target="_blank" rel="noopener noreferrer" aria-label="{$methodName}">
                    <i class="{$methodIcon}"></i>
                    <span>{$methodName}</span>
                </a>
HTML;
        }
    }

    $donationHtml = <<<HTML
        <section class="section donation-section lazy-load" data-delay="9">
            <div class="donation-header">
                <h2 class="section-title">
                    <i class="{$donationIcon}"></i>
                    <span>{$donationTitle}</span>
                </h2>
            </div>
            <p class="donation-message">{$donationMessage}</p>
            <div class="donation-methods" id="donation-methods">
                {$methodsHtml}
            </div>
        </section>
HTML;

    if (!empty($modalMethodsHtml)) {
        $donationModalHtml = <<<HTML
        <div class="donation-modal-overlay" id="donation-modal-overlay">
            <div class="donation-modal">
                <button class="donation-modal-close" id="donation-modal-close" aria-label="关闭">
                    <i class="fas fa-times"></i>
                </button>
                <div class="donation-modal-content" id="donation-modal-content">
                    {$modalMethodsHtml}
                </div>
            </div>
        </div>
HTML;
    }
}

$noticeEnabled = (bool)($notice['enabled'] ?? false);
$noticeHtml = '';

if ($noticeEnabled) {
    $noticeType = htmlspecialchars($notice['type'] ?? 'info', ENT_QUOTES, 'UTF-8');
    $noticeIcon = htmlspecialchars($notice['icon'] ?? 'fa-solid fa-info-circle', ENT_QUOTES, 'UTF-8');
    $noticeText = htmlspecialchars($notice['text'] ?? '', ENT_QUOTES, 'UTF-8');

    $noticeHtml = <<<HTML
        <div class="notice notice-{$noticeType} lazy-load" data-delay="10" role="alert">
            <i class="{$noticeIcon}"></i>
            <span>{$noticeText}</span>
        </div>
HTML;
}

$gearEnabled = !empty($gear);
$gearHtml = '';

if ($gearEnabled) {
    $gearJson = htmlspecialchars(json_encode($gear, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    $gearHtml = <<<HTML
        <div class="prompt-line" style="margin-top: 8px;">
            <span class="prompt">\$ </span>
            <span class="command">cat gear.txt</span>
        </div>
        <div class="output" id="gear-output" data-value='{$gearJson}'></div>
HTML;
}

$skeletonMusic = $musicEnabled ? '<div class="skeleton-music skeleton"></div>' : '';
$skeletonRss = $rssEnabled ? '<div class="skeleton-rss skeleton"></div>' : '';
$skeletonGallery = $galleryEnabled ? '<div class="skeleton-gallery skeleton"></div>' : '';
$skeletonBooks = $booksEnabled ? '<div class="skeleton-books skeleton"></div>' : '';
$skeletonBilibili = $bilibiliEnabled ? '<div class="skeleton-bilibili skeleton"></div>' : '';
$skeletonLinks = $linksEnabled ? '<div class="skeleton-links skeleton"></div>' : '';
$skeletonDonation = $donationEnabled ? '<div class="skeleton-donation skeleton"></div>' : '';
$skeletonNotice = $noticeEnabled ? '<div class="skeleton-notice skeleton"></div>' : '';

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

$themeDefaultSchemeJson = json_encode($themeDefaultScheme, JSON_UNESCAPED_UNICODE);

$analyticsHtml = '';

if (!empty($analytics['googleAnalytics']['enabled']) && !empty($analytics['googleAnalytics']['id'])) {
    $gaId = htmlspecialchars($analytics['googleAnalytics']['id'], ENT_QUOTES, 'UTF-8');
    $analyticsHtml .= <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$gaId}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$gaId}');</script>
HTML;
}

if (!empty($analytics['microsoftClarity']['enabled']) && !empty($analytics['microsoftClarity']['id'])) {
    $clarityId = htmlspecialchars($analytics['microsoftClarity']['id'], ENT_QUOTES, 'UTF-8');
    $analyticsHtml .= <<<HTML
<script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,"clarity","script","{$clarityId}");</script>
HTML;
}

if (!empty($analytics['umami'])) {
    $analyticsHtml .= $analytics['umami'];
}

foreach ($analytics['customScripts'] ?? [] as $script) {
    if (!empty($script)) {
        $analyticsHtml .= $script . "\n";
    }
}

$icpEnabled = !empty($footer['icp']['enabled']);
$icpNumber = $icpEnabled ? htmlspecialchars($footer['icp']['number'] ?? '', ENT_QUOTES, 'UTF-8') : '';
$copyrightYear = htmlspecialchars(($footer['copyright']['year'] ?? date('Y')), ENT_QUOTES, 'UTF-8');
$copyrightName = htmlspecialchars(($footer['copyright']['name'] ?? $siteName), ENT_QUOTES, 'UTF-8');
$copyrightUrl = htmlspecialchars(($footer['copyright']['url'] ?? '#'), ENT_QUOTES, 'UTF-8');

$momentsNavHtml = '';
$guestbookNavHtml = '';

if ($moments['enabled'] ?? false) {
    $momentsNavHtml = '<a href="moments.php" class="nav-link">动态</a>';
}

if ($guestbook['enabled'] ?? false) {
    $guestbookNavHtml = '<a href="guestbook.php" class="nav-link">留言</a>';
}
?>
<!doctype html>
<html lang="zh-CN" data-theme="<?php echo $themeDefault; ?>" data-scheme="<?php echo $themeDefaultSchemeData; ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, shrink-to-fit=no" />
        <meta name="generator" content="MoeHome <?php echo $cacheVersion; ?>" />

        <title><?php echo $pageTitle; ?></title>
        <meta name="description" content="<?php echo $pageDescription; ?>" />
        <meta name="keywords" content="<?php echo $pageKeywords; ?>" />

        <meta property="og:type" content="website" />
        <meta property="og:title" content="<?php echo $ogTitle; ?>" />
        <meta property="og:description" content="<?php echo $ogDescription; ?>" />
        <meta property="og:image" content="<?php echo $ogImg; ?>" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:title" content="<?php echo $ogTitle; ?>" />
        <meta property="twitter:description" content="<?php echo $ogDescription; ?>" />
        <meta property="twitter:image" content="<?php echo $ogImg; ?>" />

        <link rel="icon" type="image/webp" href="<?php echo $avatar; ?>" />

        <?php echo $themeInitScript; ?>

        <link rel="preconnect" href="https://fonts.googleapis.cn" />
        <link rel="preconnect" href="https://fonts.gstatic.cn" crossorigin />
        <link href="https://fonts.googleapis.cn/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <link rel="preload" href="style.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
        <noscript><link rel="stylesheet" href="style.css" /></noscript>

        <?php echo $analyticsHtml; ?>

        <script>
            window.MOEHOME_CONFIG=<?php echo json_encode([
                'identity'=>$identity,
                'interests'=>$interests,
                'quotes'=>$quotes,
                'gallery'=>$gallery,
                'books'=>$books,
                'bilibili'=>$bilibili,
                'animation'=>[
                    'typingSpeed'=>$typingSpeed,
                    'quoteDisplayTime'=>$quoteDisplayTime,
                    'quoteDeleteSpeed'=>$quoteDeleteSpeed
                ],
                'apiBase'=>'api',
                'version'=>$cacheVersion
            ], JSON_UNESCAPED_UNICODE); ?>;
        </script>
    </head>
    <body>
        <a href="#actual-content" class="skip-link">跳到主要内容</a>

        <nav class="navbar" id="navbar">
            <div class="navbar-inner">
                <a href="index.php" class="navbar-brand">
                    <span class="prompt">$</span>
                    <span class="brand-name"><?php echo $siteName; ?></span>
                </a>
                <div class="navbar-menu" id="navbar-menu">
                    <a href="index.php" class="nav-link active">首页</a>
                    <?php echo $momentsNavHtml; ?>
                    <?php echo $guestbookNavHtml; ?>
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
            <a href="index.php" class="nav-link active">首页</a>
            <?php echo $momentsNavHtml; ?>
            <?php echo $guestbookNavHtml; ?>
        </div>

        <div class="container">
            <div class="profile skeleton-container" id="skeleton-screen" role="status" aria-label="Loading content" aria-live="polite">
                <span class="sr-only">正在加载页面内容，请稍候...</span>
                <div class="skeleton-avatar skeleton"></div>
                <div class="skeleton-name skeleton"></div>
                <div class="skeleton-tagline skeleton"></div>
                <?php echo $skeletonMusic; ?>
                <div class="skeleton-terminal skeleton"></div>
                <?php echo $skeletonRss; ?>
                <?php echo $skeletonGallery; ?>
                <?php echo $skeletonBooks; ?>
                <?php echo $skeletonBilibili; ?>
                <?php echo $skeletonLinks; ?>
                <?php echo $skeletonDonation; ?>
                <?php echo $skeletonNotice; ?>
                <div class="skeleton-divider skeleton"></div>
                <div class="skeleton-footer skeleton"></div>
            </div>

            <div class="profile content-initial-hidden" id="actual-content" aria-busy="true">
                <div class="avatar-wrapper">
                    <div class="avatar image-placeholder" id="avatar-placeholder">
                        <img id="avatar-img" src="<?php echo $avatar; ?>" alt="<?php echo $profileName; ?> Avatar" width="140" height="140" data-blur />
                    </div>
                    <div class="status"></div>
                </div>

                <h1 class="name" id="profile-name"><?php echo $profileName; ?></h1>

                <p class="tagline" id="profile-tagline">
                    <span class="paw-icon"><?php echo $profileTaglinePrefix; ?></span>
                    <span class="highlight"><?php echo $profileTaglineHighlight; ?></span>
                </p>

                <?php echo $musicHtml; ?>

                <div class="terminal lazy-load" id="terminal" data-delay="2">
                    <div class="terminal-header">
                        <div class="traffic-lights" aria-hidden="true">
                            <span class="close"></span>
                            <span class="minimize"></span>
                            <span class="maximize"></span>
                        </div>
                        <div class="title" id="terminal-title"><?php echo $terminalTitle; ?></div>
                    </div>
                    <div class="terminal-content" id="terminal-content">
                        <div class="prompt-line">
                            <span class="prompt">$ </span>
                            <span class="command">whoami</span>
                        </div>
                        <div class="output" id="identity-output" data-value='<?php echo $identityJson; ?>'></div>
                        <div class="prompt-line" style="margin-top: 8px;">
                            <span class="prompt">$ </span>
                            <span class="command">cat interests.txt</span>
                        </div>
                        <div class="output" id="interests-output" data-value='<?php echo $interestsJson; ?>'></div>
                        <?php echo $gearHtml; ?>
                        <div class="prompt-line" style="margin-top: 8px;">
                            <span class="prompt">$ </span>
                            <span class="command">./wisdom.sh</span>
                        </div>
                        <div class="output" id="quote-output" data-value='<?php echo $quotesJson; ?>' aria-live="polite" aria-label="Quotes"><span class="cursor-blink" aria-hidden="true"></span></div>
                    </div>
                </div>

                <?php echo $rssHtml; ?>
                <?php echo $galleryHtml; ?>
                <?php echo $booksHtml; ?>
                <?php echo $bilibiliHtml; ?>
                <?php echo $linksHtml; ?>
                <?php echo $donationHtml; ?>
                <?php echo $noticeHtml; ?>

                <footer class="footer">
                    <div class="footer-content">
                        <p class="footer-copyright">
                            &copy; <?php echo $copyrightYear; ?>
                            <a href="<?php echo $copyrightUrl; ?>"><?php echo $copyrightName; ?></a>
                        </p>
                        <?php if ($icpEnabled): ?>
                        <p class="footer-icp">
                            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer"><?php echo $icpNumber; ?></a>
                        </p>
                        <?php endif; ?>
                    </div>
                </footer>
            </div>
        </div>

        <?php echo $donationModalHtml; ?>

        <script src="media-manager.js" defer></script>
        <script src="theme-data.js" defer></script>
        <script src="theme-utils.js" defer></script>
        <script src="app.js" defer></script>
    </body>
</html>
<?php
$output = ob_get_contents();
ob_end_flush();

$executionTime = round((microtime(true) - $startTime) * 1000, 2);
if (isset($_GET['_debug'])) {
    error_log("MoeHome {$cacheVersion} - Execution time: {$executionTime}ms");
}
