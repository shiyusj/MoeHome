<?php
/**
 * MoeHome 后台管理 - 数据库配置
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

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
define('ADMIN_PATH', __DIR__);
define('SITE_PATH', dirname(__DIR__));

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
                throw new Exception('数据库连接失败: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchOne(string $sql, array $params = []): ?array {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): int {
        $keys = array_keys($data);
        $fields = '`' . implode('`, `', $keys) . '`';
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return (int)self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "`{$key}` = :{$key}";
        }
        $sql = "UPDATE `{$table}` SET " . implode(', ', $set) . " WHERE {$where}";
        $stmt = self::query($sql, array_merge($data, $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return self::query($sql, $params)->rowCount();
    }

    public static function exists(string $table): bool {
        try {
            self::query("SELECT 1 FROM `{$table}` LIMIT 1");
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
        $timeout = 3600;
        if ((time() - $_SESSION['admin_login_time']) > $timeout) {
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

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['admin_login_time'] = time();

    Database::update('moehome_users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $user['id']]);

    return true;
}

function logout(): void {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

function getCsrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function h(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
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

function generateRandomPassword(int $length = 16): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

function installDatabase(): bool {
    $sql = "
    CREATE TABLE IF NOT EXISTS `moehome_users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `email` VARCHAR(100),
        `role` ENUM('admin', 'editor') DEFAULT 'admin',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `last_login` DATETIME,
        INDEX `idx_username` (`username`)
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
    ";

    try {
        $pdo = Database::getInstance();
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }

        Database::insert('moehome_users', [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);

        return true;
    } catch (Exception $e) {
        error_log('安装失败: ' . $e->getMessage());
        return false;
    }
}

function checkLoginAttempts(string $ip): bool {
    $record = Database::fetchOne(
        "SELECT * FROM moehome_login_attempts WHERE ip = :ip",
        [':ip' => $ip]
    );

    if (!$record) {
        return true;
    }

    if ($record['locked_until'] && strtotime($record['locked_until']) > time()) {
        return false;
    }

    if (strtotime($record['last_attempt']) < strtotime('-15 minutes')) {
        Database::delete('moehome_login_attempts', 'ip = :ip', [':ip' => $ip]);
        return true;
    }

    return true;
}

function recordLoginAttempt(string $ip, ?string $username, bool $success): void {
    $record = Database::fetchOne(
        "SELECT * FROM moehome_login_attempts WHERE ip = :ip",
        [':ip' => $ip]
    );

    if ($success) {
        if ($record) {
            Database::delete('moehome_login_attempts', 'ip = :ip', [':ip' => $ip]);
        }
        return;
    }

    if (!$record) {
        Database::insert('moehome_login_attempts', [
            'ip' => $ip,
            'username' => $username,
            'attempts' => 1,
            'last_attempt' => date('Y-m-d H:i:s')
        ]);
    } else {
        $attempts = $record['attempts'] + 1;
        $lockedUntil = $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;

        Database::update('moehome_login_attempts', [
            'attempts' => $attempts,
            'last_attempt' => date('Y-m-d H:i:s'),
            'locked_until' => $lockedUntil
        ], 'ip = :ip', [':ip' => $ip]);
    }
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
            return in_array(strtolower($result['value']), ['true', '1', 'yes', 'on']);
        case 'array':
        case 'json':
            return json_decode($result['value'], true);
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
                $config[$row['key']] = in_array(strtolower($row['value']), ['true', '1', 'yes', 'on']);
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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
