# MoeHome 虚拟主机版 - 部署指南 v2.0

专为普通虚拟主机（如 cPanel、Plesk 面板）优化的 PHP 版本，包含可视化后台管理系统。

## 环境要求

- **PHP**: 7.4+ (推荐 PHP 8.0+)
- **MySQL**: 5.7+
- **PDO 扩展**: 必须
- **JSON 扩展**: 必须
- **mod_rewrite**: 推荐启用

## 快速部署

### 1. 上传文件

通过 FTP/SFTP/cPanel 文件管理器上传所有文件到网站根目录：

```
/
├── index.php              # 首页
├── moments.php           # 动态页面
├── guestbook.php         # 留言板页面
├── 404.php / 403.php / 500.php  # 错误页面
├── style.css             # 样式文件
├── app.js / theme-utils.js / ... # JS 脚本
├── images/               # 图片资源
├── api/                  # API 代理
│   ├── config.example.php
│   ├── config.php        # 你的配置（安装后生成）
│   ├── rss.php
│   ├── github.php
│   ├── memos.php
│   └── cache/
├── admin/                # 后台管理
│   ├── install.php       # 安装向导
│   ├── login.php        # 登录页面
│   ├── index.php        # 仪表盘
│   ├── settings.php     # 站点设置
│   ├── modules.php      # 模块管理
│   ├── theme.php        # 主题设置
│   ├── password.php      # 修改密码
│   ├── api/             # 后台 API
│   ├── assets/          # 后台资源
│   └── partials/        # 模板组件
└── .htaccess            # Apache 配置
```

### 2. 运行安装向导

访问 `https://yourdomain.com/admin/install.php` 开始安装：

```
步骤 1: 数据库配置
- 输入 MySQL 数据库信息
- 系统会自动创建数据表

步骤 2: 创建管理员账户
- 设置后台登录用户名
- 设置后台登录密码（至少6位）

步骤 3: 安装完成
- 自动跳转到登录页面
- 使用刚才创建的管理员账户登录
```

### 3. 访问后台

- 后台地址: `https://yourdomain.com/admin/`
- 默认管理员: `admin` / `admin123` (首次安装后)
- **首次登录后请立即修改密码！**

## 后台功能

### 仪表盘
- 欢迎信息
- 模块状态概览
- 快速操作入口
- 系统信息

### 站点设置
- 站点基本信息（名称、URL、标语）
- 个人资料（头像、显示名称）
- SEO 设置（标题、描述、关键词）
- 页脚设置（版权信息、ICP 备案）

### 模块管理
| 模块 | 说明 |
|------|------|
| RSS 聚合 | 显示博客最新文章 |
| GitHub 项目 | 展示开源仓库 |
| Memos 动态 | 碎片化内容 |
| 留言板 | Waline/Artalk 评论 |
| 音乐播放器 | 背景音乐 |
| 赞赏支持 | 二维码收款 |

### 主题设置
- 浅色/深色/跟随系统模式
- 多种配色方案选择
- 实时预览效果

### 其他功能
- 清理缓存
- 修改密码
- 登录日志记录

## 虚拟主机兼容说明

### 支持的功能

| 功能 | 状态 | 说明 |
|------|------|------|
| 静态页面渲染 | ✅ | PHP 模板引擎 |
| 可视化后台 | ✅ | MySQL 数据库 |
| RSS 聚合 | ✅ | 通过 API 代理 |
| GitHub 项目 | ✅ | 通过 API 代理 |
| GitHub 贡献图 | ✅ | 通过 API 代理 |
| Memos 动态 | ✅ | 通过 API 代理 |
| 留言板 | ✅ | 支持 Waline/Artalk |
| 主题切换 | ✅ | 前端 JS 实现 |
| 音乐播放器 | ✅ | Meting API |
| 邮件反爬虫 | ✅ | Base64 编码 |

### API 代理说明

| API | 端点 | 说明 |
|-----|------|------|
| RSS | `/api/rss.php?url=编码的URL` | 缓存 1 小时 |
| GitHub | `/api/github.php?type=repos&user=用户名` | 缓存 30 分钟 |
| GitHub | `/api/github.php?type=contributions&user=用户名` | 缓存 30 分钟 |
| Memos | `/api/memos.php` | 缓存 5 分钟 |

## 安全设置

### 首次安装后

1. **修改默认密码**
   - 登录后台 → 修改密码
   - 使用强密码（字母+数字+特殊字符）

2. **检查目录权限**
   ```bash
   chmod 755 api/cache
   chmod 644 admin/api/config.php
   ```

3. **删除安装文件（可选）**
   ```bash
   rm admin/install.php
   ```

### 可选：限制 API 访问

在 `.htaccess` 中添加 IP 白名单：

```apache
<FilesMatch "\.(json)$">
    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
    Allow from ::1
</FilesMatch>
```

## 性能优化

### 启用 GZIP 压缩

`.htaccess` 已配置 GZIP 压缩，如不生效，添加：

```php
// 在 index.php 顶部添加
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && function_exists('ob_gzhandler')) {
    ob_start('ob_gzhandler');
}
```

### 静态资源缓存

`.htaccess` 已配置静态资源缓存：
- 图片: 1 个月
- CSS/JS: 1 周
- 字体: 1 年

### 缓存清理

- **后台清理**: 仪表盘 → 清理缓存
- **手动清理**: 删除 `api/cache/` 目录下的文件

## 常见问题

### 1. 页面空白或显示错误

检查 PHP 版本：
```php
<?php phpinfo(); ?>
```

确保 PHP 版本 >= 7.4

### 2. 数据库连接失败

- 确认 MySQL 服务正在运行
- 检查 `api/config.php` 中的数据库配置
- 确认数据库用户有权限访问该数据库

### 3. 安装向导无法访问

检查 `admin/` 目录是否正确上传

### 4. 样式/图片加载失败

检查文件路径是否正确

### 5. 缓存目录不可写

```bash
chmod 755 api/cache
```

## 数据库表结构

系统会自动创建以下表：

```sql
-- 用户表
moehome_users
├── id (INT, 主键)
├── username (VARCHAR, 唯一)
├── password (VARCHAR, 加密)
├── email (VARCHAR)
├── role (ENUM: admin/editor)
├── created_at (DATETIME)
└── last_login (DATETIME)

-- 配置表
moehome_config
├── id (INT, 主键)
├── category (VARCHAR, 分类)
├── key (VARCHAR, 配置键)
├── value (TEXT, 配置值)
├── type (ENUM: string/number/boolean/array/json)
└── updated_at (DATETIME)

-- 登录日志表
moehome_login_attempts
├── id (INT, 主键)
├── ip (VARCHAR, IP地址)
├── attempts (INT, 尝试次数)
├── locked_until (DATETIME, 锁定截止时间)
└── last_attempt (DATETIME)
```

## 获取帮助

- 原项目地址：https://github.com/moewah/MoeHome
- 提交 Issue：https://github.com/moewah/MoeHome/issues
