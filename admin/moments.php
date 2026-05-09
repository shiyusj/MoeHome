<?php
/**
 * MoeHome 后台 - 动态管理
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
    <title>动态管理 - MoeHome 后台</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php require_once 'partials/header.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>动态管理</h1>
            <button class="btn btn-primary" onclick="showMomentModal()">
                <i class="fas fa-plus"></i> 发布动态
            </button>
        </div>

        <div class="page-actions">
            <div class="filter-group">
                <select id="tag-filter" onchange="loadMoments()">
                    <option value="">全部标签</option>
                </select>
            </div>
        </div>

        <div class="moments-list" id="moments-list">
            <div class="loading">加载中...</div>
        </div>

        <div class="pagination" id="pagination"></div>
    </main>

    <!-- 动态编辑弹窗 -->
    <div class="modal-overlay" id="moment-modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modal-title">发布动态</h2>
                <button class="modal-close" onclick="closeMomentModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="moment-form">
                    <input type="hidden" id="moment-id">
                    
                    <div class="form-group">
                        <label>内容</label>
                        <textarea id="moment-content" rows="8" required placeholder="分享你的想法..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>标签 (逗号分隔)</label>
                        <input type="text" id="moment-tags" placeholder="#标签1, #标签2">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>置顶</label>
                            <input type="checkbox" id="moment-pinned">
                        </div>
                        <div class="form-group">
                            <label>状态</label>
                            <select id="moment-status">
                                <option value="published">发布</option>
                                <option value="draft">草稿</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeMomentModal()">取消</button>
                        <button type="submit" class="btn btn-primary">发布</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;

        function loadMoments(page = 1) {
            currentPage = page;
            const tag = document.getElementById('tag-filter').value;
            fetch(`api/cms.php?action=get_moments&page=${page}&limit=20&tag=${tag}`)
                .then(res => res.json())
                .then(data => {
                    renderMoments(data.moments);
                    renderPagination(data.total, page);
                    loadTags();
                })
                .catch(err => console.error(err));
        }

        function renderMoments(moments) {
            const container = document.getElementById('moments-list');
            if (!moments || moments.length === 0) {
                container.innerHTML = '<div class="empty">暂无动态</div>';
                return;
            }

            container.innerHTML = moments.map(moment => `
                <div class="moment-item">
                    <div class="moment-header">
                        <span class="moment-date">${moment.created_at}</span>
                        ${moment.is_pinned ? '<span class="pinned-badge">置顶</span>' : ''}
                    </div>
                    <div class="moment-content">${escapeHtml(moment.content)}</div>
                    <div class="moment-footer">
                        ${moment.tags ? moment.tags.split(',').map(t => `<span class="tag">#${t.trim()}</span>`).join('') : ''}
                        <div class="moment-actions">
                            <button onclick="editMoment(${moment.id})"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteMoment(${moment.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function loadTags() {
            fetch('api/cms.php?action=get_moments&page=1&limit=100')
                .then(res => res.json())
                .then(data => {
                    const tags = new Set();
                    data.moments.forEach(m => {
                        if (m.tags) {
                            m.tags.split(',').forEach(t => tags.add(t.trim()));
                        }
                    });
                    
                    const select = document.getElementById('tag-filter');
                    select.innerHTML = '<option value="">全部标签</option>' +
                        Array.from(tags).filter(t => t).map(t => `<option value="${t}">${t}</option>`).join('');
                });
        }

        function renderPagination(total, page) {
            const pagination = document.getElementById('pagination');
            const pages = Math.ceil(total / 20);
            
            if (pages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = '';
            if (page > 1) {
                html += `<button onclick="loadMoments(${page - 1})">上一页</button>`;
            }
            
            for (let i = 1; i <= pages; i++) {
                html += `<button ${i === page ? 'class="active"' : ''} onclick="loadMoments(${i})">${i}</button>`;
            }
            
            if (page < pages) {
                html += `<button onclick="loadMoments(${page + 1})">下一页</button>`;
            }
            
            pagination.innerHTML = html;
        }

        function showMomentModal() {
            document.getElementById('modal-title').textContent = '发布动态';
            document.getElementById('moment-form').reset();
            document.getElementById('moment-id').value = '';
            document.getElementById('moment-modal-overlay').style.display = 'block';
        }

        function closeMomentModal() {
            document.getElementById('moment-modal-overlay').style.display = 'none';
        }

        function editMoment(id) {
            fetch(`api/cms.php?action=get_moment&id=${id}`)
                .then(res => res.json())
                .then(moment => {
                    document.getElementById('modal-title').textContent = '编辑动态';
                    document.getElementById('moment-id').value = moment.id;
                    document.getElementById('moment-content').value = moment.content;
                    document.getElementById('moment-tags').value = moment.tags;
                    document.getElementById('moment-pinned').checked = moment.is_pinned;
                    document.getElementById('moment-status').value = moment.status;
                    document.getElementById('moment-modal-overlay').style.display = 'block';
                });
        }

        function deleteMoment(id) {
            if (!confirm('确定要删除这条动态吗？')) return;
            
            fetch(`api/cms.php?action=delete_moment&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadMoments(currentPage);
                    }
                });
        }

        document.getElementById('moment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const data = {
                id: document.getElementById('moment-id').value || null,
                content: document.getElementById('moment-content').value,
                tags: document.getElementById('moment-tags').value,
                is_pinned: document.getElementById('moment-pinned').checked ? 1 : 0,
                status: document.getElementById('moment-status').value
            };

            fetch('api/cms.php?action=save_moment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeMomentModal();
                    loadMoments(currentPage);
                }
            });
        });

        function escapeHtml(str) {
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        loadMoments(1);
    </script>
</body>
</html>
