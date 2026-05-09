<?php
/**
 * MoeHome 后台管理 - 修改密码
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/api/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

requireLogin();

$csrfToken = getCsrfToken();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = '请填写所有字段';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = '两次输入的新密码不一致';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = '新密码长度至少 6 位';
        $messageType = 'error';
    } else {
        $user = Database::fetchOne(
            "SELECT password FROM moehome_users WHERE id = :id",
            [':id' => $_SESSION['admin_id']]
        );

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $message = '当前密码错误';
            $messageType = 'error';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            Database::update('moehome_users',
                ['password' => $hashedPassword],
                'id = :id',
                [':id' => $_SESSION['admin_id']]
            );

            $message = '密码修改成功';
            $messageType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>修改密码 - MoeHome 管理后台</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/admin.css" />
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <h1 class="topbar-title">
                    <i class="fas fa-key"></i>
                    修改密码
                </h1>
                <div class="topbar-actions">
                    <button class="btn btn-secondary btn-sm" data-theme-toggle>
                        <i class="fas fa-adjust"></i>
                    </button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo h($messageType); ?> fade-in">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo h($message); ?></span>
                </div>
                <?php endif; ?>

                <div class="card fade-in" style="max-width: 500px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-lock"></i>
                            账户安全
                        </h2>
                    </div>

                    <div class="terminal-window" style="margin: 0; border: none;">
                        <div class="terminal-body" style="padding: 0;">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />

                                <div class="form-group">
                                    <label class="form-label" for="current_password">当前密码</label>
                                    <div style="position: relative;">
                                        <input type="password" id="current_password" name="current_password" class="form-input" placeholder="请输入当前密码" required style="padding-right: 44px;" />
                                        <button type="button" onclick="togglePassword('current_password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="new_password">新密码</label>
                                    <div style="position: relative;">
                                        <input type="password" id="new_password" name="new_password" class="form-input" placeholder="请输入新密码（至少6位）" required minlength="6" style="padding-right: 44px;" />
                                        <button type="button" onclick="togglePassword('new_password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-hint">
                                        <i class="fas fa-info-circle"></i>
                                        密码长度至少 6 个字符
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="confirm_password">确认新密码</label>
                                    <div style="position: relative;">
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="请再次输入新密码" required minlength="6" style="padding-right: 44px;" />
                                        <button type="button" onclick="togglePassword('confirm_password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div style="margin-top: 24px;">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-save"></i>
                                        保存新密码
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card fade-in" style="max-width: 500px;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-shield-alt"></i>
                            安全建议
                        </h2>
                    </div>

                    <ul style="list-style: none; padding: 0; color: var(--text-secondary); font-size: 0.875rem;">
                        <li style="padding: 8px 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            使用包含字母、数字和特殊字符的强密码
                        </li>
                        <li style="padding: 8px 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            避免使用与其他网站相同的密码
                        </li>
                        <li style="padding: 8px 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            定期更换密码，建议每 3 个月一次
                        </li>
                        <li style="padding: 8px 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            不要在公共电脑上保存密码
                        </li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        function togglePassword(inputId) {
            var input = document.getElementById(inputId);
            var button = input.nextElementSibling;
            var icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
