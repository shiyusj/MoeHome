<?php
/**
 * MoeHome 后台管理 - 密码重置页面
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/api/database.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$tokenValid = false;
$userEmail = '';
$username = '';

if (empty($token)) {
    $error = '无效的重置链接，缺少令牌';
} else {
    $resetData = verifyResetToken($token);
    if ($resetData) {
        $tokenValid = true;
        $userEmail = $resetData['email'];
        $username = $resetData['username'];
    } else {
        $error = '重置链接已过期或无效，请重新申请';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = '表单令牌无效，请刷新页面重试';
    } elseif (empty($newPassword)) {
        $error = '请输入新密码';
    } elseif (strlen($newPassword) < 8) {
        $error = '密码长度至少为 8 个字符';
    } elseif ($newPassword !== $confirmPassword) {
        $error = '两次输入的密码不一致';
    } else {
        if (resetPassword($token, $newPassword)) {
            $success = '密码重置成功！请使用新密码登录';
            $tokenValid = false;
        } else {
            $error = '密码重置失败，请重新尝试';
        }
    }
}

$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>重置密码 - MoeHome 管理后台</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --text-primary: #1a1a2e;
            --text-secondary: #6b7280;
            --accent: #ff6b4a;
            --accent-hover: #ff5722;
            --accent-dim: rgba(255, 107, 74, 0.1);
            --border: #e5e7eb;
            --error: #ef4444;
            --error-dim: rgba(239, 68, 68, 0.1);
            --success: #10b981;
            --success-dim: rgba(16, 185, 129, 0.1);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --font-mono: "JetBrains Mono", "SF Mono", monospace;
        }

        [data-theme="dark"] {
            --bg-primary: #0f0f1a;
            --bg-secondary: #1a1a2e;
            --text-primary: #e5e7eb;
            --text-secondary: #9ca3af;
            --accent: #00ff9f;
            --accent-hover: #00e589;
            --accent-dim: rgba(0, 255, 159, 0.1);
            --border: #374151;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -1px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-secondary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            width: 100%;
            max-width: 420px;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .reset-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .reset-logo .prompt {
            font-family: var(--font-mono);
            color: var(--accent);
        }

        .reset-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .terminal-window {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .terminal-header {
            background: var(--bg-secondary);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--border);
        }

        .terminal-dots {
            display: flex;
            gap: 6px;
        }

        .terminal-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .terminal-dot.red { background: #ff5f57; }
        .terminal-dot.yellow { background: #febc2e; }
        .terminal-dot.green { background: #28c840; }

        .terminal-title {
            flex: 1;
            text-align: center;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .terminal-body {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-reset:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .btn-reset:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-login-link {
            display: block;
            width: 100%;
            padding: 12px;
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
            text-decoration: none;
        }

        .btn-login-link:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
        }

        .alert-error {
            background: var(--error-dim);
            color: var(--error);
        }

        .alert-success {
            background: var(--success-dim);
            color: var(--success);
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            border: 1px solid var(--border);
            border-radius: 50%;
            background: var(--bg-primary);
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .theme-toggle:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .reset-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .reset-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        .reset-footer a:hover {
            text-decoration: underline;
        }

        .user-info {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .user-info strong {
            color: var(--text-primary);
        }

        .password-requirements {
            margin-top: 8px;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        @media (max-width: 480px) {
            .terminal-body {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="切换主题">
        <i class="fas fa-adjust"></i>
    </button>

    <div class="reset-container">
        <div class="reset-header">
            <div class="reset-logo">
                <span class="prompt">$</span>
                <span>MoeHome</span>
            </div>
            <p class="reset-subtitle">重置密码</p>
        </div>

        <div class="terminal-window">
            <div class="terminal-header">
                <div class="terminal-dots">
                    <span class="terminal-dot red"></span>
                    <span class="terminal-dot yellow"></span>
                    <span class="terminal-dot green"></span>
                </div>
                <span class="terminal-title">reset-password</span>
            </div>

            <div class="terminal-body">
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo h($error); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo h($success); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($tokenValid): ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />

                    <div class="user-info">
                        <i class="fas fa-user"></i>
                        重置用户：<strong><?php echo h($username); ?></strong>
                        <br />
                        <small>邮箱：<?php echo h($userEmail); ?></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">新密码</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-input"
                                placeholder="请输入新密码"
                                minlength="8"
                                required
                                autofocus
                            />
                        </div>
                        <div class="password-requirements">
                            <i class="fas fa-info-circle"></i>
                            密码长度至少为 8 个字符
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">确认密码</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="请再次输入新密码"
                                minlength="8"
                                required
                            />
                        </div>
                    </div>

                    <button type="submit" class="btn-reset">
                        <i class="fas fa-key"></i>
                        <span>确认重置</span>
                    </button>

                    <a href="login.php" class="btn-login-link">
                        <i class="fas fa-arrow-left"></i>
                        <span>返回登录</span>
                    </a>
                </form>
                <?php elseif ($success): ?>
                <a href="login.php" class="btn-reset">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>前往登录</span>
                </a>
                <?php else: ?>
                <a href="login.php?forgot=1" class="btn-reset">
                    <i class="fas fa-redo"></i>
                    <span>重新申请重置链接</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="reset-footer">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i>
                返回首页
            </a>
        </div>
    </div>

    <script>
        (function() {
            var theme = localStorage.getItem('moehome-theme');
            var themeData = theme ? JSON.parse(theme) : null;
            var mode = themeData?.mode || 'light';

            document.documentElement.setAttribute('data-theme', mode);

            window.toggleTheme = function() {
                mode = mode === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', mode);
                localStorage.setItem('moehome-theme', JSON.stringify({
                    mode: mode,
                    scheme: themeData?.scheme || (mode === 'light' ? 'coralOrange' : 'cyberGreen')
                }));

                var icon = document.querySelector('.theme-toggle i');
                icon.className = mode === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            };

            if (mode === 'dark') {
                var icon = document.querySelector('.theme-toggle i');
                icon.className = 'fas fa-sun';
            }
        })();
    </script>
</body>
</html>
