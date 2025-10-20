<?php
require_once 'includes/config.php';
$currentUser = getCurrentUser();
$pageTitle = 'Blogs';

$conn = getDbConnection();
$message = '';

// Handle blog submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    try {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        if (empty($title) || empty($content)) {
            throw new Exception("Title and content are required");
        }

        // Insert blog post
        $stmt = $conn->prepare("INSERT INTO blogs (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$currentUser['id'], $title, $content]);

        $message = '<div class="alert alert-success">Blog post published successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Get all blog posts
$stmt = $conn->query("
    SELECT b.*, u.username, u.avatar 
    FROM blogs b 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.created_at DESC
");
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <h2 class="mb-4"><i class="fas fa-blog me-2"></i>Community Blogs</h2>
        
        <?php echo $message; ?>
        
        <?php if (isLoggedIn()): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Write a New Blog Post</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Publish</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <a href="/login.php" class="alert-link">Log in</a> to write your own blog posts.
            </div>
        <?php endif; ?>

        <h4 class="mt-5 mb-3">Recent Posts</h4>
        
        <?php if (empty($blogs)): ?>
            <div class="alert alert-info">No blog posts yet. Be the first to post!</div>
        <?php else: ?>
            <?php foreach ($blogs as $blog): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="/display.php?file=<?php echo htmlspecialchars($blog['avatar'] ?? 'default.png'); ?>"
                                 class="avatar me-2"
                                 alt="<?php echo htmlspecialchars($blog['username']); ?>"
                                 onerror="this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='">
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($blog['title']); ?></h5>
                                <small class="text-muted">
                                    By <?php echo htmlspecialchars($blog['username']); ?> 
                                    on <?php echo date('M j, Y', strtotime($blog['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="blog-content mb-3">
                            <?php 
                            // Simple markdown-like formatting
                            $content = htmlspecialchars($blog['content']);
                            // Convert line breaks to <br>
                            $content = nl2br($content);
                            // Simple markdown for **bold** and *italic*
                            $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
                            $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
                            echo $content;
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
