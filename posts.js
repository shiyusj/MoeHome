/**
 * MoeHome Blog Posts Module
 * 博客文章模块 - 支持首页文章列表和博客页面分类浏览
 */

class PostsModule {
    constructor() {
        this.apiBase = window.POSTS_CONFIG?.apiBase || 'api';
        this.currentCategory = window.POSTS_CONFIG?.currentCategory || '';
        this.currentPage = 1;
        this.totalPages = 1;
        this.postsPerPage = 6;
        this.isLoading = false;
        
        this.init();
    }

    init() {
        this.initHomepagePosts();
        this.initBlogPage();
    }

    initHomepagePosts() {
        const postsList = document.getElementById('posts-list');
        if (!postsList) return;

        const count = parseInt(postsList.dataset.count) || 4;

        this.fetchPosts(1, count, '')
            .then(posts => {
                this.renderHomepagePosts(posts);
            })
            .catch(error => {
                console.error('Failed to fetch posts:', error);
                postsList.innerHTML = '<div class="posts-error">文章加载失败</div>';
            });
    }

    initBlogPage() {
        const postsContainer = document.getElementById('posts-container');
        if (!postsContainer) return;

        const filterContainer = document.getElementById('posts-filter');
        if (filterContainer) {
            filterContainer.addEventListener('click', (e) => {
                const target = e.target.closest('.filter-item');
                if (target) {
                    const category = target.dataset.category || '';
                    this.handleCategoryChange(category);
                }
            });
        }

        const prevBtn = document.getElementById('pagination-prev');
        const nextBtn = document.getElementById('pagination-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.handlePrevPage());
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.handleNextPage());
        }

        this.loadBlogPosts();
    }

    async fetchPosts(page = 1, limit = 6, category = '') {
        const url = new URL(`${this.apiBase}/posts.php`);
        url.searchParams.set('page', page.toString());
        url.searchParams.set('limit', limit.toString());
        if (category) {
            url.searchParams.set('category', category);
        }

        const response = await fetch(url.toString());
        if (!response.ok) {
            throw new Error('Failed to fetch posts');
        }
        return await response.json();
    }

    async loadBlogPosts() {
        const postsContainer = document.getElementById('posts-container');
        if (!postsContainer || this.isLoading) return;

        this.isLoading = true;
        postsContainer.innerHTML = '<div class="posts-loading"><i class="fas fa-spinner fa-spin"></i><span>加载中...</span></div>';

        try {
            const result = await this.fetchPosts(this.currentPage, this.postsPerPage, this.currentCategory);
            const posts = result.posts || result;
            const total = result.total || posts.length;
            
            this.totalPages = Math.ceil(total / this.postsPerPage);
            
            this.renderBlogPosts(posts);
            this.updatePagination();
        } catch (error) {
            console.error('Failed to load blog posts:', error);
            postsContainer.innerHTML = '<div class="posts-error"><i class="fas fa-exclamation-circle"></i><span>文章加载失败</span></div>';
        } finally {
            this.isLoading = false;
        }
    }

    renderHomepagePosts(posts) {
        const postsList = document.getElementById('posts-list');
        if (!postsList) return;

        if (!posts || posts.length === 0) {
            postsList.innerHTML = '<div class="posts-empty"><i class="fas fa-file-text"></i><span>暂无文章</span></div>';
            return;
        }

        const html = posts.map(post => this.createPostCard(post)).join('');
        postsList.innerHTML = html;
    }

    renderBlogPosts(posts) {
        const postsContainer = document.getElementById('posts-container');
        if (!postsContainer) return;

        if (!posts || posts.length === 0) {
            postsContainer.innerHTML = '<div class="posts-empty"><i class="fas fa-file-text"></i><span>暂无文章</span></div>';
            return;
        }

        const html = posts.map(post => this.createBlogPostCard(post)).join('');
        postsContainer.innerHTML = html;
    }

    createPostCard(post) {
        const tags = post.tags || [];
        const excerpt = post.excerpt || (post.content ? post.content.substring(0, 100) + '...' : '');
        const categoryName = this.getCategoryName(post.category);
        
        return `
            <article class="post-card">
                <div class="post-header">
                    <span class="post-category">${this.escapeHtml(categoryName)}</span>
                    <time class="post-date">${this.escapeHtml(post.date)}</time>
                </div>
                <h3 class="post-title">
                    <a href="posts/?p=${post.id}" class="post-link">${this.escapeHtml(post.title)}</a>
                </h3>
                <p class="post-excerpt">${this.escapeHtml(excerpt)}</p>
                <div class="post-footer">
                    ${tags.length > 0 ? tags.map(tag => `<span class="post-tag">${this.escapeHtml(tag)}</span>`).join('') : ''}
                </div>
            </article>
        `;
    }

    createBlogPostCard(post) {
        const tags = post.tags || [];
        const excerpt = post.excerpt || (post.content ? post.content.substring(0, 120) + '...' : '');
        const categoryName = this.getCategoryName(post.category);
        
        return `
            <article class="blog-post-card">
                <div class="blog-post-content">
                    <div class="blog-post-meta">
                        <span class="blog-post-category">${this.escapeHtml(categoryName)}</span>
                        <time class="blog-post-date">${this.escapeHtml(post.date)}</time>
                    </div>
                    <h2 class="blog-post-title">
                        <a href="posts/?p=${post.id}" class="blog-post-link">${this.escapeHtml(post.title)}</a>
                    </h2>
                    <p class="blog-post-excerpt">${this.escapeHtml(excerpt)}</p>
                    <div class="blog-post-tags">
                        ${tags.length > 0 ? tags.map(tag => `<span class="blog-post-tag">${this.escapeHtml(tag)}</span>`).join('') : ''}
                    </div>
                </div>
            </article>
        `;
    }

    getCategoryName(slug) {
        const categories = {
            'tech': '技术文章',
            'life': '生活随笔',
            'project': '项目分享',
            'default': '其他'
        };
        return categories[slug] || slug || '其他';
    }

    handleCategoryChange(category) {
        this.currentCategory = category;
        this.currentPage = 1;

        const filterItems = document.querySelectorAll('.filter-item');
        filterItems.forEach(item => {
            item.classList.toggle('active', item.dataset.category === category);
        });

        this.loadBlogPosts();
    }

    handlePrevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.loadBlogPosts();
        }
    }

    handleNextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.loadBlogPosts();
        }
    }

    updatePagination() {
        const prevBtn = document.getElementById('pagination-prev');
        const nextBtn = document.getElementById('pagination-next');
        const info = document.getElementById('pagination-info');

        if (prevBtn) {
            prevBtn.disabled = this.currentPage <= 1;
        }
        if (nextBtn) {
            nextBtn.disabled = this.currentPage >= this.totalPages;
        }
        if (info) {
            info.textContent = `第 ${this.currentPage} 页 / 共 ${this.totalPages} 页`;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new PostsModule();
});