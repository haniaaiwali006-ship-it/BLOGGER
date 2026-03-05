<?php
require_once 'config.php';

// Check if user is logged in (simplified version)
session_start();
if (!isset($_SESSION['user_id'])) {
    // For demo purposes, we'll use admin user
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
}

// Handle post creation/update
$post_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$post = null;
$is_edit = false;

if ($post_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
    $is_edit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $excerpt = $_POST['excerpt'];
    $category = $_POST['category'];
    $author_id = $_SESSION['user_id'];
    $author_name = $_SESSION['username'];
    
    if ($is_edit) {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, excerpt = ?, category = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $content, $excerpt, $category, $post_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, excerpt, category, author_id, author_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $title, $content, $excerpt, $category, $author_id, $author_name);
        $stmt->execute();
        $post_id = $conn->insert_id;
    }
    
    $stmt->close();
    
    // Redirect to the created/edited post
    echo "<script>window.location.href = 'post.php?id=$post_id';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Edit Post' : 'Create New Post' ?> - Classic Clean Blog</title>
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
            background-color: #f8f8f8;
            color: #333333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        header {
            background-color: #ffffff;
            border-bottom: 1px solid #eaeaea;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1000px;
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
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
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
            padding: 2rem 0;
        }
        
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #333;
        }
        
        /* Form Styles */
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
            min-height: 200px;
            resize: vertical;
        }
        
        #excerpt {
            min-height: 100px;
        }
        
        #content {
            min-height: 400px;
            font-family: 'Roboto', sans-serif;
            line-height: 1.7;
        }
        
        select.form-control {
            max-width: 300px;
        }
        
        /* Editor Toolbar */
        .editor-toolbar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background-color: #f8f8f8;
            border-radius: 4px;
            flex-wrap: wrap;
        }
        
        .editor-btn {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Roboto', sans-serif;
            font-size: 0.9rem;
        }
        
        .editor-btn:hover {
            background-color: #f0f0f0;
            border-color: #333;
        }
        
        .editor-btn.active {
            background-color: #333;
            color: white;
            border-color: #333;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        
        /* Preview Section */
        .preview-section {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: none;
        }
        
        .preview-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .preview-content {
            line-height: 1.7;
            color: #444;
        }
        
        .preview-content h1,
        .preview-content h2,
        .preview-content h3 {
            font-family: 'Playfair Display', serif;
            margin: 1.5rem 0 1rem 0;
        }
        
        .preview-content p {
            margin-bottom: 1rem;
        }
        
        .preview-content ul,
        .preview-content ol {
            margin-left: 2rem;
            margin-bottom: 1rem;
        }
        
        .preview-content blockquote {
            border-left: 4px solid #ddd;
            padding-left: 1rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: #666;
        }
        
        .preview-content code {
            background-color: #f8f8f8;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: monospace;
            font-size: 0.9em;
        }
        
        /* Footer */
        footer {
            background-color: #f8f8f8;
            padding: 2rem 0;
            margin-top: 4rem;
            border-top: 1px solid #eaeaea;
            text-align: center;
            color: #666;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .editor-toolbar {
                justify-content: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .form-container {
                padding: 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">Classic<span>Blog</span></a>
            <div class="header-actions">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="button" onclick="previewPost()" class="btn btn-secondary">Preview</button>
                <button type="submit" form="postForm" class="btn btn-primary"><?= $is_edit ? 'Update Post' : 'Publish Post' ?></button>
            </div>
        </div>
    </header>
    
    <main class="container">
        <h1 class="page-title"><?= $is_edit ? 'Edit Post' : 'Create New Post' ?></h1>
        
        <form id="postForm" method="POST" class="form-container">
            <div class="form-group">
                <label for="title">Post Title</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= $is_edit ? htmlspecialchars($post['title']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="excerpt">Excerpt (Brief Summary)</label>
                <textarea id="excerpt" name="excerpt" class="form-control"><?= $is_edit ? htmlspecialchars($post['excerpt']) : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control">
                    <option value="">Select Category</option>
                    <option value="Technology" <?= $is_edit && $post['category'] == 'Technology' ? 'selected' : '' ?>>Technology</option>
                    <option value="Lifestyle" <?= $is_edit && $post['category'] == 'Lifestyle' ? 'selected' : '' ?>>Lifestyle</option>
                    <option value="Business" <?= $is_edit && $post['category'] == 'Business' ? 'selected' : '' ?>>Business</option>
                    <option value="Travel" <?= $is_edit && $post['category'] == 'Travel' ? 'selected' : '' ?>>Travel</option>
                    <option value="Education" <?= $is_edit && $post['category'] == 'Education' ? 'selected' : '' ?>>Education</option>
                    <option value="Personal" <?= $is_edit && $post['category'] == 'Personal' ? 'selected' : '' ?>>Personal</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <div class="editor-toolbar">
                    <button type="button" class="editor-btn" onclick="formatText('bold')"><b>B</b></button>
                    <button type="button" class="editor-btn" onclick="formatText('italic')"><i>I</i></button>
                    <button type="button" class="editor-btn" onclick="formatText('underline')"><u>U</u></button>
                    <button type="button" class="editor-btn" onclick="insertHeading(1)">H1</button>
                    <button type="button" class="editor-btn" onclick="insertHeading(2)">H2</button>
                    <button type="button" class="editor-btn" onclick="insertHeading(3)">H3</button>
                    <button type="button" class="editor-btn" onclick="insertList('ul')">• List</button>
                    <button type="button" class="editor-btn" onclick="insertList('ol')">1. List</button>
                    <button type="button" class="editor-btn" onclick="insertQuote()">" Quote</button>
                    <button type="button" class="editor-btn" onclick="insertCode()">&lt;/&gt;</button>
                    <button type="button" class="editor-btn" onclick="insertLink()">🔗</button>
                </div>
                <textarea id="content" name="content" class="form-control" required><?= $is_edit ? htmlspecialchars($post['content']) : '' ?></textarea>
            </div>
        </form>
        
        <div id="previewSection" class="preview-section">
            <h2 class="preview-title" id="previewTitle">Preview Title</h2>
            <div class="preview-content" id="previewContent">
                Preview content will appear here...
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>© <?= date('Y') ?> Classic Clean Blog</p>
            <p>Create meaningful content with our simple editor</p>
        </div>
    </footer>
    
    <script>
        // Text editor functions
        function formatText(command) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            let formattedText = '';
            
            switch(command) {
                case 'bold':
                    formattedText = `<strong>${selectedText}</strong>`;
                    break;
                case 'italic':
                    formattedText = `<em>${selectedText}</em>`;
                    break;
                case 'underline':
                    formattedText = `<u>${selectedText}</u>`;
                    break;
            }
            
            textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
            textarea.focus();
        }
        
        function insertHeading(level) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const heading = `<h${level}>Your Heading Here</h${level}>\n`;
            textarea.value = textarea.value.substring(0, start) + heading + textarea.value.substring(start);
            textarea.focus();
        }
        
        function insertList(type) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const list = type === 'ul' 
                ? `<ul>\n  <li>List item 1</li>\n  <li>List item 2</li>\n</ul>\n`
                : `<ol>\n  <li>First item</li>\n  <li>Second item</li>\n</ol>\n`;
            textarea.value = textarea.value.substring(0, start) + list + textarea.value.substring(start);
            textarea.focus();
        }
        
        function insertQuote() {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const quote = `<blockquote>Your quote here</blockquote>\n`;
            textarea.value = textarea.value.substring(0, start) + quote + textarea.value.substring(start);
            textarea.focus();
        }
        
        function insertCode() {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const code = selectedText ? `<code>${selectedText}</code>` : `<code>Your code here</code>`;
            textarea.value = textarea.value.substring(0, start) + code + textarea.value.substring(end);
            textarea.focus();
        }
        
        function insertLink() {
            const url = prompt('Enter URL:');
            if (url) {
                const text = prompt('Enter link text:', url);
                const textarea = document.getElementById('content');
                const start = textarea.selectionStart;
                const link = `<a href="${url}">${text || url}</a>`;
                textarea.value = textarea.value.substring(0, start) + link + textarea.value.substring(start);
                textarea.focus();
            }
        }
        
        // Preview function
        function previewPost() {
            const title = document.getElementById('title').value || 'Preview Title';
            const content = document.getElementById('content').value || 'Preview content will appear here...';
            
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewContent').innerHTML = content;
            
            const previewSection = document.getElementById('previewSection');
            previewSection.style.display = 'block';
            previewSection.scrollIntoView({ behavior: 'smooth' });
        }
        
        // Form validation
        document.getElementById('postForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title || !content) {
                e.preventDefault();
                alert('Please fill in all required fields (Title and Content).');
                return false;
            }
            
            return true;
        });
        
        // Auto-save draft (simplified)
        let autoSaveTimer;
        const saveDraft = () => {
            const title = document.getElementById('title').value;
            const content = document.getElementById('content').value;
            const excerpt = document.getElementById('excerpt').value;
            const category = document.getElementById('category').value;
            
            const draft = { title, content, excerpt, category };
            localStorage.setItem('blog_draft', JSON.stringify(draft));
        };
        
        document.getElementById('title').addEventListener('input', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveDraft, 1000);
        });
        
        document.getElementById('content').addEventListener('input', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveDraft, 1000);
        });
        
        // Load draft on page load (for new posts only)
        window.addEventListener('load', () => {
            <?php if(!$is_edit): ?>
            const draft = localStorage.getItem('blog_draft');
            if (draft) {
                const draftData = JSON.parse(draft);
                document.getElementById('title').value = draftData.title || '';
                document.getElementById('content').value = draftData.content || '';
                document.getElementById('excerpt').value = draftData.excerpt || '';
                document.getElementById('category').value = draftData.category || '';
            }
            <?php endif; ?>
        });
        
        // Clear draft on successful publish
        document.getElementById('postForm').addEventListener('submit', () => {
            localStorage.removeItem('blog_draft');
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
