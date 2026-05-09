<?php
/**
 * Memos API 代理
 * 解决跨域限制和 API 版本兼容性问题
 * 
 * 使用方法: /api/memos.php?count=10&tags=tag1,tag2
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/config.example.php';

$memosUrl = isset($_GET['url']) ? $_GET['url'] : ($config['moments']['memosUrl'] ?? '');
$count = isset($_GET['count']) ? intval($_GET['count']) : ($config['moments']['count'] ?? 10);
$tags = isset($_GET['tags']) ? explode(',', $_GET['tags']) : ($config['moments']['tags'] ?? []);

if (empty($memosUrl)) {
    echo json_encode(['error' => 'Memos URL required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$memosUrl = rtrim($memosUrl, '/');
$cacheKey = 'memos_' . md5($memosUrl . $count . implode(',', $tags));
$cacheFile = __DIR__ . '/cache/' . $cacheKey . '.json';
$cacheTime = 300; // 5分钟缓存

if (!is_dir(__DIR__ . '/cache')) {
    @mkdir(__DIR__ . '/cache', 0755, true);
}

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$sslVerify = isset($config['api']['ssl_verify']) ? $config['api']['ssl_verify'] : true;

$context = stream_context_create([
    'http' => [
        'timeout' => 15,
        'ignore_errors' => true,
        'header' => "User-Agent: Mozilla/5.0 (compatible; MoeHome Memos Fetcher/1.0)\r\n"
    ],
    'ssl' => [
        'verify_peer' => $sslVerify,
        'verify_peer_name' => $sslVerify
    ]
]);

$result = @file_get_contents($memosUrl . '/api/memo?limit=' . $count, false, $context);

if ($result === false) {
    echo json_encode(['error' => 'Failed to fetch Memos data'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($result, true);

if (isset($data['error']) || !isset($data['data'])) {
    echo json_encode(['error' => 'Invalid Memos API response'], JSON_UNESCAPED_UNICODE);
    exit;
}

$memos = $data['data'];

if (!empty($tags)) {
    $memos = array_filter($memos, function($memo) use ($tags) {
        $memoTags = $memo['tags'] ?? [];
        foreach ($tags as $tag) {
            if (in_array($tag, $memoTags)) {
                return true;
            }
        }
        return false;
    });
    $memos = array_values($memos);
}

$response = [
    'memos' => array_slice($memos, 0, $count),
    'total' => count($memos),
    'fetched' => date('c')
];

$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);
@file_put_contents($cacheFile, $jsonResponse);

echo $jsonResponse;
