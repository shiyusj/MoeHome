<?php
/**
 * MoeHome API - 图库代理 (花瓣网/本地)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, max-age=300');

$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$gallery = $config['gallery'] ?? [];

if (!($gallery['enabled'] ?? false)) {
    http_response_code(403);
    echo json_encode(['error' => 'Gallery module is disabled']);
    exit;
}

$source = $_GET['source'] ?? ($gallery['source'] ?? 'local');
$count = min(20, max(1, intval($_GET['count'] ?? ($gallery['count'] ?? 8))));

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . '/gallery_' . md5($source . $count) . '.json';
$cacheTime = 3600;

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$result = [];

switch ($source) {
    case 'huaban':
        $result = fetchHuabanPhotos($gallery, $count);
        break;

    case 'local':
    default:
        $result = fetchLocalPhotos($gallery, $count);
        break;
}

$json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($json !== false) {
    @file_put_contents($cacheFile, $json, LOCK_EX);
}

echo $json;

function fetchLocalPhotos(array $gallery, int $count): array {
    $localDir = isset($gallery['local']['directory'])
        ? rtrim($gallery['local']['directory'], '/') . '/'
        : 'images/gallery/';

    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!is_dir($localDir)) {
        return ['photos' => [], 'source' => 'local'];
    }

    $files = [];
    $handle = opendir($localDir);

    if ($handle) {
        while (($file = readdir($handle)) !== false) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExt)) {
                $files[] = [
                    'id' => md5($file),
                    'src' => $localDir . $file,
                    'thumbnail' => $localDir . $file,
                    'alt' => pathinfo($file, PATHINFO_FILENAME)
                ];
            }
        }
        closedir($handle);
    }

    usort($files, fn() => mt_rand(-1, 1));

    return [
        'photos' => array_slice($files, 0, $count),
        'source' => 'local',
        'count' => count($files)
    ];
}

function fetchHuabanPhotos(array $gallery, int $count): array {
    $boardId = $gallery['huaban']['boardId'] ?? '';

    if (empty($boardId)) {
        return ['photos' => [], 'source' => 'huaban', 'error' => 'Board ID not configured'];
    }

    $apiUrl = "https://api.huaban.com/boards/{$boardId}/pins?limit={$count}&ref";

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; MoeHome/3.0)',
                'Accept: application/json',
                'Referer: https://huaban.com/'
            ],
            'timeout' => 10
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        return ['photos' => [], 'source' => 'huaban', 'error' => 'Failed to fetch'];
    }

    $data = json_decode($response, true);

    if (!isset($data['pins']) || !is_array($data['pins'])) {
        return ['photos' => [], 'source' => 'huaban', 'error' => 'Invalid response'];
    }

    $photos = [];
    foreach (array_slice($data['pins'], 0, $count) as $pin) {
        $key = $pin['file']['key'] ?? '';
        if (empty($key)) continue;

        $photos[] = [
            'id' => $pin['pin_id'] ?? $key,
            'src' => "https://gd.hbstatic.com/pins/{$key}.jpg",
            'thumbnail' => "https://gd.hbstatic.com/thumbnails/{$key}_fw658.jpg",
            'alt' => $pin['raw_text'] ?? '',
            'link' => "https://huaban.com/pins/{$pin['pin_id']}"
        ];
    }

    return [
        'photos' => $photos,
        'source' => 'huaban',
        'count' => count($photos)
    ];
}
