<?php
/**
 * MoeHome API - 书架代理 (豆瓣读书)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, max-age=3600');

$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$books = $config['books'] ?? [];

if (!($books['enabled'] ?? false)) {
    http_response_code(403);
    echo json_encode(['error' => 'Books module is disabled']);
    exit;
}

$count = min(12, max(1, intval($_GET['count'] ?? ($books['count'] ?? 6))));
$userId = $_GET['user_id'] ?? ($books['doubanId'] ?? '');

if (empty($userId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Douban user ID is required']);
    exit;
}

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . '/books_' . md5($userId . $count) . '.json';
$cacheTime = 7200;

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$result = fetchDoubanBooks($userId, $count);

$json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($json !== false) {
    @file_put_contents($cacheFile, $json, LOCK_EX);
}

echo $json;

function fetchDoubanBooks(string $userId, int $count): array {
    $apiUrl = "https://api.douban.com/v2/book/user/{$userId}/collections";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; MoeHome/3.0)',
                'Accept: application/json'
            ],
            'timeout' => 15
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return [
            'books' => [],
            'user' => $userId,
            'count' => 0,
            'error' => 'Failed to fetch from Douban'
        ];
    }

    $data = json_decode($response, true);

    if (isset($data['msg']) && strpos($data['msg'], 'invalid') !== false) {
        return [
            'books' => [],
            'user' => $userId,
            'count' => 0,
            'error' => 'Invalid user ID'
        ];
    }

    if (!isset($data['collections']) || !is_array($data['collections'])) {
        return [
            'books' => [],
            'user' => $userId,
            'count' => 0,
            'error' => 'No collections found'
        ];
    }

    $books = [];
    $maxRating = 5;

    foreach (array_slice($data['collections'], 0, $count) as $item) {
        if (!isset($item['book'])) continue;

        $book = $item['book'];
        $rating = $item['rating']['value'] ?? 0;

        $books[] = [
            'id' => $book['id'] ?? '',
            'title' => $book['title'] ?? 'Unknown',
            'author' => is_array($book['author']) ? implode(', ', array_slice($book['author'], 0, 2)) : ($book['author'] ?? ''),
            'cover' => $book['image'] ?? '',
            'url' => $book['alt'] ?? '',
            'rating' => $rating,
            'maxRating' => $maxRating,
            'ratingStars' => str_repeat('★', (int)$rating) . str_repeat('☆', $maxRating - (int)$rating),
            'status' => $item['status'] ?? 'read',
            'tags' => is_array($item['tags']) ? array_slice($item['tags'], 0, 3) : []
        ];
    }

    return [
        'books' => $books,
        'user' => $userId,
        'count' => count($books),
        'total' => $data['total'] ?? 0
    ];
}
