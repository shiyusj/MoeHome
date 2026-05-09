<?php
/**
 * MoeHome 后台管理 - 安装向导
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();

require_once __DIR__ . '/api/database.php';

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';

        if (empty($dbName) || empty($dbUser)) {
            $message = '请填写数据库名称和用户名';
        } else {
            $_SESSION['install_db'] = [
                'host' => $dbHost,
                'name' => $dbName,
                'user' => $dbUser,
                'pass' => $dbPass
            ];

            try {
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                $_SESSION['install_db']['tested'] = true;
                header('Location: install.php?step=2');
                exit;
            } catch (PDOException $e) {
                $message = '数据库连接失败: ' . $e->getMessage();
            }
        }
    } elseif ($step === 2) {
        $adminUser = trim($_POST['admin_user'] ?? '');
        $adminPass = $_POST['admin_pass'] ?? '';
        $adminEmail = trim($_POST['admin_email'] ?? '');

        if (empty($adminUser) || empty($adminPass)) {
            $message = '请填写用户名和密码';
        } elseif (strlen($adminPass) < 6) {
            $message = '密码长度至少 6 位';
        } else {
            $_SESSION['install_admin'] = [
                'user' => $adminUser,
                'pass' => $adminPass,
                'email' => $adminEmail
            ];

            try {
                $dbConfig = $_SESSION['install_db'];

                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbConfig['host'], $dbConfig['name']);
                $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                $sql = "
                CREATE TABLE IF NOT EXISTS `moehome_users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(50) NOT NULL UNIQUE,
                    `password` VARCHAR(255) NOT NULL,
                    `email` VARCHAR(100),
                    `role` ENUM('admin', 'editor') DEFAULT 'admin',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `last_login` DATETIME,
                    INDEX `idx_username` (`username`),
                    INDEX `idx_email` (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `moehome_config` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `category` VARCHAR(50) NOT NULL,
                    `key` VARCHAR(100) NOT NULL,
                    `value` TEXT,
                    `type` ENUM('string', 'number', 'boolean', 'array', 'json') DEFAULT 'string',
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uk_category_key` (`category`, `key`),
                    INDEX `idx_category` (`category`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `moehome_login_attempts` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `ip` VARCHAR(45) NOT NULL,
                    `username` VARCHAR(50),
                    `attempts` INT DEFAULT 1,
                    `locked_until` DATETIME,
                    `last_attempt` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_ip` (`ip`),
                    INDEX `idx_locked_until` (`locked_until`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `moehome_password_resets` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `email` VARCHAR(100) NOT NULL,
                    `token` VARCHAR(64) NOT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `expires_at` DATETIME NOT NULL,
                    `ip` VARCHAR(45) NOT NULL,
                    INDEX `idx_email` (`email`),
                    INDEX `idx_token` (`token`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `moehome_articles` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(255) NOT NULL,
                    `slug` VARCHAR(255) NOT NULL UNIQUE,
                    `excerpt` TEXT,
                    `content` LONGTEXT NOT NULL,
                    `cover` VARCHAR(500),
                    `tags` VARCHAR(500),
                    `status` ENUM('draft', 'published') DEFAULT 'draft',
                    `is_featured` TINYINT(1) DEFAULT 0,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME,
                    INDEX `idx_status` (`status`),
                    INDEX `idx_is_featured` (`is_featured`),
                    INDEX `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `moehome_projects` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `slug` VARCHAR(255) NOT NULL UNIQUE,
                    `description` TEXT,
                    `cover` VARCHAR(500),
                    `url` VARCHAR(500),
                    `language` VARCHAR(50),
                    `stars` INT DEFAULT 0,
                    `forks` INT DEFAULT 0,
                    `status` ENUM('active', 'maintenance', 'archived') DEFAULT 'active',
                    `sort_order` INT DEFAULT 0,
                    `is_main` TINYINT(1) DEFAULT 0,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME,
                    INDEX `idx_status` (`status`),
                    INDEX `idx_is_main` (`is_main`),
                    INDEX `idx_sort_order` (`sort_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `moehome_moments` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `content` TEXT NOT NULL,
                    `tags` VARCHAR(500),
                    `is_pinned` TINYINT(1) DEFAULT 0,
                    `status` ENUM('draft', 'published') DEFAULT 'published',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME,
                    INDEX `idx_status` (`status`),
                    INDEX `idx_is_pinned` (`is_pinned`),
                    INDEX `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";

                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }

                $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO moehome_users (username, password, email, role) VALUES (?, ?, ?, 'admin')");
                $stmt->execute([$adminUser, $hashedPassword, $adminEmail]);

                $configContent = '<?php
$config[\'database\'] = [
    \'host\' => \'' . addslashes($dbConfig['host']) . '\',
    \'name\' => \'' . addslashes($dbConfig['name']) . '\',
    \'username\' => \'' . addslashes($dbConfig['user']) . '\',
    \'password\' => \'' . addslashes($dbConfig['pass']) . '\'
];
$config[\'site\'] = [
    \'name\' => \'MoeHome\',
    \'tagline\' => \'技术博主 / 开源爱好者\',
    \'url\' => \'' . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : '') . '\',
    \'ogImage\' => \'\'
];
$config[\'profile\'] = [
    \'name\' => \'YourName\',
    \'tagline\' => [\'prefix\' => \'🐾\', \'highlight\' => \'欢迎来到我的主页！\'],
    \'avatar\' => \'images/avatar.webp\'
];
$config[\'theme\'] = [
    \'default\' => \'light\',
    \'defaultScheme\' => [\'light\' => \'coralOrange\', \'dark\' => \'cyberGreen\']
];
$config[\'modules\'] = [];
$config[\'rss\'] = [\'enabled\' => false];
$config[\'projects\'] = [\'enabled\' => false];
$config[\'moments\'] = [\'enabled\' => false];
$config[\'guestbook\'] = [\'enabled\' => false];
$config[\'music\'] = [\'enabled\' => false];
$config[\'donation\'] = [\'enabled\' => false];
$config[\'api\'] = [\'rss_cache\' => 3600, \'github_cache\' => 1800];
';

                $configFile = __DIR__ . '/api/config.php';
                if (file_put_contents($configFile, $configContent)) {
                    unset($_SESSION['install_db']);
                    unset($_SESSION['install_admin']);

                    $_SESSION['install_completed'] = true;
                    header('Location: install.php?step=3');
                    exit;
                } else {
                    $message = '配置文件写入失败，请检查目录权限';
                }
            } catch (Exception $e) {
                $message = '安装失败: ' . $e->getMessage();
            }
        }
    }
}

$dbConfig = $_SESSION['install_db'] ?? [];
$adminConfig = $_SESSION['install_admin'] ?? [];
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>安装向导 - MoeHome 管理后台</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/admin.css" />

    <style>
        .install-container {
            max-width: 600px;
            margin: 40px auto;
        }

        .install-progress {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .progress-step {
            display: flex;
            align-items: center;
        }

        .progress-step:not(:last-child)::after {
            content: '';
            width: 60px;
            height: 2px;
            background: var(--border);
            margin: 0 16px;
        }

        .progress-step.completed:not(:last-child)::after {
            background: var(--accent);
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            background: var(--bg-tertiary);
            color: var(--text-secondary);
        }

        .progress-step.active .step-number {
            background: var(--accent);
            color: white;
        }

        .progress-step.completed .step-number {
            background: var(--success);
            color: white;
        }

        .step-label {
            margin-top: 8px;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
        }

        .progress-step.active .step-label {
            color: var(--accent);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-progress">
            <div class="progress-step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                <div class="step-number">
                    <?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?>
                </div>
                <div class="step-label">数据库配置</div>
            </div>

            <div class="progress-step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                <div class="step-number">
                    <?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?>
                </div>
                <div class="step-label">管理员账户</div>
            </div>

            <div class="progress-step <?php echo $step >= 3 ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-label">完成安装</div>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo h($messageType); ?>">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo h($message); ?></span>
        </div>
        <?php endif; ?>

        <div class="card">
            <?php if ($step === 1): ?>
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-database"></i>
                    数据库配置
                </h2>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="db_host">数据库主机</label>
                    <input type="text" id="db_host" name="db_host" class="form-input" value="<?php echo h($dbConfig['host'] ?? 'localhost'); ?>" placeholder="localhost" />
                </div>

                <div class="form-group">
                    <label class="form-label" for="db_name">数据库名称 *</label>
                    <input type="text" id="db_name" name="db_name" class="form-input" value="<?php echo h($dbConfig['name'] ?? ''); ?>" placeholder="moehome" required />
                    <div class="form-hint">请提前在 MySQL 中创建此数据库</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="db_user">数据库用户名 *</label>
                    <input type="text" id="db_user" name="db_user" class="form-input" value="<?php echo h($dbConfig['user'] ?? ''); ?>" placeholder="root" required />
                </div>

                <div class="form-group">
                    <label class="form-label" for="db_pass">数据库密码</label>
                    <input type="password" id="db_pass" name="db_pass" class="form-input" value="<?php echo h($dbConfig['pass'] ?? ''); ?>" placeholder="••••••••" />
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i>
                        测试连接并继续
                    </button>
                </div>
            </form>

            <?php elseif ($step === 2): ?>
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-user-shield"></i>
                    创建管理员账户
                </h2>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="admin_user">管理员用户名 *</label>
                    <input type="text" id="admin_user" name="admin_user" class="form-input" value="<?php echo h($adminConfig['user'] ?? ''); ?>" placeholder="admin" required />
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin_pass">管理员密码 *</label>
                    <input type="password" id="admin_pass" name="admin_pass" class="form-input" placeholder="至少 6 位" required minlength="6" />
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin_email">管理员邮箱</label>
                    <input type="email" id="admin_email" name="admin_email" class="form-input" value="<?php echo h($adminConfig['email'] ?? ''); ?>" placeholder="admin@example.com" />
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>请牢记管理员用户名和密码，安装完成后无法找回</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 24px;">
                    <a href="install.php?step=1" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        上一步
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i>
                        完成安装
                    </button>
                </div>
            </form>

            <?php elseif ($step === 3): ?>
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    安装完成
                </h2>
            </div>

            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>MoeHome 管理后台安装成功！</span>
            </div>

            <div style="text-align: center; margin: 32px 0;">
                <i class="fas fa-rocket" style="font-size: 4rem; color: var(--accent); margin-bottom: 24px;"></i>
                <h3 style="margin-bottom: 16px;">恭喜！您的站点已准备就绪</h3>
                <p style="color: var(--text-secondary); margin-bottom: 32px;">
                    接下来您可以开始配置您的个人主页了
                </p>
            </div>

            <div style="display: flex; justify-content: center; gap: 16px;">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i>
                    进入后台
                </a>
                <a href="../index.php" target="_blank" class="btn btn-secondary btn-lg">
                    <i class="fas fa-external-link-alt"></i>
                    预览站点
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
