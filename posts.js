function initPosts() {
    const postsList = document.getElementById('posts-list');
    if (!postsList) return;

    const count = parseInt(postsList.dataset.count) || 4;

    fetch(`api/posts.php?count=${count}`)
        .then(response => response.json())
        .then(posts => {
            renderPosts(posts);
        })
        .catch(error => {
            console.error('Failed to fetch posts:', error);
            postsList.innerHTML = '<div class="posts-error">文章加载失败</div>';
        });
}

function renderPosts(posts) {
    const postsList = document.getElementById('posts-list');
    if (!postsList) return;

    if (!posts || posts.length === 0) {
        postsList.innerHTML = '<div class="posts-empty"><i class="fas fa-file-text"></i><span>暂无文章</span></div>';
        return;
    }

    const html = posts.map(post => {
        const tags = post.tags || [];
        const excerpt = post.excerpt || post.content.substring(0, 100) + '...';
        
        return `
            <article class="post-card">
                <div class="post-header">
                    <span class="post-category">${escapeHtml(post.category || 'default')}</span>
                    <time class="post-date">${post.date}</time>
                </div>
                <h3 class="post-title">${escapeHtml(post.title)}</h3>
                <p class="post-excerpt">${escapeHtml(excerpt)}</p>
                <div class="post-footer">
                    ${tags.length > 0 ? tags.map(tag => `<span class="post-tag">${escapeHtml(tag)}</span>`).join('') : ''}
                </div>
            </article>
        `;
    }).join('');

    postsList.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', initPosts);
