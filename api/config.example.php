<?php
/**
 * MoeHome 虚拟主机版配置文件
 * 将此文件复制到 api/config.php 或直接在 api/ 目录修改
 */

// ==================== 站点基础配置 ====================
$config['site'] = [
    'name' => 'YourName',
    'tagline' => '技术博主 / 开源爱好者 / AI探索者',
    'url' => 'https://example.com',
    'ogImage' => 'https://example.com/images/avatar.webp',
];

// ==================== 独立页面 SEO 配置 ====================
$config['pages'] = [
    'moments' => [
        'title' => '动态',
        'tagline' => '我的碎片化分享...',
        'description' => '个人动态，记录生活点滴',
        'keywords' => ['动态', '瞬间', '生活记录'],
    ],
    'guestbook' => [
        'title' => '留言',
        'tagline' => '欢迎在这里留下你的足迹...',
        'description' => '留言板，欢迎留言交流',
        'keywords' => ['留言板', '评论', '交流'],
    ],
];

// ==================== 首页 SEO 配置 ====================
$config['seo'] = [
    'title' => 'YourName - 技术博主 / 开源爱好者 / AI探索者',
    'description' => 'Hi，欢迎访问我的个人主页...',
    'keywords' => ['YourName', '技术博客', 'Astro', 'Docker'],
    'og' => [
        'title' => 'YourName - 个人主页',
        'description' => '开源爱好者 / Astro爱好者 / AI探索者',
        'image' => 'https://example.com/images/avatar.webp',
    ],
];

// ==================== 主题配色 ====================
$config['theme'] = [
    'default' => 'light',
    'defaultScheme' => [
        'light' => 'coralOrange',
        'dark' => 'cyberGreen'
    ]
];

// ==================== 基础信息 ====================
$config['profile'] = [
    'name' => 'YourName',
    'tagline' => [
        'prefix' => '🐾',
        'highlight' => '欢迎来到我的主页！'
    ],
    'avatar' => 'images/avatar.webp'
];

// ==================== 终端内容 ====================
$config['identity'] = ['开源爱好者', '前端开发者', 'AI探索者'];
$config['interests'] = ['前端开发', '容器技术', '自动化部署'];
$config['gear'] = ['设备1', '设备2', '设备3'];

$config['terminal'] = [
    'title' => '🐾 user@host:~|',
    'prompts' => [
        ['command' => 'whoami', 'output' => 'identity'],
        ['command' => 'cat interests.txt', 'output' => 'interests'],
        ['command' => 'cat gear.txt', 'output' => 'gear'],
        ['command' => './wisdom.sh', 'output' => 'dynamic']
    ]
];

// ==================== 名人语录 ====================
$config['quotes'] = [
    "Empty your mind, be formless, shapeless, like water...",
    "Be water, my friend."
];

// ==================== 音乐播放器 ====================
$config['music'] = [
    'enabled' => true,
    'volume' => 0.5,
    'autoplay' => false,
    'playMode' => 'list',
    'mode' => 'meting',
    'meting' => [
        'server' => 'netease',
        'type' => 'playlist',
        'id' => '10046455237',
        'apis' => [
            'https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r',
        ],
    ],
    'local' => ['music/song1.mp3', 'music/song2.mp3'],
];

// ==================== 动画配置 ====================
$config['animation'] = [
    'fadeInDelay' => 1000,
    'typingSpeed' => 60,
    'quoteDisplayTime' => 4000,
    'quoteDeleteSpeed' => 42,
];

// ==================== RSS 配置 ====================
$config['rss'] = [
    'enabled' => true,
    'url' => 'https://yourblog.com/rss.xml',
    'count' => 4,
    'openInNewTab' => true,
    'title' => [
        'text' => 'Recent Posts',
        'icon' => 'fa-solid fa-newspaper'
    ],
    'display' => [
        'showDate' => true,
        'showDescription' => true,
        'maxDescriptionLength' => 100
    ]
];

// ==================== 图库配置 ====================
$config['gallery'] = [
    'enabled' => false,
    'title' => [
        'text' => '我的图库',
        'icon' => 'fa-solid fa-images'
    ],
    'source' => 'local',
    'count' => 8,
    'local' => [
        'directory' => 'images/gallery/'
    ],
    'huaban' => [
        'boardId' => ''
    ]
];

