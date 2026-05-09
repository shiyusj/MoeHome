# MoeHome 虚拟主机版 - 后台管理系统设计规范

## 概述

为 MoeHome 虚拟主机版本添加可视化后台管理系统，使配置管理更加便捷。

## 设计目标

1. **视觉一致性** - 后台风格与前台主题效果保持一致
2. **简单易用** - 非技术人员也能轻松配置
3. **安全可靠** - MySQL 数据库存储，支持用户认证

## 技术方案

### 架构设计

```
┌─────────────────────────────────────────────────────────────┐
│                     Admin Panel                              │
├─────────────────────────────────────────────────────────────┤
│  /admin/                                                   │
│  ├── index.php          # 后台首页/仪表盘                  │
│  ├── login.php          # 登录页面                          │
│  ├── logout.php         # 登出处理                          │
│  ├── settings.php       # 站点设置                          │
│  ├── profile.php         # 个人资料                          │
│  ├── modules.php        # 模块开关                          │
│  ├── theme.php          # 主题设置                          │
│  ├── api/               # 后台 API                          │
│  │   ├── config.php     # 配置 CRUD                         │
│  │   ├── auth.php       # 认证接口                          │
│  │   └── cache.php      # 缓存管理                          │
│  └── assets/            # 后台静态资源                       │
│      ├── css/           # 后台样式                          │
│      └── js/             # 后台脚本                          │
└─────────────────────────────────────────────────────────────┘
```

### 数据库设计

```sql
-- 用户表
CREATE TABLE `moehome_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100),
  `role` ENUM('admin', 'editor') DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME
);

-- 配置表
CREATE TABLE `moehome_config` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(50) NOT NULL,
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT,
  `type` ENUM('string', 'number', 'boolean', 'array', 'json') DEFAULT 'string',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `category_key` (`category`, `key`)
);

-- 安装时创建默认管理员
INSERT INTO `moehome_users` (`username`, `password`, `role`)
VALUES ('admin', SHA2('admin123', 256), 'admin');
```

### 页面设计

#### 1. 登录页面 `/admin/login.php`
- 终端风格登录框
- 用户名/密码输入
- 登录按钮
- 主题色适配（亮/暗）

#### 2. 仪表盘 `/admin/index.php`
- 欢迎信息
- 快速统计（模块状态）
- 快捷操作入口
- 最近更新

#### 3. 站点设置 `/admin/settings.php`
- 站点名称、URL
- SEO 信息
- 个人资料
- 头像上传

#### 4. 模块管理 `/admin/modules.php`
- RSS 聚合开关/配置
- GitHub 项目开关/配置
- 留言板开关/配置
- Memos 动态开关/配置
- 音乐播放器开关/配置
- 赞赏模块开关/配置

#### 5. 主题设置 `/admin/theme.php`
- 主题模式（浅色/深色/跟随系统）
- 配色方案选择
- 自定义颜色预览

### 安全措施

1. **密码加密** - 使用 `password_hash()` / `password_verify()`
2. **Session 管理** - 登录状态验证
3. **CSRF 防护** - 表单令牌
4. **SQL 注入防护** - 使用预处理语句
5. **XSS 防护** - 输出转义
6. **登录限流** - 5次失败后锁定 15 分钟

### 界面风格

遵循现有主题设计语言：

```css
/* 后台主题变量 */
:root {
  --bg-primary: #ffffff;
  --bg-secondary: #f8f9fa;
  --text-primary: #1a1a2e;
  --accent: #ff6b4a;
  --border: #e5e7eb;
  --success: #10b981;
  --warning: #f59e0b;
  --error: #ef4444;
}

[data-theme="dark"] {
  --bg-primary: #0f0f1a;
  --bg-secondary: #1a1a2e;
  --text-primary: #e5e7eb;
  --accent: #00ff9f;
  --border: #374151;
}
```

### 功能清单

| 模块 | 功能 | 优先级 |
|------|------|--------|
| 登录认证 | 用户名+密码登录、登出 | P0 |
| 仪表盘 | 欢迎页、快速入口 | P0 |
| 站点设置 | 基础信息、SEO配置 | P0 |
| 模块开关 | RSS/GitHub/Memos等开关 | P0 |
| 主题设置 | 主题模式、配色 | P1 |
| 缓存管理 | 一键清理缓存 | P1 |
| 密码修改 | 修改登录密码 | P1 |
| 安装向导 | 首次使用的数据库初始化 | P0 |

## 部署要求

- PHP 7.4+
- MySQL 5.7+
- PDO 扩展
- 虚拟主机需支持 MySQL 连接

## 实施计划

1. 创建数据库表结构
2. 实现用户认证系统
3. 开发管理界面
4. 实现配置 CRUD
5. 主题适配
6. 测试验证
