<?php
require_once 'config.php';

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch posts with filters
$query = "SELECT * FROM posts WHERE 1=1";
if (!empty($search)) {
    $query .= " AND (title LIKE '%$search%' OR content LIKE '%$search%' OR author_name LIKE '%$search%')";
}
if (!empty($category_filter)) {
    $query .= " AND category = '$category_filter'";
}
$query .= " ORDER BY created_at DESC LIMIT 10";

$posts_result = $conn->query($query);

// Get all categories
$categories_result = $conn->query("SELECT DISTINCT category FROM posts WHERE category IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classic Clean Blog - Home</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header & Navigation */
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
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }
        
        .logo span {
            color: #666;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        nav a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: color 0.3s;
            padding: 0.5rem 0;
        }
        
        nav a:hover, nav a.active {
            color: #333;
            border-bottom: 2px solid #333;
        }
        
        .create-post-btn {
            background-color: #333;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .create-post-btn:hover {
            background-color: #555;
        }
        
        /* Search and Filter Section */
        .search-filter {
            background-color: #f8f8f8;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .search-box {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            gap: 1rem;
        }
        
        .search-box input,
        .search-box select {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 1rem;
        }
        
        .search-box button {
            background-color: #333;
            color: white;
            border: none;
            padding: 0 2rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .categories {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .category-tag {
            background-color: #f0f0f0;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .category-tag:hover,
        .category-tag.active {
            background-color: #333;
            color: white;
        }
        
        /* Main Content */
        main {
            padding: 2rem 0;
        }
        
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #333;
        }
        
        /* Posts Grid */
        .posts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .post-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        
        .post-content {
            padding: 2rem;
        }
        
        .post-category {
            display: inline-block;
            background-color: #f0f0f0;
            color: #666;
            padding: 0.3rem 1rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }
        
        .post-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #333;
            line-height: 1.3;
        }
        
        .post-title a {
            text-decoration: none;
            color: inherit;
        }
        
        .post-title a:hover {
            color: #555;
        }
        
        .post-excerpt {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }
        
        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #888;
        }
        
        .post-author {
            font-weight: 500;
            color: #333;
        }
        
        .post-date {
            font-style: italic;
        }
        
        .read-more {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding-bottom: 2px;
            border-bottom: 1px solid #333;
            transition: color 0.3s;
        }
        
        .read-more:hover {
            color: #555;
        }
        
        /* No Posts Message */
        .no-posts {
            text-align: center;
            padding: 4rem 2rem;
            color: #888;
            font-size: 1.1rem;
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
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            
            .search-box {
                flex-direction: column;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .post-title {
                font-size: 1.5rem;
            }
            
            .post-content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="index.php" class="logo">Classic<span>Blog</span></a>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <?php while($cat = $categories_result->fetch_assoc()): ?>
                        <li><a href="index.php?category=<?= urlencode($cat['category']) ?>" class="category-link"><?= htmlspecialchars($cat['category']) ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </nav>
            <a href="create.php" class="create-post-btn">Create Post</a>
        </div>
    </header>
    
    <section class="search-filter">
        <div class="container">
            <form method="GET" action="index.php" class="search-box">
                <input type="text" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php 
                    $categories = $conn->query("SELECT DISTINCT category FROM posts WHERE category IS NOT NULL");
                    while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category_filter == $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Search</button>
            </form>
            
            <div class="categories">
                <a href="index.php" class="category-tag <?= empty($category_filter) ? 'active' : '' ?>">All</a>
                <?php 
                $all_cats = $conn->query("SELECT DISTINCT category FROM posts WHERE category IS NOT NULL");
                while($cat = $all_cats->fetch_assoc()): ?>
                    <a href="index.php?category=<?= urlencode($cat['category']) ?>" 
                       class="category-tag <?= $category_filter == $cat['category'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['category']) ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    
    <main class="container">
        <h1 class="page-title">Recent Blog Posts</h1>
        
        <div class="posts-grid">
            <?php if($posts_result->num_rows > 0): ?>
                <?php while($post = $posts_result->fetch_assoc()): ?>
                    <article class="post-card">
                        <div class="post-content">
                            <?php if($post['category']): ?>
                                <span class="post-category"><?= htmlspecialchars($post['category']) ?></span>
                            <?php endif; ?>
                            
                            <h2 class="post-title">
                                <a href="post.php?id=<?= $post['id'] ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            
                            <p class="post-excerpt">
                                <?= htmlspecialchars(substr($post['content'], 0, 200)) ?>...
                            </p>
                            
                            <div class="post-meta">
                                <div>
                                    <span class="post-author">By <?= htmlspecialchars($post['author_name']) ?></span>
                                    <span class="post-date">on <?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                                </div>
                                <a href="post.php?id=<?= $post['id'] ?>" class="read-more">Read Full Post →</a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-posts">
                    <h2>No posts found</h2>
                    <p>Try a different search or create a new post!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <div class="footer-content">
            <div class="footer-logo">Classic Clean Blog</div>
            <p>© <?= date('Y') ?> Classic Clean Blog. All rights reserved.</p>
            <p>Simple, clean, and elegant blogging platform.</p>
        </div>
    </footer>
    
    <script>
        // Redirect to create post page
        document.querySelector('.create-post-btn').addEventListener('click', function(e) {
            window.location.href = 'create.php';
        });
        
        // Add smooth scrolling
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
        
        // Auto-hide search filter on scroll
        let lastScroll = 0;
        const searchSection = document.querySelector('.search-filter');
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > lastScroll && currentScroll > 200) {
                searchSection.style.transform = 'translateY(-100%)';
            } else {
                searchSection.style.transform = 'translateY(0)';
            }
            
            lastScroll = currentScroll;
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
