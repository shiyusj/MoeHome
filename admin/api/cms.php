<?php
/**
 * MoeHome CMS API - 内容管理系统
 * 支持文章、项目、动态管理
 */

declare(strict_types=1);

require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => '未登录']);
    exit;
}

switch ($action) {
    // ==================== 文章管理 ====================
    case 'get_articles':
        getArticles();
        break;
    case 'get_article':
        getArticle();
        break;
    case 'save_article':
        saveArticle();
        break;
    case 'delete_article':
        deleteArticle();
        break;
    // ==================== 项目管理 ====================
    case 'get_projects':
        getProjects();
        break;
    case 'get_project':
        getProject();
        break;
    case 'save_project':
        saveProject();
        break;
    case 'delete_project':
        deleteProject();
        break;
    // ==================== 动态管理 ====================
    case 'get_moments':
        getMoments();
        break;
    case 'get_moment':
        getMoment();
        break;
    case 'save_moment':
        saveMoment();
        break;
    case 'delete_moment':
        deleteMoment();
        break;
    // ==================== 活跃度统计 ====================
    case 'get_activity':
        getActivity();
        break;
    default:
        echo json_encode(['error' => '无效操作']);
}

// ==================== 文章管理 ====================

function getArticles() {
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    $status = $_GET['status'] ?? '';

    $where = '';
    $params = [];
    if ($status) {
        $where = 'WHERE status = :status';
        $params[':status'] = $status;
    }

    $total = Database::fetchOne("SELECT COUNT(*) as count FROM moehome_articles {$where}", $params);
    $articles = Database::fetchAll("SELECT * FROM moehome_articles {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset", 
        array_merge($params, [':limit' => $limit, ':offset' => $offset]));

    echo json_encode([
        'articles' => $articles,
        'total' => $total['count'] ?? 0,
        'page' => $page
    ]);
}

function getArticle() {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => '无效ID']);
        return;
    }

    $article = Database::fetchOne("SELECT * FROM moehome_articles WHERE id = :id", [':id' => $id]);
    echo json_encode($article ?: ['error' => '文章不存在']);
}

function saveArticle() {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $required = ['title', 'content'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['error' => "{$field} 必填"]);
            return;
        }
    }

    $article = [
        'title' => trim($data['title']),
        'slug' => trim($data['slug'] ?: slugify($data['title'])),
        'excerpt' => trim($data['excerpt'] ?? ''),
        'content' => $data['content'],
        'cover' => trim($data['cover'] ?? ''),
        'tags' => trim($data['tags'] ?? ''),
        'status' => $data['status'] ?? 'draft',
        'is_featured' => $data['is_featured'] ?? 0
    ];

    if (!empty($data['id'])) {
        $article['updated_at'] = date('Y-m-d H:i:s');
        Database::update('moehome_articles', $article, 'id = :id', [':id' => $data['id']]);
        $id = $data['id'];
    } else {
        $article['created_at'] = date('Y-m-d H:i:s');
        $id = Database::insert('moehome_articles', $article);
    }

    echo json_encode(['success' => true, 'id' => $id]);
}

function deleteArticle() {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => '无效ID']);
        return;
    }

    Database::delete('moehome_articles', 'id = :id', [':id' => $id]);
    echo json_encode(['success' => true]);
}

// ==================== 项目管理 ====================

function getProjects() {
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;

    $total = Database::fetchOne("SELECT COUNT(*) as count FROM moehome_projects");
    $projects = Database::fetchAll("SELECT * FROM moehome_projects ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset", 
        [':limit' => $limit, ':offset' => $offset]);

    echo json_encode([
        'projects' => $projects,
        'total' => $total['count'] ?? 0,
        'page' => $page
    ]);
}

function getProject() {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => '无效ID']);
        return;
    }

    $project = Database::fetchOne("SELECT * FROM moehome_projects WHERE id = :id", [':id' => $id]);
    echo json_encode($project ?: ['error' => '项目不存在']);
}

