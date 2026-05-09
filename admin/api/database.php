<?php
/**
 * MoeHome 后台管理 - 数据库配置
 * 优化版本 v2.1 - 添加邮箱找回密码功能
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configFile = __DIR__ . '/../api/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/../api/config.example.php';
}

define('DB_HOST', $config['database']['host'] ?? 'localhost');
define('DB_NAME', $config['database']['name'] ?? 'moehome');
define('DB_USER', $config['database']['username'] ?? 'root');
define('DB_PASS', $config['database']['password'] ?? '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', $config['site']['name'] ?? 'MoeHome');
define('SITE_URL', rtrim($config['site']['url'] ?? '', '/'));
define('ADMIN_PATH', __DIR__);
define('SITE_PATH', dirname(__DIR__));

define('RESET_TOKEN_EXPIRY', 1800);
define('RATE_LIMIT_WINDOW', 300);
define('MAX_LOGIN_ATTEMPTS', 5);

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                throw new Exception('数据库连接失败');
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        return self::getInstance()->prepare($sql)->execute($params) 
            ? self::getInstance()->prepare($sql) : throw new Exception('Query failed');
    }

    public static function fetchOne(string $sql, array $params = []): ?array {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function fetchAll(string $sql, array $params = []): array {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public static function insert(string $table, array $data): int {
        $keys = array_keys($data);
        $fields = '`' . implode('`, `', $keys) . '`';
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
        self::getInstance()->prepare($sql)->execute($data);
        return (int)self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "`{$key}` = :{$key}";
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $set) . " WHERE {$where}";
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute(array_merge($data, $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function exists(string $table): bool {
        try {
            self::getInstance()->query("SELECT 1 FROM `{$table}` LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

function isInstalled(): bool {
    try {
        return Database::exists('moehome_users') && Database::exists('moehome_config');
    } catch (Exception $e) {
        return false;
    }
}

function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_login_time']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    if (isset($_SESSION['admin_login_time'])) {
        if ((time() - $_SESSION['admin_login_time']) > 3600) {
            logout();
            header('Location: login.php?timeout=1');
            exit;
        }
    }
}

function login(string $username, string $password): bool {
    $user = Database::fetchOne(
        "SELECT * FROM moehome_users WHERE username = :username",
        [':username' => $username]
    );

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['admin_login_time'] = time();
    $_SESSION['admin_ip'] = getClientIp();

    Database::update('moehome_users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $user['id']]);

    return true;
}

function logout(): void {
    session_unset();
    session_destroy();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}

function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function h(string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return Database::fetchOne(
        "SELECT id, username, email, role, created_at FROM moehome_users WHERE id = :id",
        [':id' => $_SESSION['admin_id']]
    );
}

function getClientIp(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '127.0.0.1';
}

function checkLoginAttempts(string $ip): bool {
    $record = Database::fetchOne(
        "SELECT attempts, locked_until, last_attempt FROM moehome_login_attempts WHERE ip = :ip",
        [':ip' => $ip]
    );

    if (!$record) {
        return true;
    }

    if ($record['locked_until'] && strtotime($record['locked_until']) > time()) {
        return false;
    }

    if (strtotime($record['last_attempt']) < (time() - RATE_LIMIT_WINDOW * 2)) {
        Database::delete('moehome_login_attempts', 'ip = :ip', [':ip' => $ip]);
        return true;
    }

    return true;
}

function recordLoginAttempt(string $ip, ?string $username, bool $success): void {
    if ($success) {
        Database::delete('moehome_login_attempts', 'ip = :ip', [':ip' => $ip]);
        return;
    }

    $record = Database::fetchOne(
        "SELECT attempts FROM moehome_login_attempts WHERE ip = :ip",
        [':ip' => $ip]
    );

    if (!$record) {
        Database::insert('moehome_login_attempts', [
            'ip' => $ip,
            'username' => $username,
            'attempts' => 1,
            'last_attempt' => date('Y-m-d H:i:s')
        ]);
    } else {
        $attempts = $record['attempts'] + 1;
        $lockedUntil = $attempts >= MAX_LOGIN_ATTEMPTS ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;
        Database::update('moehome_login_attempts', [
            'attempts' => $attempts,
            'last_attempt' => date('Y-m-d H:i:s'),
            'locked_until' => $lockedUntil
        ], 'ip = :ip', [':ip' => $ip]);
    }
}

function generateResetToken(): string {
    return bin2hex(random_bytes(32)) . time();
}

function createPasswordReset(string $email): ?array {
    $user = Database::fetchOne(
        "SELECT id, username FROM moehome_users WHERE email = :email",
        [':email' => $email]
    );

    if (!$user) {
        return null;
    }

    Database::delete('moehome_password_resets', 'email = :email', [':email' => $email]);

    $token = generateResetToken();
    $hashedToken = hash('sha256', $token);

    Database::insert('moehome_password_resets', [
        'email' => $email,
        'token' => $hashedToken,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', time() + RESET_TOKEN_EXPIRY),
        'ip' => getClientIp()
    ]);

    return [
        'token' => $token,
        'email' => $email,
        'username' => $user['username']
    ];
}

function verifyResetToken(string $token): ?array {
    $hashedToken = hash('sha256', $token);

    $record = Database::fetchOne(
        "SELECT pr.email, pr.expires_at, u.username FROM moehome_password_resets pr
         LEFT JOIN moehome_users u ON pr.email = u.email
         WHERE pr.token = :token",
        [':token' => $hashedToken]
    );

    if (!$record) {
        return null;
    }

    if (strtotime($record['expires_at']) < time()) {
        Database::delete('moehome_password_resets', 'token = :token', [':token' => $hashedToken]);
        return null;
    }

    return [
        'email' => $record['email'],
        'username' => $record['username'] ?? 'User'
    ];
}

function resetPassword(string $token, string $newPassword): bool {
    $resetData = verifyResetToken($token);

    if (!$resetData) {
        return false;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    Database::update('moehome_users',
        ['password' => $hashedPassword],
        'email = :email',
        [':email' => $resetData['email']]
    );

    Database::delete('moehome_password_resets', 'token = :token', [':token' => hash('sha256', $token)]);

    return true;
}

function requestPasswordReset(string $email): bool {
    $smtpHost = getConfig('email', 'smtp_host', '');
    if (empty($smtpHost)) {
        return false;
    }

    $resetData = createPasswordReset($email);
    if (!$resetData) {
        return false;
    }

    return sendPasswordResetEmail(
        $resetData['email'],
        $resetData['token'],
        $resetData['username']
    );
}

function cleanupExpiredTokens(): int {
    return Database::delete('moehome_password_resets', 'expires_at < :now', [':now' => date('Y-m-d H:i:s')]);
}

function sendEmail(string $to, string $subject, string $body, bool $isHtml = true): bool {
    $smtpHost = getConfig('email', 'smtp_host', '');
    $smtpPort = (int)getConfig('email', 'smtp_port', 465);
    $smtpUser = getConfig('email', 'smtp_user', '');
    $smtpPass = getConfig('email', 'smtp_password', '');
    $smtpSecure = getConfig('email', 'smtp_secure', 'ssl');
    $fromEmail = getConfig('email', 'from_email', $smtpUser);
    $fromName = getConfig('email', 'from_name', SITE_NAME);

    if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
        error_log('Email: SMTP not configured');
        return false;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: ' . ($isHtml ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8'),
        'From: ' . encodeMimeHeader($fromName) . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: MoeHome/' . ($config['version'] ?? '2.1')
    ];

    if ($smtpSecure === 'ssl') {
        $host = 'ssl://' . $smtpHost;
    } else {
        $host = $smtpHost;
    }

    $socket = @fsockopen($host, $smtpPort, $errno, $errstr, 10);

    if (!$socket) {
        error_log("Email: Cannot connect to SMTP server - {$errstr} ({$errno})");
        return false;
    }

    stream_set_timeout($socket, 10);

    $response = fgets($socket, 512);
    if (substr($response, 0, 3) !== '220') {
        fclose($socket);
        return false;
    }

    fputs($socket, "EHLO " . gethostname() . "\r\n");
    $response = fgets($socket, 512);

    if ($smtpSecure === 'tls') {
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) !== '220') {
            fclose($socket);
            return false;
        }
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($socket, "EHLO " . gethostname() . "\r\n");
        $response = fgets($socket, 512);
    }

    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) !== '334') {
        fclose($socket);
        return false;
    }

    fputs($socket, base64_encode($smtpUser) . "\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) !== '334') {
        fclose($socket);
        return false;
    }

    fputs($socket, base64_encode($smtpPass) . "\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) !== '235') {
        fclose($socket);
        return false;
    }

    fputs($socket, "MAIL FROM:<{$fromEmail}>\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) !== '250') {
        fclose($socket);
        return false;
    }

    fputs($socket, "RCPT TO:<{$to}>\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) !== '250') {
        fclose($socket);
        return false;
    }

    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) !== '354') {
        fclose($socket);
        return false;
    }

    $message = "To: {$to}\r\n";
    $message .= implode("\r\n", $headers) . "\r\n";
    $message .= "Subject: " . encodeMimeHeader($subject) . "\r\n";
    $message .= "\r\n";
    $message .= $body . "\r\n";
    $message .= ".\r\n";

    fputs($socket, $message);
    $response = fgets($socket, 512);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return substr($response, 0, 3) === '250';
}

function encodeMimeHeader(string $text): string {
    if (preg_match('/[^\x20-\x7E]/', $text)) {
        return '=?' . 'UTF-8' . '?B?' . base64_encode($text) . '?=';
    }
    return $text;
}

function sendPasswordResetEmail(string $email, string $token, string $username): bool {
    $resetUrl = SITE_URL . '/admin/reset-password.php?token=' . $token;

    $subject = '[' . SITE_NAME . '] 密码重置请求';

    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #ff6b4a, #ff5722); padding: 30px; text-align: center; color: white; }
            .header h1 { margin: 0; font-size: 24px; }
            .content { padding: 30px; }
            .content p { color: #666; line-height: 1.6; margin: 0 0 20px; }
            .button { display: inline-block; padding: 14px 32px; background: #ff6b4a; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 600; }
            .button:hover { background: #ff5722; }
            .footer { padding: 20px 30px; background: #f9f9f9; text-align: center; color: #999; font-size: 12px; }
            .warning { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin: 20px 0; font-size: 14px; color: #856404; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>密码重置</h1>
            </div>
            <div class="content">
                <p>您好，<strong>' . h($username) . '</strong></p>
                <p>我们收到了您的密码重置请求。如果您没有请求此操作，请忽略此邮件。</p>
                <p>请点击下面的按钮来重置您的密码：</p>
                <p style="text-align: center;">
                    <a href="' . h($resetUrl) . '" class="button">重置密码</a>
                </p>
                <div class="warning">
                    ⚠️ 此链接将在 30 分钟后过期，请尽快使用。
                </div>
                <p style="font-size: 14px; color: #999;">如果您无法点击按钮，请复制以下链接到浏览器地址栏打开：<br>
                <a href="' . h($resetUrl) . '" style="word-break: break-all;">' . h($resetUrl) . '</a></p>
            </div>
            <div class="footer">
                <p>此邮件由 ' . h(SITE_NAME) . ' 自动发送，请勿回复。</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($email, $subject, $body);
}

function getConfig(string $category, string $key, $default = null) {
    $result = Database::fetchOne(
        "SELECT value, type FROM moehome_config WHERE category = :category AND `key` = :key",
        [':category' => $category, ':key' => $key]
    );

    if (!$result) {
        return $default;
    }

    switch ($result['type']) {
        case 'number':
            return is_numeric($result['value']) ? (int)$result['value'] : (float)$result['value'];
        case 'boolean':
            return in_array(strtolower($result['value']), ['true', '1', 'yes', 'on'], true);
        case 'array':
        case 'json':
            return json_decode($result['value'], true) ?? $default;
        default:
            return $result['value'];
    }
}

function setConfig(string $category, string $key, $value, string $type = 'string'): bool {
    $valueStr = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;

    $existing = Database::fetchOne(
        "SELECT id FROM moehome_config WHERE category = :category AND `key` = :key",
        [':category' => $category, ':key' => $key]
    );

    if ($existing) {
        Database::update('moehome_config',
            ['value' => $valueStr, 'type' => $type, 'updated_at' => date('Y-m-d H:i:s')],
            'category = :category AND `key` = :key',
            [':category' => $category, ':key' => $key]
        );
    } else {
        Database::insert('moehome_config', [
            'category' => $category,
            'key' => $key,
            'value' => $valueStr,
            'type' => $type
        ]);
    }

    return true;
}

function getAllConfig(string $category): array {
    $results = Database::fetchAll(
        "SELECT `key`, value, type FROM moehome_config WHERE category = :category",
        [':category' => $category]
    );

    $config = [];
    foreach ($results as $row) {
        switch ($row['type']) {
            case 'number':
                $config[$row['key']] = is_numeric($row['value']) ? (int)$row['value'] : (float)$row['value'];
                break;
            case 'boolean':
                $config[$row['key']] = in_array(strtolower($row['value']), ['true', '1', 'yes', 'on'], true);
                break;
            case 'array':
            case 'json':
                $config[$row['key']] = json_decode($row['value'], true);
                break;
            default:
                $config[$row['key']] = $row['value'];
        }
    }

    return $config;
}

function clearCache(): bool {
    $cacheDir = __DIR__ . '/../api/cache';
    if (!is_dir($cacheDir)) {
        return true;
    }

    $files = glob($cacheDir . '/*.json');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file) && unlink($file)) {
            $count++;
        }
    }

    return $count > 0 || count($files) === 0;
}

function installDatabase(): bool {
    $pdo = Database::getInstance();

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
    ";

    try {
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        return true;
    } catch (Exception $e) {
        error_log('安装失败: ' . $e->getMessage());
        return false;
    }
}

cleanupExpiredTokens();
