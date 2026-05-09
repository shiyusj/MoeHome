<?php
/**
 * MoeHome 虚拟主机版 - 首页
 * 需要 PHP 5.6+ 和以下扩展:
 * - json
 * - mbstring
 * - curl 或 allow_url_fopen
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/api/config.example.php';

$site = $config['site'] ?? [];
$seo = $config['seo'] ?? [];
$profile = $config['profile'] ?? [];
$theme = $config['theme'] ?? [];
$music = $config['music'] ?? [];
$terminal = $config['terminal'] ?? [];
$identity = $config['identity'] ?? [];
$interests = $config['interests'] ?? [];
$gear = $config['gear'] ?? [];
$quotes = $config['quotes'] ?? [];
$rss = $config['rss'] ?? [];
$projects = $config['projects'] ?? [];
$contribution = $config['contribution'] ?? [];
$moments = $config['moments'] ?? [];
$guestbook = $config['guestbook'] ?? [];
$linksConfig = $config['linksConfig'] ?? [];
$links = $config['links'] ?? [];
$donation = $config['donation'] ?? [];
$footer = $config['footer'] ?? [];
$notice = $config['notice'] ?? [];
$analytics = $config['analytics'] ?? [];
$animation = $config['animation'] ?? [];

$siteUrl = $site['url'] ?? '';
$siteName = $site['name'] ?? '';
$siteTagline = $site['tagline'] ?? '';
$ogImage = $site['ogImage'] ?? '';

$pageTitle = $seo['title'] ?? $siteName;
$pageDescription = $seo['description'] ?? '';
$pageKeywords = is_array($seo['keywords'] ?? null) ? implode(', ', $seo['keywords']) : '';

$ogTitle = $seo['og']['title'] ?? $pageTitle;
$ogDescription = $seo['og']['description'] ?? $pageDescription;
$ogImg = $seo['og']['image'] ?? $ogImage;

$profileName = $profile['name'] ?? '';
$profileTaglinePrefix = $profile['tagline']['prefix'] ?? '';
$profileTaglineHighlight = $profile['tagline']['highlight'] ?? '';
$avatar = $profile['avatar'] ?? 'images/avatar.webp';

$themeDefault = $theme['default'] ?? 'light';
$themeDefaultScheme = $theme['defaultScheme'] ?? ['light' => 'coralOrange', 'dark' => 'cyberGreen'];

$terminalTitle = $terminal['title'] ?? '🐾 user@host:~|';

$identityJson = json_encode($identity, JSON_UNESCAPED_UNICODE);
$interestsJson = json_encode($interests, JSON_UNESCAPED_UNICODE);
$quotesJson = json_encode($quotes, JSON_UNESCAPED_UNICODE);

$gearHtml = '';
if (!empty($gear)) {
    $gearJson = json_encode($gear, JSON_UNESCAPED_UNICODE);
    $gearHtml = <<<HTML
        <div class="prompt-line" style="margin-top: 8px;">
            <span class="prompt">\$ </span>
            <span class="command">cat gear.txt</span>
        </div>
        <div class="output" id="gear-output" data-value='{$gearJson}'></div>
HTML;
}

$musicEnabled = $music['enabled'] ?? false;
$musicMode = $music['mode'] ?? 'meting';
$musicVolume = $music['volume'] ?? 0.5;
$musicAutoplay = ($music['autoplay'] ?? false) ? 'true' : 'false';
$musicPlayMode = $music['playMode'] ?? 'list';
$musicData = '';

if ($musicEnabled) {
    if ($musicMode === 'meting' && isset($music['meting'])) {
        $meting = $music['meting'];
        $musicServer = $meting['server'] ?? 'netease';
        $musicType = $meting['type'] ?? 'playlist';
        $musicId = $meting['id'] ?? '';
        $musicApis = $meting['apis'] ?? ['https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r'];
        $musicApi = urlencode($musicApis[0]);
        $musicData = "data-mode=\"meting\" data-server=\"{$musicServer}\" data-type=\"{$musicType}\" data-id=\"{$musicId}\" data-api=\"{$musicApi}\"";
    } elseif ($musicMode === 'local' && isset($music['local'])) {
        $localMusic = json_encode($music['local'], JSON_UNESCAPED_UNICODE);
        $musicData = "data-mode=\"local\" data-songs='{$localMusic}'";
    }
}

$musicHtml = $musicEnabled ? <<<HTML
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
HTML : '<hr class="section-divider">';

$rssEnabled = $rss['enabled'] ?? false;
$rssHtml = '';

if ($rssEnabled) {
    $rssUrl = urlencode($rss['url'] ?? '');
    $rssCount = $rss['count'] ?? 4;
    $rssTitle = $rss['title']['text'] ?? 'Recent Posts';
    $rssIcon = $rss['title']['icon'] ?? 'fa-solid fa-newspaper';
    $rssShowDate = ($rss['display']['showDate'] ?? true) ? 'true' : 'false';
    $rssShowDesc = ($rss['display']['showDescription'] ?? true) ? 'true' : 'false';
    $rssMaxDescLen = $rss['display']['maxDescriptionLength'] ?? 100;
    $rssOpenNewTab = ($rss['openInNewTab'] ?? true) ? 'target="_blank" rel="noopener"' : '';

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

$projectsEnabled = $projects['enabled'] ?? false;
$projectsHtml = '';
$githubUser = '';
$githubUsername = '';

if ($projectsEnabled) {
    $githubUser = $projects['githubUser'] ?? '';
    $githubUsername = preg_replace('#^https?://github\.com/?#', '', rtrim($githubUser, '/'));
    $projectsCount = $projects['count'] ?? 5;
    $projectsExclude = implode(',', $projects['exclude'] ?? []);
    $projectsTitle = $projects['title']['text'] ?? '我的项目';
    $projectsIcon = $projects['title']['icon'] ?? 'fa-solid fa-folder-open';

    $projectsHtml = <<<HTML
        <section class="section projects-section lazy-load" data-delay="5">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="{$projectsIcon}"></i>
                    <span>{$projectsTitle}</span>
                </h2>
                <a href="{$githubUser}" class="section-more" target="_blank" rel="noopener noreferrer">
                    <span>查看更多</span>
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
            <div class="projects-grid" id="projects-grid" data-username="{$githubUsername}" data-count="{$projectsCount}" data-exclude="{$projectsExclude}">
                <div class="projects-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>加载中...</span>
                </div>
            </div>
        </section>
HTML;
}

$contributionEnabled = $contribution['enabled'] ?? false;
$contributionHtml = '';
$contributionUser = $contribution['githubUser'] ?: $githubUsername;
$useRealData = ($contribution['useRealData'] ?? true) ? 'true' : 'false';

if ($contributionEnabled) {
    $contributionHtml = <<<HTML
        <section class="section contribution-section">
            <div class="contribution-calendar" id="contribution-calendar" data-username="{$contributionUser}" data-real="{$useRealData}" data-year="2024"></div>
        </section>
HTML;
}

$linksEnabled = $linksConfig['enabled'] ?? false;
$linksHtml = '';

if ($linksEnabled) {
    $linksTitle = $linksConfig['title']['text'] ?? 'Quick Links';
    $linksIcon = $linksConfig['title']['icon'] ?? 'fa-solid fa-link';
    $linksItemsHtml = '';

    foreach ($links as $link) {
        if (!($link['enabled'] ?? true)) continue;

        $linkName = htmlspecialchars($link['name'] ?? '');
        $linkDesc = htmlspecialchars($link['description'] ?? '');
        $linkUrl = htmlspecialchars($link['url'] ?? '#');
        $linkIcon = $link['icon'] ?? 'fa-solid fa-link';
        $linkColor = $link['color'] ?? 'var(--accent)';
        $linkExternal = ($link['external'] ?? false) ? 'target="_blank" rel="noopener noreferrer"' : '';
        $linkBrand = $link['brand'] ?? '';
        $antiCrawler = $link['antiCrawler'] ?? false;

        $linkTag = 'a';
        $linkAttrs = "href=\"{$linkUrl}\" {$linkExternal}";

        if ($antiCrawler && strpos($linkUrl, 'mailto:') === 0) {
            $email = str_replace('mailto:', '', $linkUrl);
            $encodedEmail = base64_encode($email);
            $linkAttrs = "href=\"javascript:void(0)\" onclick=\"location.href='mailto:'+atob('{$encodedEmail}')\"";
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
        <section class="section links-section lazy-load" data-delay="6">
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

$donationEnabled = $donation['enabled'] ?? false;
$donationHtml = '';
$donationModalHtml = '';

if ($donationEnabled) {
    $donationTitle = $donation['title']['text'] ?? '赞助支持';
    $donationIcon = $donation['title']['icon'] ?? 'fa-solid fa-mug-hot';
    $donationMessage = htmlspecialchars($donation['message'] ?? '');
    $methodsHtml = '';
    $modalMethodsHtml = '';

    foreach ($donation['methods'] ?? [] as $method) {
        if (!($method['enabled'] ?? true)) continue;

        $methodName = htmlspecialchars($method['name'] ?? '');
        $methodKey = htmlspecialchars($method['key'] ?? '');
        $methodIcon = $method['icon'] ?? 'fa-solid fa-gift';

        if (!empty($method['qrImage'])) {
            $qrSrc = htmlspecialchars($method['qrImage']);
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
            $url = htmlspecialchars($method['url']);
            $methodsHtml .= <<<HTML
                <a class="donation-method" href="{$url}" target="_blank" rel="noopener noreferrer" aria-label="{$methodName}">
                    <i class="{$methodIcon}"></i>
                    <span>{$methodName}</span>
                </a>
HTML;
        }
    }

    $donationHtml = <<<HTML
        <section class="section donation-section lazy-load" data-delay="7">
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

$noticeEnabled = $notice['enabled'] ?? false;
$noticeHtml = '';

if ($noticeEnabled) {
    $noticeType = $notice['type'] ?? 'info';
    $noticeIcon = $notice['icon'] ?? 'fa-solid fa-info-circle';
    $noticeText = htmlspecialchars($notice['text'] ?? '');

    $noticeHtml = <<<HTML
        <div class="notice notice-{$noticeType} lazy-load" data-delay="8" role="alert">
            <i class="{$noticeIcon}"></i>
            <span>{$noticeText}</span>
        </div>
HTML;
}

$fadeInDelay = $animation['fadeInDelay'] ?? 1000;
$typingSpeed = $animation['typingSpeed'] ?? 60;
$quoteDisplayTime = $animation['quoteDisplayTime'] ?? 4000;
$quoteDeleteSpeed = $animation['quoteDeleteSpeed'] ?? 42;

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

$analyticsHtml = '';

if (!empty($analytics['googleAnalytics']['enabled']) && !empty($analytics['googleAnalytics']['id'])) {
    $analyticsHtml .= <<<HTML
    <script async src="https://www.googletagmanager.com/gtag/js?id={$analytics['googleAnalytics']['id']}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{$analytics['googleAnalytics']['id']}');
    </script>
HTML;
}

if (!empty($analytics['microsoftClarity']['enabled']) && !empty($analytics['microsoftClarity']['id'])) {
    $analyticsHtml .= <<<HTML
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "{$analytics['microsoftClarity']['id']}");
    </script>
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

$skeletonMusic = $musicEnabled ? '<div class="skeleton-music skeleton"></div>' : '';
$skeletonRss = $rssEnabled ? '<div class="skeleton-rss skeleton"></div>' : '';
$skeletonProjects = $projectsEnabled ? '<div class="skeleton-projects skeleton"></div>' : '';
$skeletonLinks = $linksEnabled ? '<div class="skeleton-links skeleton"></div>' : '';
$skeletonDonation = $donationEnabled ? '<div class="skeleton-donation skeleton"></div>' : '';
$skeletonNotice = $noticeEnabled ? '<div class="skeleton-notice skeleton"></div>' : '';
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
        <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle); ?>" />
        <meta property="og:description" content="<?php echo htmlspecialchars($ogDescription); ?>" />
        <meta property="og:image" content="<?php echo htmlspecialchars($ogImg); ?>" />

        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:title" content="<?php echo htmlspecialchars($ogTitle); ?>" />
        <meta property="twitter:description" content="<?php echo htmlspecialchars($ogDescription); ?>" />
        <meta property="twitter:image" content="<?php echo htmlspecialchars($ogImg); ?>" />

        <link rel="icon" type="image/webp" href="<?php echo htmlspecialchars($avatar); ?>" />

        <?php echo $themeInitScript; ?>

        <link rel="preconnect" href="https://fonts.googleapis.cn" />
        <link rel="preconnect" href="https://fonts.gstatic.cn" crossorigin />
        <link href="https://fonts.googleapis.cn/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <link rel="preload" href="style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="style.css"></noscript>

        <?php echo $analyticsHtml; ?>

        <script>
            window.MOEHOME_CONFIG = {
                identity: <?php echo $identityJson; ?>,
                interests: <?php echo $interestsJson; ?>,
                quotes: <?php echo $quotesJson; ?>,
                animation: {
                    typingSpeed: <?php echo intval($typingSpeed); ?>,
                    quoteDisplayTime: <?php echo intval($quoteDisplayTime); ?>,
                    quoteDeleteSpeed: <?php echo intval($quoteDeleteSpeed); ?>
                },
                apiBase: 'api'
            };
        </script>
    </head>
    <body>
        <a href="#actual-content" class="skip-link">跳到主要内容</a>

        <nav class="navbar" id="navbar">
            <div class="navbar-inner">
                <a href="/" class="navbar-brand">
                    <span class="prompt">$</span>
                    <span class="brand-name"><?php echo htmlspecialchars($siteName); ?></span>
                </a>
                <div class="navbar-menu" id="navbar-menu">
                    <a href="/" class="nav-link active">首页</a>
                    <?php if ($moments['enabled'] ?? false): ?>
                    <a href="moments.html" class="nav-link">动态</a>
                    <?php endif; ?>
                    <?php if ($guestbook['enabled'] ?? false): ?>
                    <a href="guestbook.html" class="nav-link">留言</a>
                    <?php endif; ?>
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
                    <button class="theme-mode-item" data-mode="auto">
                        <i class="fas fa-adjust"></i>
                        <span>跟随系统</span>
                    </button>
                    <button class="theme-mode-item" data-mode="light">
                        <i class="fas fa-sun"></i>
                        <span>浅色</span>
                    </button>
                    <button class="theme-mode-item" data-mode="dark">
                        <i class="fas fa-moon"></i>
                        <span>深色</span>
                    </button>
                </div>
            </div>
            <div class="theme-divider"></div>
            <div class="theme-section">
                <div class="theme-section-label">配色方案</div>
                <div class="theme-scheme-list" id="theme-scheme-list"></div>
            </div>
        </div>

        <div class="nav-mobile-dropdown" id="nav-mobile-dropdown">
            <a href="/" class="nav-link active">首页</a>
            <?php if ($moments['enabled'] ?? false): ?>
            <a href="moments.html" class="nav-link">动态</a>
            <?php endif; ?>
            <?php if ($guestbook['enabled'] ?? false): ?>
            <a href="guestbook.html" class="nav-link">留言</a>
            <?php endif; ?>
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
                <?php echo $skeletonProjects; ?>
                <?php echo $skeletonLinks; ?>
                <?php echo $skeletonDonation; ?>
                <?php echo $skeletonNotice; ?>
                <div class="skeleton-divider skeleton"></div>
                <div class="skeleton-footer skeleton"></div>
            </div>

            <div class="profile content-initial-hidden" id="actual-content" aria-busy="true">
                <div class="avatar-wrapper">
                    <div class="avatar image-placeholder" id="avatar-placeholder">
                        <img id="avatar-img" src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($profileName); ?> Avatar" width="140" height="140" data-blur />
                    </div>
                    <div class="status"></div>
                </div>

                <h1 class="name" id="profile-name"><?php echo htmlspecialchars($profileName); ?></h1>

                <p class="tagline" id="profile-tagline">
                    <span class="paw-icon"><?php echo htmlspecialchars($profileTaglinePrefix); ?></span>
                    <span class="highlight"><?php echo htmlspecialchars($profileTaglineHighlight); ?></span>
                </p>

                <?php echo $musicHtml; ?>

                <div class="terminal lazy-load" id="terminal" data-delay="2">
                    <div class="terminal-header">
                        <div class="traffic-lights" aria-hidden="true">
                            <span class="close"></span>
                            <span class="minimize"></span>
                            <span class="maximize"></span>
                        </div>
                        <div class="title" id="terminal-title"><?php echo htmlspecialchars($terminalTitle); ?></div>
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

                <?php echo $contributionHtml; ?>
                <?php echo $rssHtml; ?>
                <?php echo $projectsHtml; ?>
                <?php echo $linksHtml; ?>
                <?php echo $donationHtml; ?>
                <?php echo $noticeHtml; ?>

                <footer class="footer">
                    <div class="footer-content">
                        <p class="footer-copyright">
                            &copy; <?php echo htmlspecialchars($footer['copyright']['year'] ?? date('Y')); ?>
                            <a href="<?php echo htmlspecialchars($footer['copyright']['url'] ?? '#'); ?>"><?php echo htmlspecialchars($footer['copyright']['name'] ?? $siteName); ?></a>
                        </p>
                        <?php if (!empty($footer['icp']['enabled'])): ?>
                        <p class="footer-icp">
                            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($footer['icp']['number'] ?? ''); ?></a>
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
