<?php
/**
 * MoeHome 后台 - 文章管理
 */

require_once __DIR__ . '/api/database.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$config = getConfig();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章管理 - MoeHome 后台</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php require_once 'partials/header.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>文章管理</h1>
            <button class="btn btn-primary" onclick="showArticleModal()">
                <i class="fas fa-plus"></i> 新建文章
            </button>
        </div>

        <div class="page-actions">
            <div class="filter-group">
                <select id="status-filter" onchange="loadArticles()">
                    <option value="">全部状态</option>
                    <option value="published">已发布</option>
                    <option value="draft">草稿</option>
                </select>
            </div>
            <div class="search-group">
                <input type="text" id="search-input" placeholder="搜索文章..." onkeyup="debounceSearch()">
            </div>
        </div>

        <div class="table-container">
            <table class="data-table" id="articles-table">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th>标签</th>
                        <th>状态</th>
                        <th>精选</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="articles-body">
                    <tr><td colspan="6" class="loading">加载中...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pagination"></div>
    </main>

    <!-- 文章编辑弹窗 -->
    <div class="modal-overlay" id="article-modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modal-title">新建文章</h2>
                <button class="modal-close" onclick="closeArticleModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="article-form">
                    <input type="hidden" id="article-id">
                    
                    <div class="form-group">
                        <label>标题</label>
                        <input type="text" id="article-title" required>
                    </div>

                    <div class="form-group">
                        <label>别名 (slug)</label>
                        <input type="text" id="article-slug" placeholder="自动生成">
                    </div>

                    <div class="form-group">
                        <label>摘要</label>
                        <textarea id="article-excerpt" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>封面图片 URL</label>
                        <input type="url" id="article-cover" placeholder="https://...">
                    </div>

                    <div class="form-group">
                        <label>标签 (逗号分隔)</label>
                        <input type="text" id="article-tags" placeholder="标签1, 标签2, 标签3">
                    </div>

                    <div class="form-group">
                        <label>内容</label>
                        <textarea id="article-content" rows="15" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>状态</label>
                            <select id="article-status">
                                <option value="draft">草稿</option>
                                <option value="published">已发布</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>精选文章</label>
                            <input type="checkbox" id="article-featured">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeArticleModal()">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let searchTimeout = null;

        function loadArticles(page = 1) {
            currentPage = page;
            const status = document.getElementById('status-filter').value;
            const search = document.getElementById('search-input').value;
            
            fetch(`api/cms.php?action=get_articles&page=${page}&limit=10&status=${status}&search=${encodeURIComponent(search)}`)
                .then(res => res.json())
                .then(data => {
                    renderArticles(data.articles);
                    renderPagination(data.total, page);
                })
                .catch(err => console.error(err));
        }

        function renderArticles(articles) {
            const tbody = document.getElementById('articles-body');
            if (!articles || articles.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty">暂无文章</td></tr>';
                return;
            }

            tbody.innerHTML = articles.map(article => `
                <tr>
                    <td class="title-cell">
                        <a href="#" onclick="editArticle(${article.id})">${escapeHtml(article.title)}</a>
                    </td>
                    <td>
                        ${article.tags ? article.tags.split(',').map(t => `<span class="tag">${t.trim()}</span>`).join('') : '-'}
                    </td>
                    <td>
                        <span class="status-badge ${article.status}">${article.status === 'published' ? '已发布' : '草稿'}</span>
                    </td>
                    <td>
                        <i class="fas ${article.is_featured ? 'fa-star text-yellow' : 'fa-star-o'}"></i>
                    </td>
                    <td>${article.created_at}</td>
                    <td class="actions-cell">
                        <button onclick="editArticle(${article.id})" title="编辑">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteArticle(${article.id})" title="删除">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderPagination(total, page) {
            const pagination = document.getElementById('pagination');
            const pages = Math.ceil(total / 10);
            
            if (pages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = '';
            if (page > 1) {
                html += `<button onclick="loadArticles(${page - 1})">上一页</button>`;
            }
            
            for (let i = 1; i <= pages; i++) {
                html += `<button ${i === page ? 'class="active"' : ''} onclick="loadArticles(${i})">${i}</button>`;
            }
            
            if (page < pages) {
                html += `<button onclick="loadArticles(${page + 1})">下一页</button>`;
            }
            
            pagination.innerHTML = html;
        }

        function showArticleModal() {
            document.getElementById('modal-title').textContent = '新建文章';
            document.getElementById('article-form').reset();
            document.getElementById('article-id').value = '';
            document.getElementById('article-modal-overlay').style.display = 'block';
        }

        function closeArticleModal() {
            document.getElementById('article-modal-overlay').style.display = 'none';
        }

        function editArticle(id) {
            fetch(`api/cms.php?action=get_article&id=${id}`)
                .then(res => res.json())
                .then(article => {
                    document.getElementById('modal-title').textContent = '编辑文章';
                    document.getElementById('article-id').value = article.id;
                    document.getElementById('article-title').value = article.title;
                    document.getElementById('article-slug').value = article.slug;
                    document.getElementById('article-excerpt').value = article.excerpt;
                    document.getElementById('article-cover').value = article.cover;
                    document.getElementById('article-tags').value = article.tags;
                    document.getElementById('article-content').value = article.content;
                    document.getElementById('article-status').value = article.status;
                    document.getElementById('article-featured').checked = article.is_featured;
                    document.getElementById('article-modal-overlay').style.display = 'block';
                });
        }

        function deleteArticle(id) {
            if (!confirm('确定要删除这篇文章吗？')) return;
            
            fetch(`api/cms.php?action=delete_article&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadArticles(currentPage);
                    }
                });
        }

        document.getElementById('article-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const data = {
                id: document.getElementById('article-id').value || null,
                title: document.getElementById('article-title').value,
                slug: document.getElementById('article-slug').value,
                excerpt: document.getElementById('article-excerpt').value,
                cover: document.getElementById('article-cover').value,
                tags: document.getElementById('article-tags').value,
                content: document.getElementById('article-content').value,
                status: document.getElementById('article-status').value,
                is_featured: document.getElementById('article-featured').checked ? 1 : 0
            };

            fetch('api/cms.php?action=save_article', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeArticleModal();
                    loadArticles(currentPage);
                }
            });
        });

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadArticles(1), 300);
        }

        function escapeHtml(str) {
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        loadArticles(1);
    </script>
</body>
</html>
