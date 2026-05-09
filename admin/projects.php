<?php
/**
 * MoeHome 后台 - 项目管理
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
    <title>项目管理 - MoeHome 后台</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php require_once 'partials/header.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>项目管理</h1>
            <button class="btn btn-primary" onclick="showProjectModal()">
                <i class="fas fa-plus"></i> 新建项目
            </button>
        </div>

        <div class="table-container">
            <table class="data-table" id="projects-table">
                <thead>
                    <tr>
                        <th>名称</th>
                        <th>语言</th>
                        <th>状态</th>
                        <th>主项目</th>
                        <th>Stars</th>
                        <th>Forks</th>
                        <th>排序</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="projects-body">
                    <tr><td colspan="8" class="loading">加载中...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pagination"></div>
    </main>

    <!-- 项目编辑弹窗 -->
    <div class="modal-overlay" id="project-modal-overlay" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modal-title">新建项目</h2>
                <button class="modal-close" onclick="closeProjectModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="project-form">
                    <input type="hidden" id="project-id">
                    
                    <div class="form-group">
                        <label>项目名称</label>
                        <input type="text" id="project-name" required>
                    </div>

                    <div class="form-group">
                        <label>别名 (slug)</label>
                        <input type="text" id="project-slug" placeholder="自动生成">
                    </div>

                    <div class="form-group">
                        <label>描述</label>
                        <textarea id="project-description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>封面图片 URL</label>
                        <input type="url" id="project-cover" placeholder="https://...">
                    </div>

                    <div class="form-group">
                        <label>项目链接</label>
                        <input type="url" id="project-url" placeholder="https://...">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>语言</label>
                            <input type="text" id="project-language" placeholder="JavaScript">
                        </div>
                        <div class="form-group">
                            <label>排序</label>
                            <input type="number" id="project-sort" value="0" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Stars</label>
                            <input type="number" id="project-stars" value="0" min="0">
                        </div>
                        <div class="form-group">
                            <label>Forks</label>
                            <input type="number" id="project-forks" value="0" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>状态</label>
                            <select id="project-status">
                                <option value="active">活跃</option>
                                <option value="maintenance">维护中</option>
                                <option value="archived">已归档</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>主项目</label>
                            <input type="checkbox" id="project-main">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeProjectModal()">取消</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;

        function loadProjects(page = 1) {
            currentPage = page;
            fetch(`api/cms.php?action=get_projects&page=${page}&limit=10`)
                .then(res => res.json())
                .then(data => {
                    renderProjects(data.projects);
                    renderPagination(data.total, page);
                })
                .catch(err => console.error(err));
        }

        function renderProjects(projects) {
            const tbody = document.getElementById('projects-body');
            if (!projects || projects.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="empty">暂无项目</td></tr>';
                return;
            }

            tbody.innerHTML = projects.map(project => `
                <tr>
                    <td class="title-cell">
                        ${project.url ? `<a href="${project.url}" target="_blank">${escapeHtml(project.name)}</a>` : escapeHtml(project.name)}
                    </td>
                    <td><span class="language-badge">${escapeHtml(project.language) || '-'}</span></td>
                    <td>
                        <span class="status-badge ${project.status}">${getStatusText(project.status)}</span>
                    </td>
                    <td>
                        <i class="fas ${project.is_main ? 'fa-star text-yellow' : 'fa-star-o'}"></i>
                    </td>
                    <td>${project.stars}</td>
                    <td>${project.forks}</td>
                    <td>${project.sort_order}</td>
                    <td class="actions-cell">
                        <button onclick="editProject(${project.id})" title="编辑">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteProject(${project.id})" title="删除">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusText(status) {
            const texts = {
                active: '活跃',
                maintenance: '维护中',
                archived: '已归档'
            };
            return texts[status] || status;
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
                html += `<button onclick="loadProjects(${page - 1})">上一页</button>`;
            }
            
            for (let i = 1; i <= pages; i++) {
                html += `<button ${i === page ? 'class="active"' : ''} onclick="loadProjects(${i})">${i}</button>`;
            }
            
            if (page < pages) {
                html += `<button onclick="loadProjects(${page + 1})">下一页</button>`;
            }
            
            pagination.innerHTML = html;
        }

        function showProjectModal() {
            document.getElementById('modal-title').textContent = '新建项目';
            document.getElementById('project-form').reset();
            document.getElementById('project-id').value = '';
            document.getElementById('project-modal-overlay').style.display = 'block';
        }

        function closeProjectModal() {
            document.getElementById('project-modal-overlay').style.display = 'none';
        }

        function editProject(id) {
            fetch(`api/cms.php?action=get_project&id=${id}`)
                .then(res => res.json())
                .then(project => {
                    document.getElementById('modal-title').textContent = '编辑项目';
                    document.getElementById('project-id').value = project.id;
                    document.getElementById('project-name').value = project.name;
                    document.getElementById('project-slug').value = project.slug;
                    document.getElementById('project-description').value = project.description;
                    document.getElementById('project-cover').value = project.cover;
                    document.getElementById('project-url').value = project.url;
                    document.getElementById('project-language').value = project.language;
                    document.getElementById('project-stars').value = project.stars;
                    document.getElementById('project-forks').value = project.forks;
                    document.getElementById('project-status').value = project.status;
                    document.getElementById('project-sort').value = project.sort_order;
                    document.getElementById('project-main').checked = project.is_main;
                    document.getElementById('project-modal-overlay').style.display = 'block';
                });
        }

        function deleteProject(id) {
            if (!confirm('确定要删除这个项目吗？')) return;
            
            fetch(`api/cms.php?action=delete_project&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadProjects(currentPage);
                    }
                });
        }

        document.getElementById('project-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const data = {
                id: document.getElementById('project-id').value || null,
                name: document.getElementById('project-name').value,
                slug: document.getElementById('project-slug').value,
                description: document.getElementById('project-description').value,
                cover: document.getElementById('project-cover').value,
                url: document.getElementById('project-url').value,
                language: document.getElementById('project-language').value,
                stars: parseInt(document.getElementById('project-stars').value) || 0,
                forks: parseInt(document.getElementById('project-forks').value) || 0,
                status: document.getElementById('project-status').value,
                sort_order: parseInt(document.getElementById('project-sort').value) || 0,
                is_main: document.getElementById('project-main').checked ? 1 : 0
            };

            fetch('api/cms.php?action=save_project', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeProjectModal();
                    loadProjects(currentPage);
                }
            });
        });

        function escapeHtml(str) {
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        loadProjects(1);
    </script>
</body>
</html>
