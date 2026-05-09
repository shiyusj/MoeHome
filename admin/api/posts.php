<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listPosts();
        break;
    case 'get':
        getPost();
        break;
    case 'create':
        createPost();
        break;
    case 'update':
        updatePost();
        break;
    case 'delete':
        deletePost();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function listPosts(): void {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    $status = $_GET['status'] ?? '';

    $where = '';
    $params = [];
    
    if ($status) {
        $where = 'WHERE status = :status';
        $params[':status'] = $status;
    }

    $sql = "SELECT * FROM moehome_posts {$where} ORDER BY created_at DESC LIMIT :offset, :limit";
    $posts = Database::fetchAll($sql, array_merge($params, [':offset' => $offset, ':limit' => $limit]));

    $countSql = "SELECT COUNT(*) as total FROM moehome_posts {$where}";
    $count = Database::fetchOne($countSql, $params);

    echo json_encode([
        'posts' => $posts,
        'total' => $count['total'] ?? 0,
        'page' => $page,
        'pages' => (int)ceil(($count['total'] ?? 0) / $limit)
    ]);
}

function getPost(): void {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing post ID']);
        return;
    }

    $post = Database::fetchOne(
        "SELECT * FROM moehome_posts WHERE id = :id",
        [':id' => $id]
    );

    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        return;
    }

    echo json_encode($post);
}

function createPost(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $required = ['title', 'content', 'slug'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: {$field}"]);
            return;
        }
    }

    $postId = Database::insert('moehome_posts', [
        'title' => trim($data['title']),
        'slug' => trim($data['slug']),
        'content' => trim($data['content']),
        'excerpt' => trim($data['excerpt'] ?? ''),
        'category' => trim($data['category'] ?? 'default'),
        'tags' => json_encode(explode(',', trim($data['tags'] ?? ''))),
        'status' => $data['status'] ?? 'draft',
        'author_id' => $_SESSION['admin_id'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    echo json_encode(['success' => true, 'post_id' => $postId]);
}

function updatePost(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing post ID']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $updateData = [];
    if (isset($data['title'])) $updateData['title'] = trim($data['title']);
    if (isset($data['slug'])) $updateData['slug'] = trim($data['slug']);
    if (isset($data['content'])) $updateData['content'] = trim($data['content']);
    if (isset($data['excerpt'])) $updateData['excerpt'] = trim($data['excerpt']);
    if (isset($data['category'])) $updateData['category'] = trim($data['category']);
    if (isset($data['tags'])) $updateData['tags'] = json_encode(explode(',', trim($data['tags'])));
    if (isset($data['status'])) $updateData['status'] = $data['status'];
    $updateData['updated_at'] = date('Y-m-d H:i:s');

    Database::update('moehome_posts', $updateData, 'id = :id', [':id' => $id]);

    echo json_encode(['success' => true]);
}

function deletePost(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing post ID']);
        return;
    }

    Database::delete('moehome_posts', 'id = :id', [':id' => $id]);

    echo json_encode(['success' => true]);
}
