# MoeHome 虚拟主机版

**一款专为技术爱好者打造的简约、酷炫的个人主页模版**

终端风格界面 · 零运行时依赖 · 虚拟主机完美兼容

---

## 目录

1. [项目概述](#项目概述)
2. [功能特性](#功能特性)
3. [环境要求](#环境要求)
4. [安装部署](#安装部署)
5. [配置指南](#配置指南)
6. [后台管理](#后台管理)
7. [API接口](#api接口)
8. [安全说明](#安全说明)
9. [常见问题](#常见问题)

---

## 项目概述

MoeHome 是一款专为技术爱好者设计的个人主页模版，采用终端风格界面设计，支持多种第三方服务集成，无需 Node.js 环境即可在虚拟主机上完美运行。

### 设计理念

- **极简配置**：修改一个配置文件即可完成全部个性化设置
- **终端美学**：模拟真实终端界面，打造技术宅风格
- **零依赖**：纯 PHP + HTML + CSS + JavaScript，无运行时编译
- **开箱即用**：内置 RSS 聚合、图库、花架、哔哩哔哩、Memos 动态

### 项目结构

```
/workspace/
├── index.php              # 首页 - 终端风格个人主页
├── moments.php           # 动态页面 - Memos 动态流
├── guestbook.php         # 留言页面 - Waline/Artalk 评论
├── style.css             # 主样式文件
├── app.js                # 主脚本文件
├── theme-utils.js        # 主题工具函数
├── theme-data.js        # 主题配色数据
├── media-manager.js      # 媒体资源管理
├── moments.js            # 动态模块脚本
├── guestbook.js          # 留言板脚本
├── comments-standalone.js # 评论组件
├── api/                  # API 代理目录
│   ├── config.example.php  # 配置文件示例
│   ├── config.php          # 用户配置文件（需创建）
│   ├── rss.php            # RSS 代理 API
│   ├── gallery.php        # 图库代理 API
│   ├── books.php          # 书架代理 API
│   ├── bilibili.php       # 哔哩哔哩代理 API
│   ├── memos.php          # Memos 代理 API
│   └── cache/             # API 缓存目录
├── admin/                 # 后台管理目录
│   ├── login.php          # 登录页面
│   ├── reset-password.php # 密码重置页面
│   ├── index.php          # 仪表盘
│   ├── settings.php       # 站点设置
│   ├── modules.php        # 模块管理
│   ├── theme.php          # 主题设置
│   ├── password.php       # 修改密码
│   ├── install.php        # 安装向导
│   └── api/               # 后台 API
│       ├── database.php   # 数据库操作
│       ├── config.php     # 配置管理
│       ├── cache.php      # 缓存管理
│       └── security.php    # 安全中间件
└── src/                   # 源码备份目录
```

---

## 功能特性

### 核心模块

| 模块 | 说明 | 状态 |
|------|------|------|
| 终端首页 | 模拟命令行交互界面 | ✅ |
| 个人信息 | 头像、名称、标语展示 | ✅ |
| 打字机动画 | 终端文字逐字显示 | ✅ |
| 语录轮播 | `./wisdom.sh` 动态语录 | ✅ |
| RSS 聚合 | 博客文章列表展示 | ✅ |
| 图库展示 | 本地图片/花瓣网采集 | ✅ |
| 书架展示 | 豆瓣读书记录同步 | ✅ |
| 哔哩哔哩 | B站视频作品展示 | ✅ |
| 主题切换 | 浅色/深色/跟随系统 | ✅ |
| 动态页面 | Memos 碎片化分享 | ✅ |
| 留言板 | Waline/Artalk 评论 | ✅ |
| 链接导航 | 社交链接快速访问 | ✅ |
| 赞赏支持 | 扫码打赏功能 | ✅ |
| 后台管理 | 可视化配置管理 | ✅ |
| 邮箱找回 | SMTP 邮件发送 | ✅ |

### 配色方案

| 模式 | 配色方案 | 主色 |
|------|----------|------|
| 浅色 | Coral Orange | `#ff6b4a` |
| 浅色 | Berry Purple | `#a855f7` |
| 浅色 | Ocean Blue | `#3b82f6` |
| 浅色 | Forest Green | `#22c55e` |
| 浅色 | Sunset Pink | `#ec4899` |
| 深色 | Cyber Green | `#00ff9f` |
| 深色 | Neon Purple | `#a855f7` |
| 深色 | Electric Blue | `#3b82f6` |
| 深色 | Sunset Orange | `#f97316` |
| 深色 | Mint Green | `#34d399` |

---

## 环境要求

### 服务器环境

| 项目 | 最低要求 | 推荐配置 |
|------|----------|----------|
| PHP | 7.4+ | 8.0+ |
| MySQL | 5.7+ | 8.0+ |
| Apache | 2.4+ | 2.4+ |
| mod_rewrite | 必须 | 必须 |
| PDO MySQL | 必须 | 必须 |

### 虚拟主机兼容性

本项目专为虚拟主机设计，已在以下环境测试通过：

- ✅ 阿里云虚拟主机
- ✅ 腾讯云虚拟主机
- ✅ Bluehost
- ✅ HostGator
- ✅ SiteGround

### 必需扩展

```bash
PHP Extensions:
├── pdo_mysql      # 数据库连接
├── json           # JSON 处理
├── mbstring       # 中文处理
├── curl           # HTTP 请求
└── openssl       # HTTPS/加密
```

---

## 安装部署

### 方式一：自动安装向导

1. **上传源码**
   ```bash
   # 使用 FTP 或文件管理器上传所有文件到 web 目录
   /public_html/
   ├── index.php
   ├── moments.php
   ├── guestbook.php
   └── ...
   ```

2. **创建数据库**
   - 登录虚拟主机控制面板 (cPanel/Plesk)
   - 创建 MySQL 数据库
   - 创建数据库用户并授权

3. **运行安装向导**
   ```
   访问: http://yourdomain.com/admin/install.php
   ```

4. **按照向导步骤操作**
   - Step 1: 数据库配置
   - Step 2: 管理员账户
   - Step 3: 完成安装

### 方式二：手动配置

1. **创建配置文件**
   ```bash
   cp api/config.example.php api/config.php
   ```

2. **编辑配置文件**
   ```php
   # 编辑 api/config.php
   $config['site'] = [
       'name' => 'YourName',
       'url' => 'https://yourdomain.com',
   ];
   ```

3. **导入数据库表**
   ```sql
   -- 使用 phpMyAdmin 导入以下 SQL
   -- 参考 admin/install.php 中的表结构
   ```

4. **手动创建管理员**
   ```php
   // 在 database.php 环境中执行
   $password = password_hash('your_password', PASSWORD_DEFAULT);
   // INSERT INTO moehome_users VALUES (NULL, 'admin', '$password', 'admin@email.com', 'admin', NOW(), NULL);
   ```

### 目录权限

```bash
# 必须可写
chmod 755 api/cache/
chmod 755 logs/

# 可选（用于缓存清理）
chmod 755 admin/api/cache/
```

---

## 配置指南

### 站点基础配置

```php
$config['site'] = [
    'name' => 'YourName',                    // 网站名称（显示在导航栏）
    'tagline' => '技术博主 / 开源爱好者',     // 网站标语
    'url' => 'https://example.com',          // 网站完整 URL
    'ogImage' => 'https://example.com/images/avatar.webp', // 社交分享图
];
```

### 个人资料配置

```php
$config['profile'] = [
    'name' => 'YourName',                    // 显示名称
    'tagline' => [
        'prefix' => '🐾',                     // 标语前缀（图标或文字）
        'highlight' => '欢迎来到我的主页！'   // 高亮文字
    ],
    'avatar' => 'images/avatar.webp'        // 头像路径
];
```

### 终端内容配置

```php
// 身份标签（whoami 输出）
$config['identity'] = [
    '开源爱好者',
    '前端开发者',
    'AI探索者'
];

// 兴趣爱好
$config['interests'] = [
    'Docker & 容器技术',
    'Proxmox & 虚拟化',
    'AI工具 & ComfyUI'
];

// 设备列表
$config['gear'] = [
    'Mac mini M4 Pro 48G',
    'Dell S2725QS',
    'Synology DS920+'
];
```

### RSS 配置

```php
$config['rss'] = [
    'enabled' => true,
    'url' => 'https://yourblog.com/rss.xml',  // RSS 订阅地址
    'count' => 4,                              // 显示数量
    'openInNewTab' => true,                   // 新标签打开
    'title' => [
        'text' => '近期更新',
        'icon' => 'fa-solid fa-newspaper'
    ],
    'display' => [
        'showDate' => true,                   // 显示日期
        'showDescription' => true,             // 显示描述
        'maxDescriptionLength' => 100          // 描述最大长度
    ]
];
```

### GitHub 项目配置

```php
$config['projects'] = [
    'enabled' => true,
    'title' => [
        'text' => '我的项目',
        'icon' => 'fa-solid fa-folder-open'
    ],
    'githubUser' => 'https://github.com/yourusername',
    'count' => 5,                             // 显示数量
    'exclude' => ['.github']                  // 排除的仓库
];

$config['contribution'] = [
    'enabled' => true,
    'useRealData' => true,                   // 使用真实数据
    'githubUser' => ''                        // 为空则使用 projects 的用户名
];
```

### Memos 动态配置

```php
$config['moments'] = [
    'enabled' => true,
    'memosUrl' => 'https://your-memos.com/',  // Memos 实例地址
    'count' => 10,                             // 加载数量
    'tags' => ['AI技巧', '摄影日常'],           // 筛选标签（留空显示全部）
    'showSkeleton' => true                     // 显示骨架屏
];
```

### 留言板配置

```php
$config['guestbook'] = [
    'enabled' => true,
    'provider' => 'waline',                    // 评论系统：waline / artalk
    'server' => 'https://your-waline.vercel.app', // 服务地址
    'site' => 'MoeHome',
    'placeholder' => '欢迎留下你的信号...',
    'limits' => [
        'comments' => ['newest' => 30, 'hot' => 30],
        'barrage' => ['pinned' => 5, 'hot' => 10, 'latest' => 5]
    ]
];
```

### 链接导航配置

```php
$config['linksConfig'] = [
    'enabled' => true,
    'title' => [
        'text' => '链接导航',
        'icon' => 'fa-solid fa-link'
    ]
];

$config['links'] = [
    [
        'name' => 'Blog',                      // 链接名称
        'description' => '技术文章 & 教程',    // 描述
        'url' => 'https://yourblog.com',       // 链接地址
        'icon' => 'fa-solid fa-pen-nib',       // Font Awesome 图标
        'brand' => 'blog',                     // 品牌标识
        'external' => true,                    // 是否外链
        'color' => '#00ff9f',                 // 自定义颜色
        'enabled' => true
    ],
    [
        'name' => 'Email',
        'description' => '联系 & 合作',
        'url' => 'mailto:admin@example.com',
        'icon' => 'fa-solid fa-envelope',
        'brand' => 'email',
        'antiCrawler' => true,                // 防爬虫（邮箱地址加密）
        'enabled' => true
    ]
];
```

### 音乐播放器配置

```php
$config['music'] = [
    'enabled' => true,
    'volume' => 0.5,                          // 音量 0-1
    'autoplay' => false,
    'playMode' => 'list',                    // 播放模式：list / random
    'mode' => 'meting',                      // 模式：meting / local

    // Meting 音乐源配置
    'meting' => [
        'server' => 'netease',                // netease / tencent / kugou / xiami / baidu
        'type' => 'playlist',                 // song / album / artist / playlist / search
        'id' => '10046455237',               // 对应 ID
        'apis' => [
            'https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r'
        ]
    ]
];
```

### SMTP 邮箱配置（后台设置）

```
路径：后台管理 → 设置 → 邮箱设置

配置项：
├── SMTP 服务器    smtp.example.com
├── SMTP 端口     465 (SSL) / 587 (TLS)
├── 加密方式      SSL / TLS / 无
├── 用户名        your@email.com
├── 密码          授权码（不是登录密码）
├── 发件人邮箱    noreply@example.com
└── 发件人名称    MoeHome
```

---

## 后台管理

### 访问地址

```
http://yourdomain.com/admin/
```

### 功能模块

| 模块 | 说明 |
|------|------|
| 仪表盘 | 系统状态概览 |
| 站点设置 | 基础信息、SEO、页脚设置 |
| 模块管理 | 开关控制、模块配置 |
| 主题设置 | 配色方案、主题模式 |
| 账户安全 | 修改密码、邮箱设置 |
| 缓存管理 | API 缓存清理 |

### 找回密码

1. 访问登录页面
2. 点击「忘记密码」
3. 输入注册邮箱
4. 点击「发送重置链接」
5. 查收邮件，点击链接
6. 设置新密码

**前提条件**：已在「账户安全」中绑定邮箱，且在「站点设置」中配置 SMTP

---

## API接口

### RSS 代理

```
GET /api/rss.php?url=<encoded_url>&count=<number>
```

**参数**：
- `url` (必需): RSS 订阅地址（URL 编码）
- `count` (可选): 返回数量，默认 10

**响应**：
```json
{
  "items": [
    {
      "title": "文章标题",
      "link": "https://...",
      "description": "文章描述...",
      "pubDate": "2024-01-01",
      "author": "作者"
    }
  ],
  "fetched": "2024-01-01T00:00:00+08:00",
  "source": "blog.example.com"
}
```

### GitHub 项目

```
GET /api/github.php?type=repos&user=<username>&count=<number>&exclude=<patterns>
```

**参数**：
- `type` (必需): `repos` 或 `contributions`
- `user` (必需): GitHub 用户名
- `count` (可选): 返回数量
- `exclude` (可选): 排除的仓库（逗号分隔）

**响应**：
```json
{
  "repositories": [
    {
      "name": "repo-name",
      "full_name": "user/repo-name",
      "description": "项目描述",
      "html_url": "https://github.com/...",
      "stargazers_count": 123,
      "forks_count": 45,
      "language": "JavaScript",
      "topics": ["topic1", "topic2"]
    }
  ],
  "total": 10
}
```

### Memos 动态

```
GET /api/memos.php?count=<number>&tag=<tag>
```

**参数**：
- `count` (可选): 返回数量
- `tag` (可选): 标签筛选

---

## 安全说明

### 内置安全措施

| 措施 | 说明 |
|------|------|
| CSRF 防护 | 所有表单使用一次性令牌 |
| SQL 预处理 | PDO 参数化查询 |
| XSS 过滤 | 输出强制 HTML 编码 |
| 密码哈希 | bcrypt 加密 |
| Session 安全 | HttpOnly + SameSite |
| 登录限流 | 5 次失败锁定 15 分钟 |
| 密码重置 | SHA256 令牌 + IP 绑定 |
| 安全日志 | 记录安全事件 |
| API 限流 | 防止滥用 |

### 建议的安全配置

1. **启用 HTTPS**
   ```apache
   # .htaccess
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

2. **设置强密码**
   - 至少 8 位
   - 包含大小写字母
   - 包含数字和特殊字符

3. **定期更新**
   - 关注安全公告
   - 及时更新密码

4. **备份数据**
   - 定期备份数据库
   - 备份配置文件

---

## 常见问题

### Q: 页面显示空白？

**检查项**：
1. PHP 版本是否 >= 7.4
2. 是否启用 mod_rewrite
3. config.php 是否存在语法错误
4. 浏览器控制台是否有 JS 错误

### Q: RSS/GitHub 无法加载？

**检查项**：
1. 虚拟主机是否允许外部请求 (`allow_url_fopen`)
2. 缓存目录是否可写
3. API 地址是否正确

### Q: 留言板无法评论？

**检查项**：
1. Waline/Artalk 服务是否正常
2. 服务地址是否正确配置
3. 浏览器是否阻止第三方脚本

### Q: 找回密码邮件发送失败？

**检查项**：
1. SMTP 配置是否正确
2. 邮箱是否开启 SMTP
3. 密码是否使用授权码（非登录密码）
4. 防火墙是否阻止 SMTP 端口

### Q: 如何获取 GitHub Token？

1. 访问 https://github.com/settings/tokens
2. 点击 Generate new token
3. 选择 `read:user` 权限
4. 复制 Token 到配置

---

## 更新日志

### v2.2 (当前版本)
- ✅ 添加邮箱找回密码功能
- ✅ 添加密码重置 IP 绑定
- ✅ 强化 Session 安全
- ✅ 添加 API 速率限制
- ✅ 添加安全日志记录
- ✅ 添加 CSP 内容安全策略

### v2.1
- ✅ 可视化后台管理系统
- ✅ 主题配色切换
- ✅ 模块化管理
- ✅ 数据库配置存储

### v2.0
- ✅ 终端风格界面
- ✅ RSS/GitHub/Memos 集成
- ✅ Waline/Artalk 评论
- ✅ 虚拟主机兼容

---

## 致谢

- [Meting](https://github.com/metowolf/MetingJS) - 音乐解析
- [Waline](https://github.com/walinejs/waline) - 评论系统
- [Artalk](https://github.com/ArtalkJS/Artalk) - 评论系统
- [Font Awesome](https://fontawesome.com/) - 图标库
- [JetBrains Mono](https://www.jetbrains.com/lp/mono/) - 编程字体

---

## 许可证

本项目采用 [MIT License](https://opensource.org/licenses/MIT) 开源免费使用。

---

**文档版本**: v2.2
**最后更新**: 2026-05-09
