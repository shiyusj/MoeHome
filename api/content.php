<?php
/**
 * MoeHome 前台 CMS API
 * 提供文章、项目、动态数据
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, max-age=300');

require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

switch ($action) {
    case 'articles':
        getArticles();
        break;
    case 'projects':
        getProjects();
        break;
    case 'moments':
        getMoments();
        break;
    case 'activity':
        getActivity();
        break;
    default:
        echo json_encode(['error' => '无效操作']);
}

function getArticles() {
    global $cacheDir;
    
    $count = min(10, max(1, intval($_GET['count'] ?? 4)));
    $featured = $_GET['featured'] ?? '';
    
    $cacheKey = "articles_{$count}_{$featured}";
    $cacheFile = $cacheDir . '/' . md5($cacheKey) . '.json';
    
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        echo file_get_contents($cacheFile);
        return;
    }

    $where = "status = 'published'";
    if ($featured) {
        $where .= " AND is_featured = 1";
    }

    $articles = [];
    $dbFile = __DIR__ . '/../admin/install.php';
    if (is_file($dbFile)) {
        $pdo = getPdo();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM moehome_articles WHERE {$where} ORDER BY created_at DESC LIMIT :limit");
            $stmt->execute([':limit' => $count]);
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    foreach ($articles as &$article) {
        $article['excerpt'] = $article['excerpt'] ?: mb_substr(strip_tags($article['content']), 0, 200);
        $article['tags'] = !empty($article['tags']) ? explode(',', $article['tags']) : [];
        $article['url'] = "article/{$article['slug']}.html";
    }

    $result = ['articles' => $articles, 'count' => count($articles)];
    $json = json_encode($result, JSON_UNESCAPED_UNICODE);
    @file_put_contents($cacheFile, $json, LOCK_EX);
    echo $json;
}

function getProjects() {
    global $cacheDir;
    
    $count = min(10, max(1, intval($_GET['count'] ?? 5)));
    $mainOnly = $_GET['main'] ?? '';
    
    $cacheKey = "projects_{$count}_{$mainOnly}";
    $cacheFile = $cacheDir . '/' . md5($cacheKey) . '.json';
    
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        echo file_get_contents($cacheFile);
        return;
    }

    $where = '';
    if ($mainOnly) {
        $where = "WHERE is_main = 1";
    }

    $projects = [];
    $pdo = getPdo();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM moehome_projects {$where} ORDER BY sort_order ASC, created_at DESC LIMIT :limit");
        $stmt->execute([':limit' => $count]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $result = ['projects' => $projects, 'count' => count($projects)];
    $json = json_encode($result, JSON_UNESCAPED_UNICODE);
    @file_put_contents($cacheFile, $json, LOCK_EX);
    echo $json;
}

function getMoments() {
    global $cacheDir;
    
    $count = min(20, max(1, intval($_GET['count'] ?? 10)));
    $tag = $_GET['tag'] ?? '';
    
    $cacheKey = "moments_{$count}_{$tag}";
    $cacheFile = $cacheDir . '/' . md5($cacheKey) . '.json';
    
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
        echo file_get_contents($cacheFile);
        return;
    }

    $where = "status = 'published'";
    $params = [];
    if ($tag) {
        $where .= " AND tags LIKE :tag";
        $params[':tag'] = "%{$tag}%";
    }

    $moments = [];
    $pdo = getPdo();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM moehome_moments WHERE {$where} ORDER BY is_pinned DESC, created_at DESC LIMIT :limit");
        $params[':limit'] = $count;
        $stmt->execute($params);
        $moments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($moments as &$moment) {
        $moment['tags'] = !empty($moment['tags']) ? array_map('trim', explode(',', $moment['tags'])) : [];
        $moment['created_at'] = formatDate($moment['created_at']);
    }

    $result = ['moments' => $moments, 'count' => count($moments)];
    $json = json_encode($result, JSON_UNESCAPED_UNICODE);
    @file_put_contents($cacheFile, $json, LOCK_EX);
    echo $json;
}

function getActivity() {
    global $cacheDir;
    
    $days = intval($_GET['days'] ?? 14);
    
    $cacheKey = "activity_{$days}";
    $cacheFile = $cacheDir . '/' . md5($cacheKey) . '.json';
    
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        echo file_get_contents($cacheFile);
        return;
    }

    $pdo = getPdo();
    $activity = [];
    
    if ($pdo) {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM moehome_articles WHERE created_at >= :startDate AND status = 'published'");
        $stmt->execute([':startDate' => $startDate]);
        $articleCount = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM moehome_moments WHERE created_at >= :startDate AND status = 'published'");
        $stmt->execute([':startDate' => $startDate]);
        $momentCount = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM moehome_projects WHERE created_at >= :startDate");
        $stmt->execute([':startDate' => $startDate]);
        $projectCount = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT DATE(created_at) as date, 'article' as type FROM moehome_articles WHERE created_at >= :startDate AND status = 'published' UNION SELECT DATE(created_at), 'moment' FROM moehome_moments WHERE created_at >= :startDate AND status = 'published' UNION SELECT DATE(created_at), 'project' FROM moehome_projects WHERE created_at >= :startDate ORDER BY date DESC");
        $stmt->execute([':startDate' => $startDate]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activity = [
            'articles' => (int)$articleCount,
            'moments' => (int)$momentCount,
            'projects' => (int)$projectCount,
            'total' => (int)$articleCount + (int)$momentCount + (int)$projectCount,
            'history' => $history
        ];
    }

    $json = json_encode($activity, JSON_UNESCAPED_UNICODE);
    @file_put_contents($cacheFile, $json, LOCK_EX);
    echo $json;
}

function getPdo() {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }

    $dbConfig = [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'moehome',
        'user' => 'root',
        'pass' => '',
        'prefix' => 'moehome_'
    ];

    $configFile = __DIR__ . '/config.php';
    if (is_file($configFile)) {
        $config = [];
        require $configFile;
        if (!empty($config['database'])) {
            $dbConfig = array_merge($dbConfig, $config['database']);
        }
    }

    try {
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (Exception $e) {
        return null;
    }
}

function formatDate(string $date): string {
    $timestamp = strtotime($date);
    if (!$timestamp) return $date;
    
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    if ($diff < 604800) return floor($diff / 86400) . '天前';
    
    return date('Y年m月d日', $timestamp);
}