// ==================== 书架配置 (豆瓣) ====================
$config['books'] = [
    'enabled' => false,
    'title' => [
        'text' => '书架',
        'icon' => 'fa-solid fa-book'
    ],
    'doubanId' => '',
    'count' => 6
];

// ==================== 哔哩哔哩配置 ====================
$config['bilibili'] = [
    'enabled' => false,
    'title' => [
        'text' => '哔哩哔哩',
        'icon' => 'fa-brands fa-bilibili'
    ],
    'uid' => '',
    'count' => 4
];

// ==================== Memos 动态 ====================
$config['moments'] = [
    'enabled' => true,
    'memosUrl' => 'https://your-memos-instance.com/',
    'count' => 10,
    'tags' => [],
    'showSkeleton' => true,
];

// ==================== 留言板 ====================
$config['guestbook'] = [
    'enabled' => true,
    'provider' => 'waline',
    'server' => 'https://your-waline.vercel.app',
    'site' => 'MoeHome',
    'placeholder' => '欢迎留下你的信号...',
    'limits' => [
        'comments' => ['newest' => 30, 'hot' => 30],
        'barrage' => ['pinned' => 5, 'hot' => 10, 'latest' => 5]
    ]
];

// ==================== 链接模块 ====================
$config['linksConfig'] = [
    'enabled' => true,
    'title' => [
        'text' => 'Quick Links',
        'icon' => 'fa-solid fa-link',
    ],
];

// ==================== 社交链接 ====================
$config['links'] = [
    [
        'name' => 'Blog',
        'description' => '技术文章 & 教程',
        'url' => 'https://yourblog.com',
        'icon' => 'fa-solid fa-pen-nib',
        'brand' => 'blog',
        'external' => true,
        'color' => '#00ff9f',
        'enabled' => true
    ],
    [
        'name' => 'Email',
        'description' => '联系 & 合作',
        'url' => 'mailto:admin@example.com',
        'icon' => 'fa-solid fa-envelope',
        'brand' => 'email',
        'external' => false,
        'color' => '#ea4335',
        'antiCrawler' => true,
        'enabled' => true
    ]
];

// ==================== 赞赏支持 ====================
$config['donation'] = [
    'enabled' => true,
    'title' => [
        'text' => '赞助支持',
        'icon' => 'fa-solid fa-mug-hot',
    ],
    'message' => '如果我的内容对你有帮助，欢迎请我喝杯咖啡~',
    'methods' => [
        ['name' => '微信支付', 'key' => 'wechat', 'icon' => 'fa-brands fa-weixin', 'qrImage' => 'images/wechat.png', 'enabled' => true],
        ['name' => '支付宝', 'key' => 'alipay', 'icon' => 'fa-brands fa-alipay', 'qrImage' => 'images/alipay.png', 'enabled' => true],
        ['name' => '爱发电', 'key' => 'afdian', 'icon' => 'fa-solid fa-heart', 'url' => 'https://ifdian.net/a/yourname', 'enabled' => true],
    ],
];

// ==================== 页脚 ====================
$config['footer'] = [
    'copyright' => [
        'year' => '2018-2026',
        'name' => 'Your Name',
        'url' => 'https://yourblog.com/'
    ],
    'icp' => [
        'enabled' => false,
        'number' => '京ICP备XXXXXXXX号'
    ]
];

// ==================== 安全提示 ====================
$config['notice'] = [
    'enabled' => true,
    'type' => 'warning',
    'icon' => 'fa-solid fa-shield-halved',
    'text' => '声明：本人不会主动邀请或联系任何人...',
];

// ==================== 统计代码 ====================
$config['analytics'] = [
    'googleAnalytics' => [
        'enabled' => false,
        'id' => 'G-XXXXXXXXXX'
    ],
    'microsoftClarity' => [
        'enabled' => false,
        'id' => 'xxxxxxxxxxxx'
    ],
    'umami' => '',
    'customScripts' => []
];

// ==================== API 配置 ====================
// GitHub Token (可选，用于提高 API 请求限制)
// 申请地址: https://github.com/settings/tokens
$config['api']['github_token'] = '';

// RSS 缓存时间 (秒)
$config['api']['rss_cache'] = 3600;

// GitHub 数据缓存时间 (秒)
$config['api']['github_cache'] = 1800;

// 是否启用 HTTPS 验证 (某些虚拟主机需要关闭)
$config['api']['ssl_verify'] = true;
