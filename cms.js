/**
 * MoeHome CMS Frontend Script
 * 负责加载和渲染文章、项目、动态等内容
 */

(function() {
    'use strict';

    const API_BASE = window.MOEHOME_CONFIG?.apiBase || 'api';

    document.addEventListener('DOMContentLoaded', function() {
        loadArticles();
        loadActivity();
        loadProjects();
        loadMomentsPreview();
    });

    async function loadArticles() {
        try {
            const response = await fetch(`${API_BASE}/content.php?action=articles&count=4`);
            const data = await response.json();
            
            if (data.articles && data.articles.length > 0) {
                renderArticles(data.articles);
                document.getElementById('articles-count').textContent = `${data.count} posts`;
            } else {
                document.getElementById('articles-list').innerHTML = '<div class="empty">暂无文章</div>';
            }
        } catch (error) {
            console.error('Failed to load articles:', error);
        }
    }

    function renderArticles(articles) {
        const container = document.getElementById('articles-list');
        container.innerHTML = articles.map(article => `
            <article class="article-card">
                ${article.cover ? `<div class="article-cover"><img src="${article.cover}" alt="${article.title}"></div>` : ''}
                <div class="article-content">
                    <h3 class="article-title">
                        <a href="article/${article.slug}.html">${escapeHtml(article.title)}</a>
                    </h3>
                    <p class="article-excerpt">${escapeHtml(article.excerpt || '')}</p>
                    <div class="article-meta">
                        ${article.tags ? article.tags.split(',').slice(0, 3).map(tag => `<span class="article-tag">#${tag.trim()}</span>`).join('') : ''}
                        <span class="article-date">${formatDate(article.created_at)}</span>
                    </div>
                </div>
            </article>
        `).join('');
    }

    async function loadActivity() {
        try {
            const response = await fetch(`${API_BASE}/content.php?action=activity&days=14`);
            const data = await response.json();
            
            if (data) {
                renderActivity(data);
            }
        } catch (error) {
            console.error('Failed to load activity:', error);
        }
    }

    function renderActivity(activity) {
        const container = document.getElementById('activity-container');
        
        const activityHtml = `
            <div class="activity-stats">
                <div class="activity-stat">
                    <div class="stat-value">${activity.articles || 0}</div>
                    <div class="stat-label">文章</div>
                </div>
                <div class="activity-stat">
                    <div class="stat-value">${activity.moments || 0}</div>
                    <div class="stat-label">动态</div>
                </div>
                <div class="activity-stat">
                    <div class="stat-value">${activity.projects || 0}</div>
                    <div class="stat-label">项目</div>
                </div>
                <div class="activity-stat">
                    <div class="stat-value">${activity.total || 0}</div>
                    <div class="stat-label">总计</div>
                </div>
            </div>
            ${activity.history && activity.history.length > 0 ? renderActivityTimeline(activity.history) : ''}
        `;
        
        container.innerHTML = activityHtml;
    }

    function renderActivityTimeline(history) {
        const timeline = {};
        history.forEach(item => {
            if (!timeline[item.date]) {
                timeline[item.date] = [];
            }
            timeline[item.date].push(item.type);
        });

        const dates = Object.keys(timeline).slice(0, 7).reverse();
        
        return `
            <div class="activity-timeline">
                ${dates.map(date => `
                    <div class="timeline-item">
                        <span class="timeline-date">${formatShortDate(date)}</span>
                        <div class="timeline-dots">
                            ${timeline[date].map(type => `<span class="timeline-dot timeline-dot-${type}"></span>`).join('')}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    async function loadProjects() {
        try {
            const response = await fetch(`${API_BASE}/content.php?action=projects&count=5`);
            const data = await response.json();
            
            if (data.projects && data.projects.length > 0) {
                renderProjects(data.projects);
                document.getElementById('projects-count').textContent = `${data.count} repos`;
            } else {
                document.getElementById('projects-container').innerHTML = '<div class="empty">暂无项目</div>';
            }
        } catch (error) {
            console.error('Failed to load projects:', error);
        }
    }

    function renderProjects(projects) {
        const container = document.getElementById('projects-container');
        
        const mainProject = projects.find(p => p.is_main);
        const otherProjects = projects.filter(p => !p.is_main);
        
        let html = '';
        
        if (mainProject) {
            html += `
                <div class="project-main">
                    <div class="project-header">
                        <h2 class="project-name">${escapeHtml(mainProject.name)}</h2>
                        <div class="project-stats">
                            <span class="project-stat"><i class="fas fa-star"></i> ${mainProject.stars}</span>
                            <span class="project-stat"><i class="fas fa-code-fork"></i> ${mainProject.forks}</span>
                        </div>
                    </div>
                    <p class="project-description">${escapeHtml(mainProject.description || '')}</p>
                    <div class="project-meta">
                        ${mainProject.language ? `<span class="project-language">${escapeHtml(mainProject.language)}</span>` : ''}
                        ${mainProject.url ? `<a href="${mainProject.url}" target="_blank" class="project-link">查看项目</a>` : ''}
                    </div>
                </div>
            `;
        }
        
        if (otherProjects.length > 0) {
            html += `
                <div class="projects-grid">
                    ${otherProjects.map(project => `
                        <div class="project-card">
                            <div class="project-card-header">
                                ${project.url ? `<a href="${project.url}" target="_blank">${escapeHtml(project.name)}</a>` : `<span>${escapeHtml(project.name)}</span>`}
                                <span class="project-card-language">${escapeHtml(project.language || '')}</span>
                            </div>
                            <p class="project-card-desc">${escapeHtml(project.description || '')}</p>
                            <div class="project-card-stats">
                                <span><i class="fas fa-star"></i> ${project.stars}</span>
                                <span><i class="fas fa-code-fork"></i> ${project.forks}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    async function loadMomentsPreview() {
        try {
            const response = await fetch(`${API_BASE}/content.php?action=moments&count=3`);
            const data = await response.json();
            
            if (data.moments && data.moments.length > 0) {
                renderMomentsPreview(data.moments);
            } else {
                document.getElementById('moments-preview').innerHTML = '<div class="empty">暂无动态</div>';
            }
        } catch (error) {
            console.error('Failed to load moments:', error);
        }
    }

    function renderMomentsPreview(moments) {
        const container = document.getElementById('moments-preview');
        container.innerHTML = moments.map(moment => `
            <div class="moment-preview-item">
                <div class="moment-preview-header">
                    <span class="moment-preview-date">${moment.created_at}</span>
                    ${moment.is_pinned ? '<span class="moment-pinned">置顶</span>' : ''}
                </div>
                <p class="moment-preview-content">${escapeHtml(moment.content)}</p>
                ${moment.tags && moment.tags.length > 0 ? `
                    <div class="moment-preview-tags">
                        ${moment.tags.map(tag => `<span class="moment-tag">#${tag}</span>`).join('')}
                    </div>
                ` : ''}
            </div>
        `).join('');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return '刚刚';
        if (diff < 3600000) return Math.floor(diff / 60000) + '分钟前';
        if (diff < 86400000) return Math.floor(diff / 3600000) + '小时前';
        if (diff < 604800000) return Math.floor(diff / 86400000) + '天前';
        
        return dateStr.slice(0, 10);
    }

    function formatShortDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return `${date.getMonth() + 1}/${date.getDate()}`;
    }

})();
