<?php
/**
 * MoeHome API - 哔哩哔哩代理
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, max-age=600');

$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$bilibili = $config['bilibili'] ?? [];

if (!($bilibili['enabled'] ?? false)) {
    http_response_code(403);
    echo json_encode(['error' => 'Bilibili module is disabled']);
    exit;
}

$uid = $_GET['uid'] ?? ($bilibili['uid'] ?? '');
$count = min(12, max(1, intval($_GET['count'] ?? ($bilibili['count'] ?? 4))));

if (empty($uid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Bilibili UID is required']);
    exit;
}

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . '/bilibili_' . md5($uid . $count) . '.json';
$cacheTime = 1800;

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$result = fetchBilibiliData($uid, $count);

$json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($json !== false) {
    @file_put_contents($cacheFile, $json, LOCK_EX);
}

echo $json;

function fetchBilibiliData(string $uid, int $count): array {
    $userInfo = fetchBilibiliUser($uid);

    if (empty($userInfo)) {
        return [
            'videos' => [],
            'user' => ['uid' => $uid],
            'error' => 'Failed to fetch user info'
        ];
    }

    $videos = fetchBilibiliVideos($uid, $count);

    return [
        'videos' => $videos,
        'user' => [
            'uid' => $userInfo['mid'] ?? $uid,
            'name' => $userInfo['name'] ?? 'Unknown',
            'face' => $userInfo['face'] ?? '',
            'sign' => $userInfo['sign'] ?? '',
            'fans' => $userInfo['fans'] ?? 0,
            'likes' => $userInfo['like'] ?? 0
        ],
        'count' => count($videos)
    ];
}

function fetchBilibiliUser(string $uid): array {
    $apiUrl = "https://api.bilibili.com/x/web-interface/card?mid={$uid}&photo=true";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; MoeHome/3.0)',
                'Accept: application/json',
                'Referer: https://www.bilibili.com/'
            ],
            'timeout' => 10
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);

    if (!isset($data['data']['card']) || !is_array($data['data']['card'])) {
        return [];
    }

    $card = $data['data']['card'];

    return [
        'mid' => $card['mid'] ?? $uid,
        'name' => $card['name'] ?? '',
        'face' => $card['face'] ?? '',
        'sign' => $card['sign'] ?? '',
        'fans' => $card['fans'] ?? 0,
        'like' => $card['like'] ?? 0
    ];
}

function fetchBilibiliVideos(string $uid, int $count): array {
    $apiUrl = "https://api.bilibili.com/x/space/wbi/arc/search?mid={$uid}&pn=1&ps={$count}&jsonp=jsonp";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; MoeHome/3.0)',
                'Accept: application/json',
                'Referer: https://www.bilibili.com/'
            ],
            'timeout' => 10
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);

    if (!isset($data['data']['list']['vlist']) || !is_array($data['data']['list']['vlist'])) {
        return [];
    }

    $videos = [];

    foreach (array_slice($data['data']['list']['vlist'], 0, $count) as $video) {
        $videos[] = [
            'bvid' => $video['bvid'] ?? '',
            'aid' => $video['aid'] ?? 0,
            'title' => $video['title'] ?? 'Untitled',
            'description' => $video['description'] ?? '',
            'pic' => $video['pic'] ?? '',
            'picCdn' => str_replace('http://', 'https://', $video['pic'] ?? ''),
            'duration' => formatDuration($video['duration'] ?? 0),
            'author' => $video['author'] ?? '',
            'view' => formatNumber($video['play'] ?? 0),
            'danmu' => formatNumber($video['video_review'] ?? 0),
            'publish' => formatDate($video['created'] ?? 0),
            'url' => "https://www.bilibili.com/video/{$video['bvid']}"
        ];
    }

    return $videos;
}

function formatDuration(int $seconds): string {
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%d:%02d', $minutes, $secs);
}

function formatNumber(int $num): string {
    if ($num >= 100000000) {
        return round($num / 100000000, 1) . '亿';
    }
    if ($num >= 10000) {
        return round($num / 10000, 1) . '万';
    }
    return (string)$num;
}

function formatDate(int $timestamp): string {
    if ($timestamp == 0) return '';
    return date('Y-m-d', $timestamp);
}
