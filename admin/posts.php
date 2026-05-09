<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
require_once __DIR__ . '/api/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

requireLogin();

$csrfToken = getCsrfToken();
$message = '';
$messageType = '';

$action = $_GET['action'] ?? 'list';
$editPost = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $editPost = Database::fetchOne(
        "SELECT * FROM moehome_posts WHERE id = :id",
        [':id' => (int)$_GET['id']]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    if (isset($_POST['save_post'])) {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'category' => trim($_POST['category'] ?? 'default'),
            'tags' => trim($_POST['tags'] ?? ''),
            'status' => $_POST['status'] ?? 'draft',
        ];

        if (empty($data['title']) || empty($data['slug']) || empty($data['content'])) {
            $message = '请填写必填字段';
            $messageType = 'error';
        } else {
            if (isset($_POST['post_id']) && $_POST['post_id']) {
                Database::update('moehome_posts', [
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'content' => $data['content'],
                    'excerpt' => $data['excerpt'],
                    'category' => $data['category'],
                    'tags' => json_encode(array_filter(array_map('trim', explode(',', $data['tags'])))),
                    'status' => $data['status'],
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = :id', [':id' => (int)$_POST['post_id']]);
                $message = '文章已更新';
            } else {
                Database::insert('moehome_posts', [
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'content' => $data['content'],
                    'excerpt' => $data['excerpt'],
                    'category' => $data['category'],
                    'tags' => json_encode(array_filter(array_map('trim', explode(',', $data['tags'])))),
                    'status' => $data['status'],
                    'author_id' => $_SESSION['admin_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $message = '文章已创建';
            }
            $messageType = 'success';
            $action = 'list';
            $editPost = null;
        }
    } elseif (isset($_POST['delete_post'])) {
        Database::delete('moehome_posts', 'id = :id', [':id' => (int)$_POST['post_id']]);
        $message = '文章已删除';
        $messageType = 'success';
    }
}

$posts = Database::fetchAll("SELECT * FROM moehome_posts ORDER BY created_at DESC");

function formatTags(string $tags): string {
    $arr = json_decode($tags, true) ?? [];
    return implode(', ', $arr);
}
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>文章管理 - MoeHome 管理后台</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/admin.css" />
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <h1 class="topbar-title">
                    <i class="fas fa-file-alt"></i>
                    文章管理
                </h1>
                <div class="topbar-actions">
                    <a href="posts.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        写文章
                    </a>
                    <button class="btn btn-secondary btn-sm" data-theme-toggle>
                        <i class="fas fa-adjust"></i>
                    </button>
                </div>
            </header>

            <div class="page-content">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo h($messageType); ?> fade-in">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo h($message); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                <div class="card fade-in">
                    <div class="card-header">
                        <h2 class="card-title">文章列表</h2>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>标题</th>
                                    <th>分类</th>
                                    <th>标签</th>
                                    <th>状态</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-secondary);">
                                        <i class="fas fa-file-text" style="font-size: 2rem; opacity: 0.5; display: block; margin-bottom: 8px;"></i>
                                        暂无文章，点击右上角写文章
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <a href="posts.php?action=edit&id=<?php echo h((string)$post['id']); ?>" style="color: var(--text-primary);">
                                            <?php echo h($post['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo h($post['category']); ?></td>
                                    <td><?php echo h(formatTags($post['tags'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo $post['status'] === 'published' ? '已发布' : '草稿'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo h($post['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="posts.php?action=edit&id=<?php echo h((string)$post['id']); ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('确定删除这篇文章吗？');">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                                                <input type="hidden" name="post_id" value="<?php echo h((string)$post['id']); ?>" />
                                                <input type="hidden" name="delete_post" value="1" />
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php else: ?>
                <div class="card fade-in">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-pencil-alt"></i>
                            <?php echo $editPost ? '编辑文章' : '写文章'; ?>
                        </h2>
                    </div>

                    <form method="POST" action="" id="postForm">
                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                        <input type="hidden" name="save_post" value="1" />
                        <?php if ($editPost): ?>
                        <input type="hidden" name="post_id" value="<?php echo h((string)$editPost['id']); ?>" />
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group" style="flex: 2;">
                                <label class="form-label" for="title">文章标题 <span style="color: #ff4757;">*</span></label>
                                <input type="text" id="title" name="title" class="form-input" 
                                    value="<?php echo h($editPost['title'] ?? ''); ?>" 
                                    placeholder="请输入文章标题" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="slug">文章别名 <span style="color: #ff4757;">*</span></label>
                                <input type="text" id="slug" name="slug" class="form-input" 
                                    value="<?php echo h($editPost['slug'] ?? ''); ?>" 
                                    placeholder="用于 URL 的别名" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="category">分类</label>
                                <select id="category" name="category" class="form-select">
                                    <option value="default" <?php echo ($editPost['category'] ?? '') === 'default' ? 'selected' : ''; ?>>默认分类</option>
                                    <option value="tech" <?php echo ($editPost['category'] ?? '') === 'tech' ? 'selected' : ''; ?>>技术文章</option>
                                    <option value="life" <?php echo ($editPost['category'] ?? '') === 'life' ? 'selected' : ''; ?>>生活随笔</option>
                                    <option value="project" <?php echo ($editPost['category'] ?? '') === 'project' ? 'selected' : ''; ?>>项目分享</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="tags">标签</label>
                                <input type="text" id="tags" name="tags" class="form-input" 
                                    value="<?php echo h($editPost ? formatTags($editPost['tags']) : ''); ?>" 
                                    placeholder="多个标签用英文逗号分隔" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="status">状态</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="draft" <?php echo ($editPost['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>草稿</option>
                                    <option value="published" <?php echo ($editPost['status'] ?? '') === 'published' ? 'selected' : ''; ?>>已发布</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="excerpt">摘要</label>
                            <textarea id="excerpt" name="excerpt" class="form-textarea" rows="3" 
                                placeholder="文章摘要（可选）"><?php echo h($editPost['excerpt'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="content">文章内容 <span style="color: #ff4757;">*</span></label>
                            <textarea id="content" name="content" class="form-textarea" rows="15" 
                                placeholder="请输入文章内容（支持 Markdown）"><?php echo h($editPost['content'] ?? ''); ?></textarea>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                            <a href="posts.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                返回列表
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $editPost ? '更新文章' : '保存文章'; ?>
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>