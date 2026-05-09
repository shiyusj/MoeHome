<?php
/**
 * MoeHome 后台管理 - 缓存管理 API
 * 安全加强版本
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/../api/database.php';
require_once __DIR__ . '/security.php';

blockSuspiciousRequests();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$clientIp = getClientIp();
$rateLimitKey = 'api_cache_' . md5($clientIp);

if (!checkApiRateLimit($rateLimitKey, 10, 60)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => '请求过于频繁，请稍后再试',
        'retry_after' => 60
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '未登录或登录已过期']);
    exit;
}

requireLogin();

$action = $_GET['action'] ?? '';

$allowedActions = ['clear', 'status'];
if (!in_array($action, $allowedActions, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => '未知操作'
    ]);
    exit;
}

switch ($action) {
    case 'clear':
        $cacheDir = __DIR__ . '/../api/cache';

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
            echo json_encode(['success' => true, 'message' => '缓存目录已创建', 'count' => 0]);
            exit;
        }

        if (!is_writable($cacheDir)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => '缓存目录不可写'
            ]);
            exit;
        }

        $files = glob($cacheDir . '/*.json');
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file) && is_writable($file)) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }

        logSecurityEvent('cache_clear', "Cache cleared: {$count} files");
        echo json_encode([
            'success' => true,
            'message' => "已清理 {$count} 个缓存文件",
            'count' => $count
        ]);
        break;

    case 'status':
        $cacheDir = __DIR__ . '/../api/cache';

        if (!is_dir($cacheDir)) {
            $status = [
                'exists' => false,
                'count' => 0,
                'size' => 0,
                'writable' => false
            ];
        } else {
            $files = glob($cacheDir . '/*.json');
            $size = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    $size += filesize($file);
                }
            }

            $status = [
                'exists' => true,
                'count' => count($files),
                'size' => $size,
                'sizeFormatted' => formatBytes($size),
                'writable' => is_writable($cacheDir)
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $status
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '未知操作'
        ]);
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}
