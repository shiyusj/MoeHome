<?php
/**
 * MoeHome 后台管理 - 登录页面
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
$isLocked = false;
$showForgotForm = isset($_GET['forgot']);
$showResetForm = isset($_GET['reset']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['forgot_password'])) {
        $email = trim($_POST['email'] ?? '');
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!verifyCsrfToken($csrfToken)) {
            $error = '表单令牌无效，请刷新页面重试';
            $showForgotForm = true;
        } elseif (empty($email)) {
            $error = '请输入邮箱地址';
            $showForgotForm = true;
        } else {
            $result = requestPasswordReset($email);
            if ($result) {
                $success = '如果该邮箱已注册，重置链接将发送到您的邮箱，请查收';
            } else {
                $success = '如果该邮箱已注册，重置链接将发送到您的邮箱，请查收';
            }
        }
    } elseif (isset($_POST['login'])) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!verifyCsrfToken($csrfToken)) {
            $error = '表单令牌无效，请刷新页面重试';
        } elseif (empty($username) || empty($password)) {
            $error = '请输入用户名和密码';
        } else {
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            if (!checkLoginAttempts($clientIp)) {
                $isLocked = true;
                $error = '登录尝试次数过多，请 15 分钟后再试';
            } else {
                if (login($username, $password)) {
                    recordLoginAttempt($clientIp, $username, true);
                    header('Location: index.php');
                    exit;
                } else {
                    recordLoginAttempt($clientIp, $username, false);
                    $remaining = 5 - Database::fetchOne(
                        "SELECT attempts FROM moehome_login_attempts WHERE ip = :ip",
                        [':ip' => $clientIp]
                    )['attempts'];
                    $error = '用户名或密码错误' . ($remaining > 0 ? '（剩余 ' . max(0, $remaining) . ' 次尝试）' : '');
                }
            }
        }
    }
}

if (isset($_GET['timeout'])) {
    $error = '登录已过期，请重新登录';
}

if (isset($_GET['loggedout'])) {
    $success = '已成功退出登录';
}

$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>登录 - MoeHome 管理后台</title>

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

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 12px;
        }

        .login-logo .prompt {
            font-family: var(--font-mono);
            color: var(--accent);
        }

        .login-subtitle {
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

        .btn-login {
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

        .btn-login:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .login-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .security-notice {
            margin-top: 20px;
            padding: 12px;
            background: var(--bg-secondary);
            border-radius: 8px;
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-align: center;
        }

        .security-notice i {
            margin-right: 6px;
        }

        .forgot-password-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 0.875rem;
            color: var(--accent);
            text-decoration: none;
            cursor: pointer;
        }

        .forgot-password-link:hover {
            text-decoration: underline;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }

        .forgot-header i {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 12px;
        }

        .forgot-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .forgot-header p {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .btn-back {
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
        }

        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent);
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

    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <span class="prompt">$</span>
                <span>MoeHome</span>
            </div>
            <p class="login-subtitle">管理后台</p>
        </div>

        <div class="terminal-window">
            <div class="terminal-header">
                <div class="terminal-dots">
                    <span class="terminal-dot red"></span>
                    <span class="terminal-dot yellow"></span>
                    <span class="terminal-dot green"></span>
                </div>
                <span class="terminal-title">admin-login</span>
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

                <?php if ($isLocked): ?>
                <div class="alert alert-error">
                    <i class="fas fa-lock"></i>
                    <span>账户已被锁定，请在 15 分钟后重试</span>
                </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm" style="<?php echo $showForgotForm ? 'display: none;' : ''; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />

                    <div class="form-group">
                        <label class="form-label" for="username">用户名</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="请输入用户名"
                                value="<?php echo h($_POST['username'] ?? ''); ?>"
                                autocomplete="username"
                                required
                                autofocus
                            />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">密码</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="请输入密码"
                                autocomplete="current-password"
                                required
                            />
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-login" <?php echo $isLocked ? 'disabled' : ''; ?>>
                        <i class="fas fa-sign-in-alt"></i>
                        <span>登录</span>
                    </button>
                </form>

                <a href="?forgot=1" class="forgot-password-link" id="forgotLink">
                    <i class="fas fa-question-circle"></i>
                    忘记密码？
                </a>

                <form method="POST" action="" id="forgotForm" style="<?php echo $showForgotForm ? '' : 'display: none;'; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                    <input type="hidden" name="forgot_password" value="1" />

                    <div class="forgot-header">
                        <i class="fas fa-key"></i>
                        <h3>找回密码</h3>
                        <p>输入您的注册邮箱，我们将发送密码重置链接</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">邮箱地址</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="请输入注册邮箱"
                                autocomplete="email"
                                required
                                autofocus
                            />
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-paper-plane"></i>
                        <span>发送重置链接</span>
                    </button>

                    <button type="button" class="btn-back" onclick="showLoginForm()">
                        <i class="fas fa-arrow-left"></i>
                        <span>返回登录</span>
                    </button>
                </form>

                <div class="security-notice">
                    <i class="fas fa-shield-alt"></i>
                    登录尝试失败 5 次后将被锁定 15 分钟
                </div>
            </div>
        </div>

        <div class="login-footer">
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

            window.showLoginForm = function() {
                document.getElementById('loginForm').style.display = 'block';
                document.getElementById('forgotForm').style.display = 'none';
                document.getElementById('forgotLink').style.display = 'block';
                document.getElementById('username').focus();
            };

            window.showForgotForm = function() {
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('forgotForm').style.display = 'block';
                document.getElementById('forgotLink').style.display = 'none';
                document.getElementById('email').focus();
            };

            if (window.location.search.includes('forgot=1')) {
                showForgotForm();
            }
        })();
    </script>
</body>
</html>
