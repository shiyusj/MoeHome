<?php
/**
 * RSS 代理 API - 优化版本
 * 解决虚拟主机 allow_url_fopen 限制问题
 *
 * 优化:
 * - 缓存预检验
 * - 错误处理优化
 * - 内存优化
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');

$config = [];
$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$url = $_GET['url'] ?? '';

if (empty($url)) {
    echo json_encode(['error' => 'URL parameter required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$decodedUrl = urldecode($url);

if (!filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Invalid URL format'], JSON_UNESCAPED_UNICODE);
    exit;
}

$cacheKey = 'rss_' . md5($decodedUrl);
$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/' . $cacheKey . '.json';
$cacheTime = max(60, intval($config['api']['rss_cache'] ?? 3600));

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
$timeout = 10;

$contextOptions = [
    'http' => [
        'method' => 'GET',
        'timeout' => $timeout,
        'ignore_errors' => true,
        'header' => [
            "User-Agent: Mozilla/5.0 (compatible; MoeHome RSS Fetcher/2.0)\r\n",
            "Accept: application/rss+xml, application/xml, text/xml\r\n",
            "Accept-Encoding: identity\r\n"
        ]
    ],
    'ssl' => [
        'verify_peer' => $sslVerify,
        'verify_peer_name' => $sslVerify,
        'allow_self_signed' => !$sslVerify
    ]
];

$context = stream_context_create($contextOptions);
$result = @file_get_contents($decodedUrl, false, $context);

if ($result === false) {
    $error = error_get_last();
    echo json_encode([
        'error' => 'Failed to fetch RSS feed',
        'message' => $error['message'] ?? 'Network error'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

libxml_use_internal_errors(true);
$xml = @simplexml_load_string($result);

if ($xml === false) {
    echo json_encode(['error' => 'Invalid RSS/XML format'], JSON_UNESCAPED_UNICODE);
    exit;
}

$items = [];
$count = min(50, max(1, intval($_GET['count'] ?? 10)));

if (isset($xml->channel)) {
    foreach ($xml->channel->item as $item) {
        if (count($items) >= $count) break;

        $description = trim(strip_tags((string)$item->description));
        if (function_exists('mb_substr')) {
            $description = mb_substr($description, 0, 200, 'UTF-8');
        } else {
            $description = substr($description, 0, 200);
        }

        $items[] = [
            'title' => trim((string)$item->title),
            'link' => trim((string)$item->link),
            'description' => $description,
            'pubDate' => trim((string)$item->pubDate),
            'author' => isset($item->{'dc:creator'}) ? trim((string)$item->{'dc:creator'}) : null
        ];
    }
} elseif (isset($xml->entry)) {
    foreach ($xml->entry as $entry) {
        if (count($items) >= $count) break;

        $content = isset($entry->content) ? (string)$entry->content : (string)$entry->summary;
        $description = trim(strip_tags($content));
        if (function_exists('mb_substr')) {
            $description = mb_substr($description, 0, 200, 'UTF-8');
        } else {
            $description = substr($description, 0, 200);
        }

        $link = '';
        if (isset($entry->link['href'])) {
            $link = (string)$entry->link['href'];
        } elseif (isset($entry->link)) {
            $attributes = $entry->link->attributes();
            $link = isset($attributes['href']) ? (string)$attributes['href'] : '';
        }

        $items[] = [
            'title' => trim((string)$entry->title),
            'link' => $link,
            'description' => $description,
            'pubDate' => isset($entry->updated) ? trim((string)$entry->updated) : (isset($entry->published) ? trim((string)$entry->published) : ''),
            'author' => isset($entry->author->name) ? trim((string)$entry->author->name) : null
        ];
    }
}

$response = [
    'items' => $items,
    'fetched' => date('c'),
    'source' => parse_url($decodedUrl, PHP_URL_HOST)
];

$jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
$jsonResponse = json_encode($response, $jsonOptions);

if ($jsonResponse !== false) {
    @file_put_contents($cacheFile, $jsonResponse, LOCK_EX);
}

echo $jsonResponse;
