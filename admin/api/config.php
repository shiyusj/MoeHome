<?php
/**
 * MoeHome 后台管理 - 配置管理 API
 * 安全加强版本
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';

blockSuspiciousRequests();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$clientIp = getClientIp();
$rateLimitKey = 'api_config_' . md5($clientIp);

if (!checkApiRateLimit($rateLimitKey, 20, 60)) {
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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '无效的请求数据格式']);
        exit;
    }

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '缺少请求数据']);
        exit;
    }

    $action = $input['action'] ?? '';
    $category = isset($input['category']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $input['category']) : '';
    $data = $input['data'] ?? [];

    if (empty($category)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '缺少分类参数']);
        exit;
    }

    $allowedCategories = ['site', 'profile', 'seo', 'footer', 'theme', 'modules', 'email', 'api'];
    if (!in_array($category, $allowedCategories, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '不允许的操作']);
        exit;
    }

    switch ($action) {
        case 'save':
            if (!is_array($data)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '无效的数据格式']);
                exit;
            }

            foreach ($data as $key => $value) {
                $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$key);

                if (strlen($safeKey) > 50) {
                    continue;
                }

                $type = gettype($value);
                if ($type === 'boolean') {
                    $type = 'boolean';
                    $value = $value ? '1' : '0';
                } elseif ($type === 'array') {
                    $type = 'json';
                } else {
                    $type = 'string';
                    $value = is_string($value) ? substr($value, 0, 65535) : (string)$value;
                }

                setConfig($category, $safeKey, $value, $type);
            }

            logSecurityEvent('config_save', "Config saved: {$category}");
            echo json_encode([
                'success' => true,
                'message' => '配置已保存'
            ]);
            break;

        case 'get':
            $config = getAllConfig($category);
            echo json_encode([
                'success' => true,
                'data' => $config
            ]);
            break;

        case 'delete':
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => '删除操作不被允许'
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => '未知操作'
            ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => '不支持的请求方法'
    ]);
}
