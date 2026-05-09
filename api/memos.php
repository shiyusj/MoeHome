<?php
/**
 * Memos API 代理 - 优化版本
 * 解决跨域限制和 API 版本兼容性问题
 *
 * 优化:
 * - 缓存机制
 * - 错误处理
 * - 参数验证
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$config = [];
$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$memosUrl = $_GET['url'] ?? ($config['moments']['memosUrl'] ?? '');
$count = min(50, max(1, intval($_GET['count'] ?? ($config['moments']['count'] ?? 10))));
$tags = isset($_GET['tags']) ? array_filter(array_map('trim', explode(',', $_GET['tags']))) : [];

if (empty($memosUrl)) {
    echo json_encode(['error' => 'Memos URL required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$memosUrl = rtrim($memosUrl, '/');

if (!filter_var($memosUrl, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Invalid Memos URL'], JSON_UNESCAPED_UNICODE);
    exit;
}

$cacheKey = 'memos_' . md5($memosUrl . $count . implode(',', $tags));
$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTime = max(60, intval($_GET['cache'] ?? 300));

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $cached = @file_get_contents($cacheFile);
    if ($cached !== false) {
        echo $cached;
        exit;
    }
}

$sslVerify = (bool)($config['api']['ssl_verify'] ?? true);

$contextOptions = [
    'http' => [
        'method' => 'GET',
        'timeout' => 15,
        'ignore_errors' => true,
        'header' => [
            "User-Agent: Mozilla/5.0 (compatible; MoeHome Memos Fetcher/2.0)\r\n",
            "Accept: application/json\r\n",
            "Accept-Encoding: identity\r\n"
        ]
    ],
    'ssl' => [
        'verify_peer' => $sslVerify,
        'verify_peer_name' => $sslVerify
    ]
];

$context = stream_context_create($contextOptions);

$apiEndpoints = [
    '/api/memo/resource?limit=' . $count,
    '/api/v1/memo?pageSize=' . $count,
    '/api/memo?limit=' . $count
];

$result = null;
$usedEndpoint = '';

foreach ($apiEndpoints as $endpoint) {
    $result = @file_get_contents($memosUrl . $endpoint, false, $context);
    if ($result !== false) {
        $usedEndpoint = $endpoint;
        break;
    }
}

if ($result === false) {
    echo json_encode(['error' => 'Failed to fetch Memos data'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($result, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON response from Memos'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($data['error']) || (isset($data['code']) && $data['code'] !== 0)) {
    echo json_encode(['error' => $data['message'] ?? 'Memos API error'], JSON_UNESCAPED_UNICODE);
    exit;
}

$memos = [];
if (isset($data['data']) && is_array($data['data'])) {
    $memos = $data['data'];
} elseif (isset($data['data']) && is_object($data['data'])) {
    $memos = $data['data'];
} elseif (is_array($data) && !isset($data['error'])) {
    $memos = $data;
}

if (!empty($tags) && !empty($memos)) {
    $memos = array_filter($memos, function($memo) use ($tags) {
        $memoTags = $memo['tags'] ?? [];
        if (!is_array($memoTags)) {
            $memoTags = is_string($memoTags) ? [$memoTags] : [];
        }
        foreach ($tags as $tag) {
            if (in_array($tag, $memoTags, true)) {
                return true;
            }
        }
        return false;
    });
    $memos = array_values($memos);
}

$memos = array_slice($memos, 0, $count);

$response = [
    'memos' => $memos,
    'total' => count($memos),
    'fetched' => date('c'),
    'endpoint' => $usedEndpoint
];

$jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
$jsonResponse = json_encode($response, $jsonOptions);

if ($jsonResponse !== false) {
    @file_put_contents($cacheFile, $jsonResponse, LOCK_EX);
}

echo $jsonResponse;
