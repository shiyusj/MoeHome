<?php
/**
 * RSS 代理 API
 * 解决虚拟主机 allow_url_fopen 限制问题
 * 使用方法: /api/rss.php?url=编码后的RSS地址
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

$config = [];
$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    echo json_encode(['error' => 'URL parameter required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$decodedUrl = urldecode($url);
$cacheKey = 'rss_' . md5($decodedUrl);
$cacheFile = __DIR__ . '/cache/' . $cacheKey . '.json';
$cacheTime = isset($config['api']['rss_cache']) ? $config['api']['rss_cache'] : 3600;

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
        'timeout' => 10,
        'ignore_errors' => true,
        'header' => "User-Agent: Mozilla/5.0 (compatible; MoeHome RSS Fetcher/1.0)\r\n"
    ],
    'ssl' => [
        'verify_peer' => $sslVerify,
        'verify_peer_name' => $sslVerify
    ]
]);

$result = @file_get_contents($decodedUrl, false, $context);

if ($result === false) {
    echo json_encode(['error' => 'Failed to fetch RSS feed'], JSON_UNESCAPED_UNICODE);
    exit;
}

libxml_use_internal_errors(true);
$xml = simplexml_load_string($result);

if ($xml === false) {
    echo json_encode(['error' => 'Invalid RSS/XML format'], JSON_UNESCAPED_UNICODE);
    exit;
}

$items = [];
$count = isset($_GET['count']) ? intval($_GET['count']) : 10;

if (isset($xml->channel)) {
    foreach ($xml->channel->item as $item) {
        if (count($items) >= $count) break;

        $itemData = [
            'title' => (string)$item->title,
            'link' => (string)$item->link,
            'description' => strip_tags((string)$item->description),
            'pubDate' => (string)$item->pubDate,
        ];

        if (isset($item->{'dc:creator'})) {
            $itemData['author'] = (string)$item->{'dc:creator'};
        }

        $items[] = $itemData;
    }
} elseif (isset($xml->entry)) {
    foreach ($xml->entry as $entry) {
        if (count($items) >= $count) break;

        $itemData = [
            'title' => (string)$entry->title,
            'link' => (string)$entry->link['href'],
            'description' => strip_tags((string)$entry->content),
            'pubDate' => (string)$entry->updated,
        ];

        $items[] = $itemData;
    }
}

$response = [
    'items' => $items,
    'fetched' => date('c')
];

$jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@file_put_contents($cacheFile, $jsonResponse);

echo $jsonResponse;
