<?php
declare(strict_types=1);

$config = [];
$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cacheFile = __DIR__ . '/cache/posts.json';
$cacheExpiry = 3600;

$count = intval($_GET['count'] ?? 4);

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
    $posts = json_decode(file_get_contents($cacheFile), true);
    if ($posts) {
        echo json_encode(array_slice($posts, 0, $count));
        exit;
    }
}

$posts = [];

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', 
        $config['database']['host'] ?? 'localhost', 
        $config['database']['name'] ?? 'moehome');
    
    $pdo = new PDO($dsn, 
        $config['database']['username'] ?? 'root', 
        $config['database']['password'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->prepare("SELECT id, title, slug, excerpt, category, tags, created_at FROM moehome_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT :limit");
    $stmt->execute([':limit' => $count]);
    $posts = $stmt->fetchAll();

    foreach ($posts as &$post) {
        $post['tags'] = json_decode($post['tags'], true) ?? [];
        $post['date'] = date('Y-m-d', strtotime($post['created_at']));
        unset($post['created_at']);
    }
} catch (Exception $e) {
}

@file_put_contents($cacheFile, json_encode($posts));

echo json_encode($posts);
