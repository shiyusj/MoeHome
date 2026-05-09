<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=3600, stale-while-revalidate=86400');

$config = [];
$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
$category = isset($_GET['category']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', trim($_GET['category'])) : '';

$offset = ($page - 1) * $limit;

$posts = [];
$total = 0;

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
        $config['database']['host'] ?? 'localhost',
        $config['database']['name'] ?? 'moehome');

    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    $pdo = new PDO($dsn,
        $config['database']['username'] ?? 'root',
        $config['database']['password'] ?? '',
        $pdoOptions);

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
    $posts = $stmt->fetchAll() ?: [];

    foreach ($posts as &$post) {
        $post['tags'] = json_decode($post['tags'] ?? '[]', true) ?: [];
        $post['date'] = date('Y-m-d', strtotime($post['created_at'] ?? 'now'));
        unset($post['created_at']);
    }
} catch (Exception $e) {
    http_response_code(503);
}

$response = [
    'posts' => $posts,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'totalPages' => $total > 0 ? ceil($total / $limit) : 1
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
