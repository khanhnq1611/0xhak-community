<?php
require_once 'includes/config.php';
$currentUser = getCurrentUser();

if (!isLoggedIn()) {
    header('Location: /login.php');
    exit();
}

$pageTitle = 'Profile';
$message = '';
$conn = getDbConnection();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $bio = $_POST['bio'] ?? '';
        
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            // Delete old avatar if exists
            if (!empty($currentUser['avatar']) && file_exists(__DIR__ . '/uploads/avatars/' . $currentUser['avatar'])) {
                @unlink(__DIR__ . '/uploads/avatars/' . $currentUser['avatar']);
            }
            
            // Generate unique filename
            $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $avatarFilename = 'avatar_' . uniqid() . '.' . $fileExt;
            $targetPath = __DIR__ . '/uploads/avatars/' . $avatarFilename;
            
            // Create uploads directory if it doesn't exist
            if (!file_exists(__DIR__ . '/uploads/avatars')) {
                mkdir(__DIR__ . '/uploads/avatars', 0755, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                // Update avatar in database
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$avatarFilename, $currentUser['id']]);
            } else {
                throw new Exception("Failed to upload avatar. Please try again.");
            }
            
            // Update current user data in session
            $currentUser['avatar'] = $avatarFilename;
            $message = '<div class="alert alert-success">Profile updated successfully!</div>';
        }
        
        // Update bio if provided
        if (!empty($bio)) {
            $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
            $stmt->execute([$bio, $currentUser['id']]);
            $currentUser['bio'] = $bio;
            $message = '<div class="alert alert-success">Profile updated successfully!</div>';
        }
        
        // Refresh current user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user'] = $currentUser;
        
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-user me-2"></i>My Profile</h4>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                
                <div class="text-center mb-4">
                    <?php if (!empty($currentUser['avatar']) && file_exists(__DIR__ . '/uploads/avatars/' . $currentUser['avatar'])): ?>
                        <img src="/uploads/avatars/<?php echo htmlspecialchars($currentUser['avatar']); ?>" 
                             class="profile-avatar mb-3" 
                             alt="Profile Picture">
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($currentUser['username']); ?></h3>
                    <p class="text-muted">Member since <?php echo date('F Y', strtotime($currentUser['created_at'])); ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-primary">
                            <i class="fas fa-star me-1"></i> <?php echo number_format($currentUser['points'] ?? 0); ?> Points
                        </span>
                        <span class="badge bg-secondary">
                            <i class="fas fa-trophy me-1"></i> Rank #<?php 
                                try {
                                    $stmt = $conn->prepare("
                                        SELECT COUNT(*) + 1 as `rank` 
                                        FROM users 
                                        WHERE points > ?
                                    ");
                                    $stmt->execute([$currentUser['points'] ?? 0]);
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo $result ? $result['rank'] : 'N/A';
                                } catch (Exception $e) {
                                    echo 'N/A';
                                }
                            ?>
                        </span>
                    </div>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <h5><i class="fas fa-camera me-2"></i>Change Avatar</h5>
                        <div class="mb-3">
                            <label for="avatar" class="form-label">
                                <?php echo !empty($currentUser['avatar']) ? 'Change' : 'Upload'; ?> Profile Picture
                            </label>
                            <input class="form-control" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">

                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5><i class="fas fa-info-circle me-2"></i>About Me</h5>
                        <textarea class="form-control" name="bio" rows="4" 
                                  placeholder="Tell us something about yourself..."><?php echo htmlspecialchars($currentUser['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>My Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="display-6">
                                    <?php 
                                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blogs WHERE user_id = ?");
                                        $stmt->execute([$currentUser['id']]);
                                        echo $stmt->fetch()['count'];
                                    ?>
                                </h2>
                                <p class="mb-0 text-muted">Blog Posts</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="display-6">
                                    <?php 
                                        $stmt = $conn->prepare("
                                            SELECT COUNT(DISTINCT w.id) as count 
                                            FROM wargame_scores ws
                                            JOIN wargames w ON ws.wargame_id = w.id
                                            WHERE ws.user_id = ? AND w.end_date < NOW()
                                        ");
                                        $stmt->execute([$currentUser['id']]);
                                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $result ? (int)$result['count'] : 0;
                                    ?>
                                </h2>
                                <p class="mb-0 text-muted">War Games Played</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="display-6">
                                    <?php 
                                        $stmt = $conn->prepare("
                                            SELECT COUNT(*) as count 
                                            FROM wargame_scores 
                                            WHERE user_id = ?
                                        ");
                                        $stmt->execute([$currentUser['id']]);
                                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $result ? (int)$result['count'] : 0;
                                    ?>
                                </h2>
                                <p class="mb-0 text-muted">Challenges Solved</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