function saveProject() {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (empty($data['name'])) {
        echo json_encode(['error' => '项目名称必填']);
        return;
    }

    $project = [
        'name' => trim($data['name']),
        'slug' => trim($data['slug'] ?: slugify($data['name'])),
        'description' => trim($data['description'] ?? ''),
        'cover' => trim($data['cover'] ?? ''),
        'url' => trim($data['url'] ?? ''),
        'language' => trim($data['language'] ?? ''),
        'stars' => intval($data['stars'] ?? 0),
        'forks' => intval($data['forks'] ?? 0),
        'status' => $data['status'] ?? 'active',
        'sort_order' => intval($data['sort_order'] ?? 0),
        'is_main' => $data['is_main'] ?? 0
    ];

    if (!empty($data['id'])) {
        $project['updated_at'] = date('Y-m-d H:i:s');
        Database::update('moehome_projects', $project, 'id = :id', [':id' => $data['id']]);
        $id = $data['id'];
    } else {
        $project['created_at'] = date('Y-m-d H:i:s');
        $id = Database::insert('moehome_projects', $project);
    }

    echo json_encode(['success' => true, 'id' => $id]);
}

function deleteProject() {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => '无效ID']);
        return;
    }

    Database::delete('moehome_projects', 'id = :id', [':id' => $id]);
    echo json_encode(['success' => true]);
}

// ==================== 动态管理 ====================

function getMoments() {
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;
    $tag = $_GET['tag'] ?? '';

    $where = '';
    $params = [];
    if ($tag) {
        $where = "WHERE tags LIKE :tag";
        $params[':tag'] = "%{$tag}%";
    }

    $total = Database::fetchOne("SELECT COUNT(*) as count FROM moehome_moments {$where}", $params);
    $moments = Database::fetchAll("SELECT * FROM moehome_moments {$where} ORDER BY is_pinned DESC, created_at DESC LIMIT :limit OFFSET :offset", 
        array_merge($params, [':limit' => $limit, ':offset' => $offset]));

    echo json_encode([
        'moments' => $moments,
        'total' => $total['count'] ?? 0,
        'page' => $page
    ]);
}

function getMoment() {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => '无效ID']);
        return;
    }

    $moment = Database::fetchOne("SELECT * FROM moehome_moments WHERE id = :id", [':id' => $id]);
    echo json_encode($moment ?: ['error' => '动态不存在']);
}

function saveMoment() {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (empty($data['content'])) {
        echo json_encode(['error' => '内容必填']);
        return;
    }

    $moment = [
        'content' => trim($data['content']),
        'tags' => trim($data['tags'] ?? ''),
        'is_pinned' => $data['is_pinned'] ?? 0,
        'status' => $data['status'] ?? 'published'
    ];

    if (!empty($data['id'])) {
        $moment['updated_at'] = date('Y-m-d H:i:s');
        Database::update('moehome_moments', $moment, 'id = :id', [':id' => $data['id']]);
        $id = $data['id'];
    } else {
        $moment['created_at'] = date('Y-m-d H:i:s');
        $id = Database::insert('moehome_moments', $moment);
    }

    echo json_encode(['success' => true, 'id' => $id]);
}

function deleteMoment() {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['error' => '无效ID']);
        return;
    }

    Database::delete('moehome_moments', 'id = :id', [':id' => $id]);
    echo json_encode(['success' => true]);
}

// ==================== 活跃度统计 ====================

function getActivity() {
    $days = intval($_GET['days'] ?? 365);
    $startDate = date('Y-m-d', strtotime("-{$days} days"));

    $articleActivity = Database::fetchAll("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM moehome_articles 
        WHERE created_at >= :startDate AND status = 'published'
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ", [':startDate' => $startDate]);

    $momentActivity = Database::fetchAll("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM moehome_moments 
        WHERE created_at >= :startDate AND status = 'published'
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ", [':startDate' => $startDate]);

    $projectActivity = Database::fetchAll("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM moehome_projects 
        WHERE created_at >= :startDate
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ", [':startDate' => $startDate]);

    echo json_encode([
        'articles' => $articleActivity,
        'moments' => $momentActivity,
        'projects' => $projectActivity,
        'days' => $days
    ]);
}

function slugify(string $text): string {
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text ?: 'post';
}
