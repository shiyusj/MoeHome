# MoeHome 虚拟主机版 - 部署指南

专为普通虚拟主机（如 cPanel、Plesk 面板）优化的 PHP 版本，无需 Node.js 构建环境。

## 环境要求

- PHP 5.6+ (推荐 PHP 7.4+)
- JSON 扩展
- cURL 或 allow_url_fopen
- Apache + mod_rewrite (推荐)

## 快速部署

### 1. 上传文件

通过 FTP/SFTP/cPanel 文件管理器上传以下文件到网站根目录：

```
/
├── index.php          # 首页
├── moments.php        # 动态页面
├── guestbook.php     # 留言板页面
├── style.css         # 样式文件
├── app.js            # 主脚本
├── theme-utils.js    # 主题工具
├── theme-data.js     # 主题数据
├── moments.js        # 动态模块
├── guestbook.js      # 留言板模块
├── media-manager.js  # 媒体管理
├── comments-standalone.js  # 评论组件
├── images/           # 图片资源
├── music/            # 音乐文件（可选）
├── api/              # API 代理目录
│   ├── config.example.php  # 配置文件
│   ├── rss.php       # RSS 代理
│   ├── github.php    # GitHub API 代理
│   ├── memos.php     # Memos API 代理
│   └── cache/        # 缓存目录（自动创建）
└── .htaccess         # Apache 配置
```

### 2. 配置站点

编辑 `api/config.example.php`，修改为你的配置：

```bash
# 重命名配置文件
mv api/config.example.php api/config.php
```

主要配置项：

```php
// 站点基础信息
$config['site'] = [
    'name' => 'YourName',
    'url' => 'https://yourdomain.com',
];

// GitHub 配置
$config['projects'] = [
    'githubUser' => 'https://github.com/yourusername',
];

// RSS 订阅
$config['rss'] = [
    'enabled' => true,
    'url' => 'https://yourblog.com/rss.xml',
];

// Memos 动态
$config['moments'] = [
    'enabled' => true,
    'memosUrl' => 'https://your-memos.com/',
];

// 留言板
$config['guestbook'] = [
    'enabled' => true,
    'provider' => 'waline',  // 或 'artalk'
    'server' => 'https://your-waline.vercel.app',
];
```

### 3. 设置权限

确保缓存目录可写：

```bash
chmod 755 api/cache
# 或
chmod 777 api/cache
```

### 4. 配置 URL 重写

如果虚拟主机不支持 `.htaccess`，需要在 cPanel 或管理面板中启用 URL 重写。

启用方法：
1. 登录 cPanel
2. 找到"高级" → "htaccess" 或 "URL 重写"
3. 上传 `.htaccess` 文件

## 虚拟主机兼容说明

### 支持的功能

| 功能 | 状态 | 说明 |
|------|------|------|
| 静态页面渲染 | ✅ | PHP 模板引擎 |
| RSS 聚合 | ✅ | 通过 API 代理 |
| GitHub 项目 | ✅ | 通过 API 代理 |
| GitHub 贡献图 | ✅ | 通过 API 代理 |
| Memos 动态 | ✅ | 通过 API 代理 |
| 留言板 | ✅ | 支持 Waline/Artalk |
| 主题切换 | ✅ | 前端 JS 实现 |
| 音乐播放器 | ✅ | Meting API |
| 邮件反爬虫 | ✅ | Base64 编码 |

### API 代理说明

虚拟主机无法直接调用第三方 API（RSS、GitHub、Memos），因此提供 PHP 代理：

| API | 端点 | 说明 |
|-----|------|------|
| RSS | `/api/rss.php?url=编码的URL` | 缓存 1 小时 |
| GitHub | `/api/github.php?type=repos&user=用户名` | 缓存 30 分钟 |
| GitHub | `/api/github.php?type=contributions&user=用户名` | 缓存 30 分钟 |
| Memos | `/api/memos.php` | 缓存 5 分钟 |

### 可选：提高 API 限制

GitHub 未认证请求限制为 60 次/小时。获取更多配额：

1. 访问 https://github.com/settings/tokens
2. 生成新 Token（无需任何权限）
3. 在 `api/config.php` 中添加：

```php
$config['api']['github_token'] = 'ghp_xxxxxxxxxxxx';
```

## 目录结构

```
your-site/
├── index.php              # 首页 (必须)
├── moments.php            # 动态页 (可选)
├── guestbook.php           # 留言板 (可选)
├── style.css               # 样式 (必须)
├── app.js                  # 主脚本 (必须)
├── theme-utils.js          # 主题工具 (必须)
├── theme-data.js           # 主题数据 (必须)
├── moments.js              # 动态模块 (必须)
├── guestbook.js            # 留言板模块 (必须)
├── media-manager.js        # 媒体管理 (必须)
├── comments-standalone.js  # 评论组件 (必须)
├── images/                 # 图片资源 (必须)
│   ├── avatar.webp
│   └── ...
├── api/                    # API 代理 (必须)
│   ├── config.example.php  # 配置示例
│   ├── config.php         # 你的配置
│   ├── rss.php
│   ├── github.php
│   ├── memos.php
│   └── cache/              # 缓存目录
└── .htaccess               # Apache 配置
```

## 常见问题

### 1. 页面空白或显示错误

检查 PHP 版本：
```php
<?php phpinfo(); ?>
```

确保 PHP 版本 >= 5.6

### 2. API 请求失败

检查虚拟主机设置：
- 确认 allow_url_fopen 开启，或
- 确认 cURL 扩展已安装

### 3. 样式/图片加载失败

检查文件路径是否正确，特别是 `api/config.php` 中的路径配置。

### 4. 缓存目录不可写

```bash
# SSH 方式
chmod 755 api/cache

# FTP 方式
在文件管理器中右键 → 权限 → 755
```

### 5. 页面 500 错误

检查 `.htaccess` 语法或禁用自定义 php.ini 设置。

## 性能优化

### 启用 GZIP 压缩

`.htaccess` 已配置 GZIP 压缩，如不生效，添加：

```php
// 在 api/config.php 顶部添加
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && function_exists('ob_gzhandler')) {
    ob_start('ob_gzhandler');
}
```

### 静态资源缓存

`.htaccess` 已配置静态资源缓存（图片 1 年，CSS/JS 1 周）。

### 缓存清理

自动清理：`api/cache/` 目录下的文件会在过期后自动删除。

手动清理：
```bash
rm -rf api/cache/*
```

## 安全建议

1. **重命名配置文件**
   ```bash
   mv api/config.example.php api/config.php
   ```

2. **限制 API 访问**（可选）
   在 `.htaccess` 中添加 IP 白名单

3. **定期清理缓存**
   ```bash
   # crontab 设置
   0 */6 * * * rm -rf /path/to/api/cache/*
   ```

## 获取帮助

- 原项目地址：https://github.com/moewah/MoeHome
- 提交 Issue：https://github.com/moewah/MoeHome/issues
