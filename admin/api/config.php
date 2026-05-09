<?php
/**
 * MoeHome 后台管理 - 配置管理 API
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['success' => false, 'error' => '无效的请求数据']);
        exit;
    }

    $action = $input['action'] ?? '';
    $category = $input['category'] ?? '';
    $data = $input['data'] ?? [];

    if (empty($category)) {
        echo json_encode(['success' => false, 'error' => '缺少分类参数']);
        exit;
    }

    switch ($action) {
        case 'save':
            foreach ($data as $key => $value) {
                $type = gettype($value);
                if ($type === 'boolean') {
                    $type = 'boolean';
                    $value = $value ? '1' : '0';
                } elseif ($type === 'array') {
                    $type = 'json';
                } else {
                    $type = 'string';
                }
                setConfig($category, $key, $value, $type);
            }

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

        default:
            echo json_encode([
                'success' => false,
                'error' => '未知操作'
            ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => '不支持的请求方法'
    ]);
}
