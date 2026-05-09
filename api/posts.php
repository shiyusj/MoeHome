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

$page = intval($_GET['page'] ?? 1);
$limit = intval($_GET['limit'] ?? 4);
$category = trim($_GET['category'] ?? '');

$offset = ($page - 1) * $limit;

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
    $posts = json_decode(file_get_contents($cacheFile), true);
    if ($posts) {
        if (!empty($category)) {
            $posts = array_filter($posts, function($post) use ($category) {
                return $post['category'] === $category;
            });
            $posts = array_values($posts);
        }
        $total = count($posts);
        $posts = array_slice($posts, $offset, $limit);
        echo json_encode(['posts' => $posts, 'total' => $total]);
        exit;
    }
}

$posts = [];
$total = 0;

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

    $whereClause = 'status = "published"';
    $params = [];

    if (!empty($category)) {
        $whereClause .= ' AND category = :category';
        $params[':category'] = $category;
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM moehome_posts WHERE {$whereClause}");
    $countStmt->execute($params);
    $countResult = $countStmt->fetch();
    $total = intval($countResult['total'] ?? 0);

    $stmt = $pdo->prepare("SELECT id, title, slug, excerpt, content, category, tags, created_at FROM moehome_posts WHERE {$whereClause} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->execute(array_merge($params, [':limit' => $limit, ':offset' => $offset]));
    $posts = $stmt->fetchAll();

    foreach ($posts as &$post) {
        $post['tags'] = json_decode($post['tags'], true) ?? [];
        $post['date'] = date('Y-m-d', strtotime($post['created_at']));
        unset($post['created_at']);
    }
} catch (Exception $e) {
}

@file_put_contents($cacheFile, json_encode($posts));

echo json_encode(['posts' => $posts, 'total' => $total]);
