<?php
/**
 * MoeHome 安全中间件
 * 所有PHP文件应在开始时包含此文件
 */

declare(strict_types=1);

if (!defined('SECURITY_LOADED')) {

    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("X-Robots-Tag: noindex, nofollow");

    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https: blob:; connect-src 'self' https:; frame-ancestors 'self';");

    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        exit(0);
    }

    define('SECURITY_LOADED', true);
}

function sanitizeInput(string $input): string {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $input;
}

function sanitizeFilename(string $filename): string {
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
    $filename = preg_replace('/\.+/', '.', $filename);
    $filename = trim($filename, '.');
    return $filename ?: 'file';
}

function validateTokenFormat(string $token): bool {
    return preg_match('/^[a-f0-9]{64,}$/', $token) === 1;
}

function generateSecureToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email) === 1
        && strlen($email) <= 254;
}

function isSuspiciousRequest(): bool {
    $suspiciousPatterns = [
        '/<script/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<\/?(iframe|object|embed)/i',
        '/data:/i',
        '/base64/i'
    ];

    $body = file_get_contents('php://input');
    $query = $_SERVER['QUERY_STRING'] ?? '';
    $combined = $body . $query;

    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $combined)) {
            return true;
        }
    }

    return false;
}

function blockSuspiciousRequests(): void {
    if (isSuspiciousRequest()) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid request'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
