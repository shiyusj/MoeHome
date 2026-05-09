<?php
/**
 * MoeHome 后台管理 - 缓存管理 API
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/../api/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'clear':
        $cacheDir = __DIR__ . '/../api/cache';

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
            echo json_encode(['success' => true, 'message' => '缓存目录已创建']);
            exit;
        }

        $files = glob($cacheDir . '/*.json');
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $count++;
            }
        }

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
