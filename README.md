# MoeHome 个人主页系统

**纯静态优先 + 轻量博客系统 = 个人品牌中心化入口**

MoeHome 是一款精心设计的个人主页系统，将分散的社交资产（博客文章、GitHub 项目、音乐品味、动态分享）聚合为统一的品牌体验。它既保留了传统静态站点的极致性能，又融入了现代化的博客写作功能，让个人品牌展示更加丰富立体。

配置驱动、零运行时依赖，支持多主题配色、博客文章管理、RSS 聚合、GitHub 集成，专注性能与视觉体验的平衡。

![许可协议](https://img.shields.io/badge/许可协议-MIT-green)
![版本号](https://img.shields.io/badge/版本-v2.3.0-blue)

---

## 一、功能亮点

### １.１ 博客文章系统

全新轻量级博客写作与展示功能，让静态主页也能拥有动态内容：

- **后台管理面板**：独立的博客文章管理后台，支持文章的增删改查操作；
- **分类体系**：内置四类预置分类——技术文章、生活随笔、项目分享、其他，支持自定义扩展；
- **标签系统**：每篇文章支持多个标签，便于内容聚合与检索；
- **独立博客页面**：独立的 `/posts` 路由页面，支持分类筛选与分页导航；
- **首页聚合展示**：在首页「最近更新」区域自动展示最新文章列表；
- **写作活跃度**：将原有的「我的项目」板块升级为文章写作活跃度展示，用数据呈现内容产出。

### １.２ 静态页面系统

继承经典静态站点的核心优势：

- **配置驱动**：所有内容在 `src/config.js` 中管理，无需修改代码即可更新内容；
- **零运行时依赖**：前端无任何框架依赖，构建时自动压缩优化；
- **秒级加载**：构建生成纯静态 HTML，首屏性能优异；
- **SEO 友好**：完整的 meta 标签、结构化数据、Open Graph、Twitter Card 支持；
- **多部署方式**：构建产物即插即用，支持任意静态托管平台。

### １.３ 现代化交互体验

精致视觉与流畅交互的融合：

- **多主题配色**：跟随系统、浅色、暗色三种模式，八种精选配色方案；
- **玻璃光泽卡片**：现代化的毛玻璃效果，层次分明又不喧宾夺主；
- **自定义光标**：终端风格光标效果，契合极客气质；
- **打字机效果**：品牌区自动循环打字，动态展示个人标签；
- **响应式设计**：完美适配移动端和桌面端，支持触摸手势操作；
- **动画系统**：页面元素渐入动画、悬停效果、滚动进度指示。

### １.４ 社交功能集成

丰富的第三方服务集成能力：

- **GitHub 模块**：自动展示项目和贡献图，支持真实数据或模拟数据；
- **RSS 聚合**：构建时预获取博客文章列表，无运行时延迟；
- **音乐播放器**：支持 Meting API 和本地音乐文件，简约设计自然融入页面；
- **Memos 动态**：集成 Memos 实例，时间线布局，支持图片、音视频、Markdown；
- **留言板**：终端风格弹幕交互，支持 Waline 和 Artalk 两大评论系统；
- **赞赏支持**：二维码扫码和外部链接双模式，支持微信、支付宝、爱发电等。

### １.５ 安全与可维护性

代码质量与安全防护并重：

- **邮箱反爬虫**：mailto 链接动态编码，防止邮箱地址被爬虫抓取；
- **SQL 注入防护**：全部数据库操作使用预处理语句；
- **XSS 防护**：所有用户输入输出均进行转义处理；
- **HTTPS 强制**：推荐生产环境全程强制 HTTPS；
- **详细日志**：后台操作有完整的行为日志记录；
- **模块化架构**：前端模块化开发，后端分层设计，便于维护扩展。

---

## 二、页面预览

### 暗色主题

![暗色主题预览](./src/images/screenshot-dark.png)

### 亮色主题

![亮色主题预览](./src/images/screenshot-light.png)

---

## 三、快速开始

### ３.１ 环境要求

在开始安装之前，请确保你的服务器满足以下环境要求：

- **PHP**：推荐 PHP 8.0 及以上版本（用于后台管理和博客 API）；
- **MySQL**：MySQL 5.7 或更高版本（用于存储博客文章数据）；
- **Node.js**：推荐 Node.js 18 及以上版本（用于构建静态页面）；
- **Web 服务器**：Nginx 1.18+ 或 Apache 2.4+（支持 URL 重写）。

对于纯静态展示场景（如 GitHub Pages、Vercel、Netlify），只需确保静态文件托管环境支持 URL 重写即可。

### ３.２ 安装步骤

#### 第一步：下载源码

你可以通过以下任一方式获取最新版本的 MoeHome 源码：

```bash
# 方式一：克隆 Git 仓库（推荐，便于后续更新）
git clone https://github.com/moewah/MoeHome.git
cd MoeHome

# 方式二：直接下载压缩包
# 下载 /workspace/moehome-v2.3.zip 并解压
```

#### 第二步：安装前端依赖

安装 Node.js 开发依赖，用于构建静态页面：

```bash
npm install
```

安装过程会自动下载 terser、lightningcss、html-minifier-terser、sharp 等构建工具。

#### 第三步：配置站点信息

编辑 `src/config.js` 文件，修改以下基础配置：

```javascript
site: {
    name: '你的名字',
    tagline: '技术博主 / 开源爱好者 / AI 探索者',
    url: 'https://你的域名.com',
    ogImage: 'https://你的域名.com/images/avatar.webp'
}
```

详细的配置项说明请参阅本文档「配置手册」章节。

#### 第四步：构建静态页面

配置完成后，执行构建命令生成静态文件：

```bash
npm run build
```

构建产物默认输出到 `dist/` 目录，包括压缩后的 HTML、CSS、JavaScript 和图片资源。

#### 第五步：本地预览

构建完成后，可以使用本地服务器预览效果：

```bash
npm run serve
# 访问 http://localhost:8080
```

### ３.３ PHP 后端安装（可选）

如果需要使用博客文章管理功能，必须安装 PHP 后端环境。

#### 环境要求

- PHP 8.0+（需要 pdo、pdo_mysql、json、mbstring 等扩展）；
- MySQL 5.7+；
- Web 服务器（Nginx 或 Apache）。

#### 安装步骤

##### 步骤一：创建数据库

登录 MySQL，创建一个新的数据库用于存储博客数据：

```sql
CREATE DATABASE moehome DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

如果需要独立的数据库用户，执行以下命令：

```sql
CREATE USER 'moehome'@'localhost' IDENTIFIED BY '你的强密码';
GRANT ALL PRIVILEGES ON moehome.* TO 'moehome'@'localhost';
FLUSH PRIVILEGES;
```

##### 步骤二：配置数据库连接

复制配置文件并编辑：

```bash
cp api/config.example.php api/config.php
```

编辑 `api/config.php`，填入数据库连接信息：

```php
$config['database'] = [
    'host' => 'localhost',
    'name' => 'moehome',
    'username' => 'moehome',
    'password' => '你的强密码'
];
```

##### 步骤三：设置后台访问密码

编辑 `admin/api/config.php` 文件，设置后台管理员密码：

```php
$config['auth']['password'] = '你的后台管理密码';
```

##### 步骤四：配置 URL 重写规则

如果你的 Web 服务器使用 Nginx，确保配置文件中有以下重写规则：

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location /admin/ {
    try_files $uri $uri/ /admin/index.php?$query_string;
}

location /api/ {
    try_files $uri $uri/ /api/posts.php?$query_string;
}
```

如果使用 Apache，确保 `.htaccess` 文件存在且内容正确。

##### 步骤五：访问后台管理

完成以上配置后，访问 `https://你的域名/admin/` 进入后台管理页面。

首次访问会跳转到安装向导页面，按照提示完成初始化操作。安装向导会自动创建必要的数据库表结构和初始数据。

---

## 四、配置手册

### ４.１ 站点基础配置

`src/config.js` 文件中的 `site` 对象控制站点的基础信息：

```javascript
site: {
    name: '你的名字',
    tagline: '技术博主 / 开源爱好者 / AI 探索者',
    url: 'https://example.com',
    ogImage: 'https://example.com/images/avatar.webp'
}
```

| 配置项 | 说明 |
|--------|------|
| `name` | 站点名称，用于页面标题后缀显示 |
| `tagline` | 站点标语，显示在首页主标题下方 |
| `url` | 站点完整 URL，用于生成 Open Graph 图片等绝对路径 |
| `ogImage` | 默认 Open Graph 图片，所有页面共用此图片作为分享预览图 |

### ４.２ 博客文章配置

博客功能相关配置，用于控制首页文章展示和独立博客页面的行为：

```javascript
posts: {
    enabled: true,              // 是否启用博客功能
    count: 4,                   // 首页展示的文章数量
    openInNewTab: false,         // 是否在新标签页打开文章
    title: {
        text: '近期更新',        // 模块标题文本
        icon: 'fa-solid fa-newspaper'  // 模块标题图标
    },
    display: {
        showDate: true,          // 是否显示发布日期
        showDescription: true,   // 是否显示文章摘要
        maxDescriptionLength: 100  // 摘要最大字符数
    },
    categories: [
        { slug: 'tech', name: '技术文章', icon: 'fa-solid fa-code' },
        { slug: 'life', name: '生活随笔', icon: 'fa-solid fa-coffee' },
        { slug: 'project', name: '项目分享', icon: 'fa-solid fa-folder-open' },
        { slug: 'default', name: '其他', icon: 'fa-solid fa-file' }
    ]
}
```

| 配置项 | 说明 |
|--------|------|
| `enabled` | 设为 `false` 可完全禁用博客功能 |
| `count` | 首页「最近更新」区域显示的文章数量上限 |
| `openInNewTab` | 点击文章链接时是否强制在新标签页打开 |
| `display.showDate` | 是否在文章卡片上显示发布日期 |
| `display.showDescription` | 是否显示文章摘要内容 |
| `display.maxDescriptionLength` | 摘要文字的最大字符数，超出部分自动截断 |
| `categories` | 文章分类数组，可按需增删修改 |

#### 分类路由说明

博客页面支持通过 URL 参数按分类筛选文章：

| URL | 效果 |
|-----|------|
| `/posts` | 显示所有文章 |
| `/posts?category=tech` | 只显示「技术文章」分类的文章 |
| `/posts?category=life` | 只显示「生活随笔」分类的文章 |
| `/posts?category=project` | 只显示「项目分享」分类的文章 |

### ４.３ 独立页面 SEO 配置

每个独立页面可定义自己的 SEO 元信息，标题会自动生成为「页面标题 | 站点名称」的格式：

```javascript
pages: {
    posts: {
        title: '博客',
        tagline: '我的技术文章与思考记录',
        description: '博客文章动态，记录技术分享与生活感悟',
        keywords: ['博客', '文章', '技术分享']
    },
    moments: {
        title: '动态',
        tagline: '我的碎片化分享...',
        description: '个人动态，记录生活点滴',
        keywords: ['动态', '瞬间', '生活记录']
    },
    guestbook: {
        title: '留言',
        tagline: '欢迎在这里留下你的足迹...',
        description: '留言板，欢迎留言交流',
        keywords: ['留言板', '评论', '交流']
    }
}
```

| 配置项 | 说明 |
|--------|------|
| `title` | 页面标题，自动追加站点名称作为后缀 |
| `tagline` | 页面标语，显示在标题下方的副标题 |
| `description` | 页面描述，用于 meta description 和 Open Graph |
| `keywords` | 关键词数组，用于 meta keywords |

### ４.４ 主题配色配置

支持模式切换和配色方案切换，用户在页面上的切换操作只在当前会话生效，刷新页面后恢复默认配置。

#### 模式切换

| 模式值 | 说明 |
|--------|------|
| `light` | 浅色模式，始终使用浅色主题 |
| `dark` | 深色模式，始终使用深色主题 |
| `auto` | 跟随系统，根据操作系统偏好自动切换 |

#### 内置配色方案

| 方案标识 | 名称 | 类型 | 说明 |
|----------|------|------|------|
| `coralOrange` | 珊瑚橙 | 亮色 | 系统默认亮色方案，温暖活力 |
| `nordSnowStorm` | 冰川青 | 亮色 | Nord 霜雪青色调，清新冷峻 |
| `gruvboxLight` | 复古风 | 亮色 | 复古暖色调，怀旧氛围 |
| `ayuLight` | 简约风 | 亮色 | 简约清爽，适合阅读 |
| `cyberGreen` | 赛博绿 | 暗色 | 系统默认暗色方案，赛博朋克风 |
| `catppuccinMocha` | 摩卡色 | 暗色 | 柔和粉彩，温暖舒适 |
| `kanagawaDragon` | 浮世绘 | 暗色 | 日式浮世绘风格，紫蓝渐变 |
| `rosePineMoon` | 鸢尾紫 | 暗色 | 优雅鸢尾紫，神秘高贵 |

#### 配置示例

```javascript
theme: {
    default: 'auto',    // 首屏默认模式：light | dark | auto
    defaultScheme: {
        light: 'coralOrange',      // 默认亮色配色方案
        dark: 'cyberGreen'         // 默认暗色配色方案
    }
}
```

**衍生色说明**：悬停效果、阴影颜色、玻璃光泽等衍生颜色由内置算法自动计算，无需手动配置。

### ４.５ 导航栏配置

顶部固定导航栏，包含品牌区、导航链接、主题切换和移动端菜单：

```javascript
nav: {
    enabled: true,
    brand: {
        showPrompt: true,
        hoverText: '~/whoami'
    },
    menus: [
        {
            name: '资源导航',
            items: [
                { name: '工具推荐', url: 'https://example.com/tools', external: true },
                { name: '友情链接', url: 'https://example.com/links', external: true }
            ]
        }
    ]
}
```

| 配置项 | 说明 |
|--------|------|
| `brand.showPrompt` | 是否显示终端提示符 `$` 符号 |
| `brand.hoverText` | 鼠标悬停时循环打字显示的文字内容 |
| `menus` | 自定义二级下拉菜单数组，每个菜单项可包含多个链接 |

**主题切换**：点击配色按钮可展开菜单，选择模式（自动／浅色／暗色）和配色方案。

**移动端手势**：支持从屏幕右边缘向左滑动打开侧边栏，侧边栏内向右滑动关闭。

### ４.６ 基础信息配置

```javascript
profile: {
    name: '你的名字',
    tagline: {
        prefix: '🐾',
        highlight: '欢迎来到我的主页！'
    },
    avatar: 'images/avatar.webp'
}
```

### ４.７ Favicon 配置

```javascript
favicon: {
    path: ''    // 留空则从头像自动生成，支持自定义文件路径如 'images/favicon.ico'
}
```

自动生成功能会创建三个尺寸：16×16（小图标）、32×32（ICO 文件）、180×180（Apple Touch Icon）。

### ４.８ 终端内容配置

终端模块展示个人身份、兴趣和装备信息：

```javascript
identity: ['开源爱好者', '前端开发者', 'AI探索者'],
interests: ['前端开发', '容器技术', '自动化部署'],
gear: ['MacBook Pro M3', 'HHKB Professional HYBRID', 'LG 27UK850'],

terminal: {
    title: '🐾 user@host:~|',
    prompts: [
        { command: 'whoami', output: 'identity' },
        { command: 'cat interests.txt', output: 'interests' },
        { command: 'cat gear.txt', output: 'gear' },
        { command: './wisdom.sh', output: 'dynamic' }
    ]
}
```

| 配置项 | 说明 |
|--------|------|
| `identity` | 身份标签数组，执行 `whoami` 命令时依次展示 |
| `interests` | 兴趣领域数组，执行 `cat interests.txt` 时展示 |
| `gear` | 装备列表数组，执行 `cat gear.txt` 时展示，留空则不显示该命令 |
| `terminal.title` | 终端窗口标题栏显示的文字 |
| `terminal.prompts` | 命令列表，定义终端自动执行的命令序列 |

### ４.９ 名人语录配置

```javascript
quotes: [
    "Empty your mind, be formless, shapeless, like water...",
    "Be water, my friend."
]
```

语录会在终端下方随机展示，切换间隔由动画配置控制。

### ４.１０ 音乐播放器配置

支持 Meting API 在线音乐源和本地音乐文件两种模式：

```javascript
music: {
    enabled: true,
    volume: 0.5,           // 默认音量，取值范围 0-1
    autoplay: false,        // 是否自动播放（大多数浏览器会阻止自动播放）
    playMode: 'list',       // 播放模式：list=列表循环 | one=单曲循环 | random=随机
    mode: 'meting',        // 模式：meting=在线API | local=本地文件

    // Meting API 模式（在线音乐）
    meting: {
        server: 'netease',  // 平台：netease | tencent | kugou | xiami | baidu
        type: 'playlist',   // 类型：song | playlist | album | search | artist
        id: '10046455237',  // 歌单／单曲 ID，从平台分享链接中提取
        apis: [
            'https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r'
        ]
    },

    // 本地音乐模式
    local: ['music/song1.mp3', 'music/song2.mp3']  // 相对于 src/ 目录的路径
}
```

### ４.１１ RSS 博客文章配置

从外部 RSS 源获取文章列表，构建时预获取数据：

```javascript
rss: {
    enabled: true,
    url: 'https://yourblog.com/rss.xml',
    count: 4,
    openInNewTab: true,
    title: {
        text: 'Recent Posts',
        icon: 'fa-solid fa-newspaper'
    },
    display: {
        showDate: true,
        showDescription: true,
        maxDescriptionLength: 100
    }
}
```

### ４.１２ GitHub 模块配置

项目展示和贡献图共用 GitHub 用户配置：

```javascript
projects: {
    enabled: true,
    title: {
        text: '我的项目',
        icon: 'fa-solid fa-folder-open'
    },
    githubUser: 'https://github.com/yourusername',
    count: 5,
    exclude: ['.github']
},

contribution: {
    enabled: true,
    useRealData: true,    // true=真实 GitHub 数据 | false=随机模拟数据
    githubUser: ''         // 留空则自动使用 projects.githubUser
}
```

| 配置项 | 说明 |
|--------|------|
| `projects.githubUser` | GitHub 用户主页完整地址 |
| `projects.count` | 显示项目数量上限，按 star 数降序排列 |
| `projects.exclude` | 排除的仓库名数组，支持正则表达式匹配 |
| `contribution.useRealData` | 设为 `true` 通过 GitHub API 获取真实贡献数据 |

### ４.１３ Memos 动态配置

集成 Memos 实例，展示个人动态和碎碎念：

```javascript
moments: {
    enabled: true,
    memosUrl: 'https://your-memos-instance.com/',
    count: 10,
    tags: ['标签1', '标签2'],  // 留空获取所有公开动态
    showSkeleton: true
}
```

**功能特性**包括：时间线布局与玻璃卡片设计、图片灯箱（支持键盘导航和触摸滑动）、音视频播放器（自定义控件和全屏支持）、文档附件预览（PDF、Word、Excel 等格式）、完整 Markdown 渲染（代码高亮、表格、TODO 列表）、无限滚动加载、标签筛选、置顶动态支持。

### ４.１４ 留言板配置

终端风格留言板，支持弹幕交互和多种评论系统：

```javascript
guestbook: {
    enabled: true,
    provider: 'waline',                            // 评论系统：waline | artalk
    server: 'https://your-waline.vercel.app',      // 服务器地址
    site: 'MoeHome',                              // 站点名称（仅 Artalk 需要）
    placeholder: '欢迎留下你的信号...',
    limits: {
        comments: { newest: 30, hot: 30 },
        barrage: { pinned: 5, hot: 10, latest: 5 }
    }
}
```

| 配置项 | 说明 |
|--------|------|
| `provider` | 评论系统类型，`waline` 或 `artalk` |
| `server` | 评论系统部署后的服务端地址 |
| `site` | 站点名称，仅 Artalk 需要配置 |
| `placeholder` | 评论输入框的占位提示文字 |
| `limits.comments` | 评论列表显示数量限制 |
| `limits.barrage` | 弹幕显示数量限制，分置顶、热门、最新三类 |

### ４.１５ 社交链接配置

```javascript
links: [
    {
        name: '博客',
        description: '技术文章与教程',
        url: 'https://yourblog.com',
        icon: 'fa-solid fa-pen-nib',
        brand: 'blog',
        external: true,
        color: '#00ff9f',
        enabled: true
    },
    {
        name: '邮箱',
        description: '联系与合作',
        url: 'mailto:admin@example.com',
        icon: 'fa-solid fa-envelope',
        brand: 'email',
        external: false,
        color: '#ea4335',
        antiCrawler: true,  // 开启邮箱地址编码防爬虫
        enabled: true
    }
]
```

| 配置项 | 说明 |
|--------|------|
| `name` | 按钮显示的链接名称 |
| `url` | 链接目标地址 |
| `icon` | Font Awesome 图标类名 |
| `color` | 按钮主题色 |
| `enabled` | 是否显示该链接 |
| `antiCrawler` | 邮箱链接专用，开启后地址会被 base64 编码防抓取 |

### ４.１６ 赞赏支持配置

```javascript
donation: {
    enabled: true,
    title: {
        text: '赞助支持',
        icon: 'fa-solid fa-mug-hot'
    },
    message: '如果我的内容对你有帮助，欢迎请我喝杯咖啡～',
    methods: [
        { name: '微信支付', key: 'wechat', icon: 'fa-brands fa-weixin', qrImage: 'images/wechat.png', enabled: true },
        { name: '支付宝', key: 'alipay', icon: 'fa-brands fa-alipay', qrImage: 'images/alipay.png', enabled: true },
        { name: '爱发电', key: 'afdian', icon: 'fa-solid fa-heart', url: 'https://ifdian.net/a/yourname', enabled: true }
    ]
}
```

### ４.１７ 页脚配置

```javascript
footer: {
    copyright: {
        year: '2018-2026',
        name: '你的名字',
        url: 'https://yourblog.com/'
    },
    icp: {
        enabled: true,
        number: '京ICP备XXXXXXXX号'
    }
}
```

### ４.１８ 安全公告配置

页面顶部公告栏，用于防诈骗声明等重要提示：

```javascript
notice: {
    enabled: true,
    type: 'warning',               // warning | info | success
    icon: 'fa-solid fa-shield-halved',
    text: '郑重声明：本人不会主动联系任何人要求转账或提供账号密码，谨防诈骗。'
}
```

### ４.１９ 统计代码配置

支持多种网站统计工具：

```javascript
analytics: {
    googleAnalytics: {
        enabled: true,
        id: 'G-XXXXXXXXXX'
    },
    microsoftClarity: {
        enabled: true,
        id: 'xxxxxxxxxxxx'
    },
    umami: '<script defer src="https://umami.example.com/script.js" data-website-id="xxx"></script>',
    customScripts: [
        '<script>console.log("custom analytics")</script>'
    ]
}
```

---

## 五、项目结构

```
MoeHome/
├── admin/                          # PHP 后台管理（可选）
│   ├── api/                        # 后台 API 端点
│   │   ├── cache.php              # 缓存管理接口
│   │   ├── config.php             # 后台配置接口
│   │   ├── database.php           # 数据库管理接口
│   │   ├── posts.php              # 博客文章管理接口
│   │   └── security.php           # 安全配置接口
│   ├── assets/                    # 后台静态资源
│   │   ├── css/admin.css          # 后台样式
│   │   └── js/admin.js            # 后台脚本
│   ├── partials/                  # 后台模板片段
│   ├── index.php                  # 后台主页
│   ├── login.php                  # 登录页面
│   ├── posts.php                  # 文章管理页面
│   ├── settings.php               # 设置页面
│   └── install.php                # 安装向导
├── api/                           # 前端 API 端点
│   ├── config.example.php         # 配置文件模板
│   └── posts.php                  # 博客文章 API
├── posts/                         # 博客页面
│   └── index.php                  # 独立博客页面
├── src/                           # 源代码
│   ├── app.js                     # 页面交互逻辑
│   ├── config.js                  # 站点配置文件
│   ├── style.css                  # 样式文件
│   ├── theme-utils.js             # 主题工具函数
│   ├── theme-data.js              # 主题配色数据
│   ├── moments.js                 # Memos 动态模块
│   ├── guestbook.js               # 留言板模块
│   ├── media-manager.js           # 媒体管理器
│   ├── comments-standalone.js     # 独立评论模块
│   ├── config.js                  # 运行时配置
│   ├── theme-data.js              # 主题运行时数据
│   ├── theme-utils.js            # 主题运行时工具
│   ├── app.js                     # 主应用脚本
│   ├── media-manager.js          # 媒体管理脚本
│   ├── moments.js                # 动态脚本
│   ├── guestbook.js              # 留言板脚本
│   ├── comments-standalone.js    # 独立评论脚本
│   └── images/                    # 图片资源
├── scripts/                       # 构建脚本
│   ├── build.js                   # 主构建脚本
│   ├── minify.js                  # 压缩优化模块
│   ├── rss-parser.js              # RSS 解析器
│   ├── github-fetcher.js          # GitHub 数据获取
│   └── contribution-fetcher.js    # 贡献数据获取
├── templates/                      # HTML 模板
│   ├── index.template.html        # 首页模板
│   ├── moments.template.html      # 动态页面模板
│   ├── guestbook.template.html    # 留言板模板
│   ├── 404.template.html          # 404 页面模板
│   └── partials/                  # 模板片段
│       ├── navbar.html            # 导航栏
│       └── footer.html            # 页脚
├── dist/                          # 构建输出（部署用）
├── package.json
└── README.md
```

---

## 六、部署指南

### ６.１ 静态部署

适用于纯静态页面展示，无需 PHP 后端：

#### 构建命令

```bash
npm run build
# 构建产物输出到 dist/ 目录
```

#### 部署平台

将 `dist/` 目录内容上传至任意静态托管服务：

| 平台 | 部署方式 |
|------|----------|
| GitHub Pages | 推送代码到 `gh-pages` 分支或设置构建 |
| Vercel | 连接 Git 仓库，框架预设选择「其他」 |
| Netlify | 拖拽 `dist/` 目录或连接 Git 仓库 |
| Cloudflare Pages | 连接 Git 仓库，构建设置指定输出目录 |
| 阿里云 OSS | 使用 ossutil 或控制台上传 |
| 腾讯云 COS | 使用 coscmd 或控制台上传 |
| Nginx 服务器 | SCP 上传并配置站点根目录 |

#### 伪静态配置

页面使用 History API 路由（`/posts`、`/moments` 等），需要服务器配置伪静态规则。

**Nginx 配置示例**：

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/moehome/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # 可选：配置静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

**Apache 配置示例**（`.htaccess`）：

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

### ６.２ 虚拟主机部署

适用于共享虚拟主机环境（如 cPanel、Plesk 等）：

1. 将 `dist/` 目录内容上传到网站的 `public_html` 或 `www` 目录；
2. 将 `admin/` 目录上传到网站根目录（如果需要后台管理）；
3. 将 `api/` 目录上传到网站根目录（如果需要博客 API）；
4. 确保服务器支持 URL 重写功能（联系主机商开启）；
5. 参考「PHP 后端安装」章节配置数据库和后台密码。

详细说明请参阅 `VIRTUAL_HOSTING_README.md`。

### ６.３ Docker 部署

适用于 Docker 容器化部署场景：

```dockerfile
# Dockerfile 示例
FROM nginx:alpine
COPY dist/ /usr/share/nginx/html/
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

```bash
# 构建并运行
docker build -t moehome .
docker run -d -p 8080:80 --name moehome moehome
```

---

## 七、环境变量

构建时支持以下环境变量控制压缩行为：

```bash
# 启用或禁用压缩（默认全部启用）
MINIFY=true npm run build

# 单独控制各类资源压缩
MINIFY_JS=true npm run build
MINIFY_CSS=true npm run build
MINIFY_HTML=true npm run build

# 图片压缩质量（1-100，数值越小文件越小但质量越低）
IMAGE_QUALITY=80 npm run build

# 禁用图片压缩
COMPRESS_IMAGES=false npm run build
```

---

## 八、技术栈

### 前端技术

- **HTML5 + CSS3 + Vanilla JavaScript**：零框架依赖，原生性能优异；
- **Font Awesome 6**：图标库支持，丰富的图标资源；
- **CSS 变量**：主题配色系统的基础，灵活的样式定制能力。

### 字体策略

采用「西文优先加系统回退」策略，确保英文渲染精致的同时兼顾中文可读性：

| 字体用途 | 字体栈 |
|----------|--------|
| 正文界面 | Avenir Next → SF Pro Text → PingFang SC → Microsoft YaHei → 系统回退 |
| 终端代码 | JetBrains Mono（Google Fonts）→ SF Mono → Cascadia Code → 系统回退 |
| 大标题名言 | Iowan Old Style → Palatino Linotype → Noto Serif SC → Georgia |

### 开发依赖

仅在构建时使用，不影响最终产物大小：

| 依赖 | 用途 |
|------|------|
| terser | JavaScript 压缩与混淆 |
| lightningcss | CSS 压缩与优化 |
| html-minifier-terser | HTML 压缩与优化 |
| sharp | WebP/PNG/JPEG 图片压缩 |

---

## 九、常见问题

### 问：博客功能是否必须安装 PHP 后端？

答：首页文章展示（RSS 模式）和独立博客页面需要 PHP 后端支持。如果只使用 GitHub 展示和 Memos 动态，可以不需要 PHP。

### 问：如何获取音乐播放器的歌单 ID？

答：以网易云音乐为例，打开目标歌单页面，浏览器地址栏中的 URL 通常为 `https://music.163.com/#/playlist?id=10046455237`，其中 `10046455237` 即为歌单 ID。

### 问：留言板推荐使用 Waline 还是 Artalk？

答：两者都是优秀的评论系统。Waline 基于 Vercel 免费部署，适合不想管理服务器的用户；Artalk 可自建后端，数据完全自主可控。

### 问：如何自定义主题配色？

答：修改 `src/theme-data.js` 文件中的配色方案定义，或通过页面的主题切换菜单实时预览效果。

### 问：构建失败如何排查？

答：首先确保 Node.js 版本在 18 以上；其次检查 `src/config.js` 是否有语法错误；最后查看终端输出的错误信息定位问题。

### 问：如何升级到新版本？

答：如果通过 Git 克隆，可以执行 `git pull origin main` 获取更新，然后重新执行 `npm install` 和 `npm run build`。建议在升级前备份配置文件。

---

## 十、开源协议

MIT License — 可自由使用、修改和分发。

本项目使用了以下优秀开源项目：

- [Memos](https://github.com/usememos/memos) — MIT License
- [Waline](https://github.com/walinejs/waline) — MIT License
- [Artalk](https://github.com/ArtalkJS/Artalk) — MIT License
- [JetBrains Mono](https://github.com/JetBrains/JetBrainsMono) — OFL-1.1 License

---

如果这个项目对你有帮助，欢迎 Star ⭐️ 支持一下！
