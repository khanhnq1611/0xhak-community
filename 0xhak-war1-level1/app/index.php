<?php
require_once 'includes/config.php';
$currentUser = getCurrentUser();
$pageTitle = 'Home';

// Get top 10 users by points
$conn = getDbConnection();
$stmt = $conn->query("SELECT id, username, points, avatar FROM users ORDER BY points DESC LIMIT 10");
$topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-trophy me-2"></i>Leaderboard</h4>
                <span class="badge bg-primary">Top 10 Hackers</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Hacker</th>
                                <th class="text-end">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topUsers as $index => $user): ?>
                                <tr class="rank-<?php echo $index + 1; ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="/display.php?file=<?php echo htmlspecialchars($user['avatar']); ?>"
                                                 class="avatar me-2"
                                                 alt="<?php echo htmlspecialchars($user['username']); ?>"
                                                 onerror="this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary"><?php echo number_format($user['points']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Latest News</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <h6>Welcome to 0xHAK Community!</h6>
                        <p class="small text-muted mb-0">We're excited to launch our new platform for security enthusiasts.</p>
                    </div>
                    <div class="list-group-item">
                        <h6>Upcoming CTF Competition</h6>
                        <p class="small text-muted mb-0">Join our next Capture The Flag event starting next month.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Events</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Monthly Security Workshop</h6>
                            <small class="text-muted">Next Week</small>
                        </div>
                        <p class="small text-muted mb-0">Learn about the latest security vulnerabilities and how to protect against them.</p>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Guest Speaker: Jane Doe</h6>
                            <small class="text-muted">In 2 Weeks</small>
                        </div>
                        <p class="small text-muted mb-0">Renowned security researcher will share insights on modern web exploitation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
