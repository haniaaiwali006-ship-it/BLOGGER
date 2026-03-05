<?php
require_once 'config.php';

$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($post_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch post details
$post_stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();
$post = $post_result->fetch_assoc();
$post_stmt->close();

if (!$post) {
    header("Location: index.php");
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $comment_content = $_POST['comment_content'];
    
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_name, user_email, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $post_id, $user_name, $user_email, $comment_content);
    $stmt->execute();
    $stmt->close();
    
    // Redirect to prevent form resubmission
    header("Location: post.php?id=$post_id#comments");
    exit;
}

// Fetch comments for this post
$comments_stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
$comments_stmt->bind_param("i", $post_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();

// Fetch related posts (same category, excluding current post)
$related_stmt = $conn->prepare("SELECT * FROM posts WHERE category = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
$related_stmt->bind_param("si", $post['category'], $post_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Classic Clean Blog</title>
    <style>
        /* Import Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #ffffff;
            color: #333333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        header {
            background-color: #ffffff;
            border-bottom: 1px solid #eaeaea;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }
        
        .logo span {
            color: #666;
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .btn-outline {
            background-color: transparent;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-outline:hover {
            background-color: #f8f8f8;
            border-color: #333;
        }
        
        .btn-primary {
            background-color: #333;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #555;
        }
        
        /* Main Content */
        main {
            padding: 3rem 0;
        }
        
        /* Post Header */
        .post-header {
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .post-category {
            display: inline-block;
            background-color: #f0f0f0;
            color: #666;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .post-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .post-meta {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            color: #888;
            font-size: 0.95rem;
            flex-wrap: wrap;
        }
        
        .post-author {
            font-weight: 500;
            color: #333;
        }
        
        .post-date {
            font-style: italic;
        }
        
        /* Post Content */
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
            margin-bottom: 4rem;
        }
        
        .post-content h1,
        .post-content h2,
        .post-content h3 {
            font-family: 'Playfair Display', serif;
            margin: 2.5rem 0 1.5rem 0;
            color: #333;
            line-height: 1.3;
        }
        
        .post-content h1 {
            font-size: 2rem;
        }
        
        .post-content h2 {
            font-size: 1.7rem;
        }
        
        .post-content h3 {
            font-size: 1.4rem;
        }
        
        .post-content p {
            margin-bottom: 1.5rem;
        }
        
        .post-content ul,
        .post-content ol {
            margin-left: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .post-content li {
            margin-bottom: 0.5rem;
        }
        
        .post-content blockquote {
            border-left: 4px solid #333;
            padding: 1.5rem 2rem;
            margin: 2rem 0;
            font-style: italic;
            color: #555;
            background-color: #f8f8f8;
            font-size: 1.2rem;
            line-height: 1.6;
        }
        
        .post-content code {
            background-color: #f8f8f8;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: monospace;
            font-size: 0.95em;
        }
        
        .post-content pre {
            background-color: #f8f8f8;
            padding: 1.5rem;
            border-radius: 6px;
            overflow-x: auto;
            margin: 1.5rem 0;
            font-family: monospace;
            font-size: 0.95em;
            line-height: 1.5;
        }
        
        .post-content a {
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #ddd;
            transition: border-color 0.3s;
        }
        
        .post-content a:hover {
            border-color: #333;
        }
        
        .post-content img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            margin: 1.5rem 0;
        }
        
        /* Post Actions */
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 0;
            margin: 2rem 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        .post-nav {
            display: flex;
            gap: 1rem;
        }
        
        /* Related Posts */
        .related-posts {
            margin: 4rem 0;
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: #333;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .related-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 1.5rem;
            transition: all 0.3s;
        }
        
        .related-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            border-color: #ddd;
        }
        
        .related-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .related-card a {
            color: #333;
            text-decoration: none;
        }
        
        .related-card a:hover {
            color: #555;
        }
        
        .related-excerpt {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .related-meta {
            font-size: 0.85rem;
            color: #888;
        }
        
        /* Comments Section */
        .comments-section {
            margin: 4rem 0;
        }
        
        .comments-list {
            margin-bottom: 3rem;
        }
        
        .comment {
            background: white;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: border-color 0.3s;
        }
        
        .comment:hover {
            border-color: #ddd;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .comment-author {
            font-weight: 500;
            color: #333;
        }
        
        .comment-date {
            font-size: 0.85rem;
            color: #888;
        }
        
        .comment-content {
            line-height: 1.6;
            color: #444;
        }
        
        /* Comment Form */
        .comment-form {
            background: white;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #333;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        /* No Comments */
        .no-comments {
            text-align: center;
            padding: 3rem;
            color: #888;
            font-size: 1.1rem;
            background-color: #f8f8f8;
            border-radius: 6px;
        }
        
        /* Footer */
        footer {
            background-color: #f8f8f8;
            padding: 3rem 0;
            margin-top: 4rem;
            border-top: 1px solid #eaeaea;
        }
        
        .footer-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            color: #666;
        }
        
        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .post-title {
                font-size: 2rem;
            }
            
            .post-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .post-actions {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .post-nav {
                flex-direction: column;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .post-title {
                font-size: 1.7rem;
            }
            
            .post-content {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .comment-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">Classic<span>Blog</span></a>
            <div class="header-actions">
                <a href="index.php" class="btn btn-outline">← Back to Home</a>
                <a href="create.php?edit=<?= $post['id'] ?>" class="btn btn-outline">Edit Post</a>
                <a href="create.php" class="btn btn-primary">New Post</a>
            </div>
        </div>
    </header>
    
    <main class="container">
        <article>
            <div class="post-header">
                <?php if($post['category']): ?>
                    <div class="post-category"><?= htmlspecialchars($post['category']) ?></div>
                <?php endif; ?>
                
                <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                
                <div class="post-meta">
                    <div class="post-author">By <?= htmlspecialchars($post['author_name']) ?></div>
                    <div class="post-date">Published on <?= date('F j, Y', strtotime($post['created_at'])) ?></div>
                    <?php if($post['updated_at'] != $post['created_at']): ?>
                        <div class="post-date">Updated on <?= date('F j, Y', strtotime($post['updated_at'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
            
            <div class="post-actions">
                <div class="post-nav">
                    <a href="index.php" class="btn btn-outline">← All Posts</a>
                </div>
                <a href="#comments" class="btn btn-primary">Join Discussion</a>
            </div>
            
            <?php if($related_result->num_rows > 0): ?>
                <section class="related-posts">
                    <h2 class="section-title">Related Posts</h2>
                    <div class="related-grid">
                        <?php while($related = $related_result->fetch_assoc()): ?>
                            <div class="related-card">
                                <h3>
                                    <a href="post.php?id=<?= $related['id'] ?>">
                                        <?= htmlspecialchars($related['title']) ?>
                                    </a>
                                </h3>
                                <p class="related-excerpt">
                                    <?= htmlspecialchars(substr($related['excerpt'] ?: $related['content'], 0, 100)) ?>...
                                </p>
                                <div class="related-meta">
                                    By <?= htmlspecialchars($related['author_name']) ?> • 
                                    <?= date('M j, Y', strtotime($related['created_at'])) ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
            <?php endif; ?>
            
            <section id="comments" class="comments-section">
                <h2 class="section-title">Discussion</h2>
                
                <div class="comments-list">
                    <?php if($comments_result->num_rows > 0): ?>
                        <?php while($comment = $comments_result->fetch_assoc()): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <div class="comment-author"><?= htmlspecialchars($comment['user_name']) ?></div>
                                    <div class="comment-date"><?= date('F j, Y', strtotime($comment['created_at'])) ?></div>
                                </div>
                                <div class="comment-content">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-comments">
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="comment-form">
                    <h3>Add Your Comment</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_name">Name *</label>
                                <input type="text" id="user_name" name="user_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="user_email">Email *</label>
                                <input type="email" id="user_email" name="user_email" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment_content">Comment *</label>
                            <textarea id="comment_content" name="comment_content" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="submit_comment" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>
            </section>
        </article>
    </main>
    
    <footer>
        <div class="footer-content">
            <div class="footer-logo">Classic Clean Blog</div>
            <p>© <?= date('Y') ?> Classic Clean Blog. All rights reserved.</p>
            <p>Share your thoughts and join meaningful discussions.</p>
        </div>
    </footer>
    
    <script>
        // Smooth scroll to comments
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Comment form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('user_name').value.trim();
            const email = document.getElementById('user_email').value.trim();
            const comment = document.getElementById('comment_content').value.trim();
            
            if (!name || !email || !comment) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            return true;
        });
        
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Auto-resize textarea
        const textarea = document.getElementById('comment_content');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Share functionality
        function sharePost(platform) {
            const url = window.location.href;
            const title = document.querySelector('.post-title').textContent;
            let shareUrl;
            
            switch(platform) {
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`;
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        }
        
        // Add share buttons dynamically
        const shareButtons = `
            <div class="post-share" style="margin: 2rem 0; padding: 1rem 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                <h3 style="font-family: 'Playfair Display', serif; margin-bottom: 1rem;">Share this post</h3>
                <div style="display: flex; gap: 1rem;">
                    <button onclick="sharePost('twitter')" style="padding: 0.5rem 1rem; background: #1DA1F2; color: white; border: none; border-radius: 4px; cursor: pointer;">Twitter</button>
                    <button onclick="sharePost('facebook')" style="padding: 0.5rem 1rem; background: #4267B2; color: white; border: none; border-radius: 4px; cursor: pointer;">Facebook</button>
                    <button onclick="sharePost('linkedin')" style="padding: 0.5rem 1rem; background: #0077B5; color: white; border: none; border-radius: 4px; cursor: pointer;">LinkedIn</button>
                </div>
            </div>
        `;
        
        document.querySelector('.post-actions').insertAdjacentHTML('afterend', shareButtons);
    </script>
</body>
</html>
<?php 
$comments_stmt->close();
$related_stmt->close();
$conn->close();
?>
