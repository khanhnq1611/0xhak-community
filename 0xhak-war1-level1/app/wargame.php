<?php
require_once 'includes/config.php';
$currentUser = getCurrentUser();
$pageTitle = 'War Games';

$conn = getDbConnection();
$selectedGameId = $_GET['id'] ?? null;

// Get all wargames
$wargames = $conn->query("SELECT * FROM wargames ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);

$gameDetails = null;
$leaderboard = [];

// Get details for selected game
if ($selectedGameId) {
    $stmt = $conn->prepare("SELECT * FROM wargames WHERE id = ?");
    $stmt->execute([$selectedGameId]);
    $gameDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($gameDetails) {
        // Get top 10 players for selected game
        $stmt = $conn->prepare("
            SELECT u.username, u.avatar, ws.score,
                   @rank := @rank + 1 as player_rank
            FROM (SELECT @rank := 0) r,
                 wargame_scores ws
            JOIN users u ON ws.user_id = u.id
            WHERE ws.wargame_id = ?
            ORDER BY ws.score DESC
            LIMIT 10
        ");
        $stmt->execute([$selectedGameId]);
        $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Past Events</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($wargames)): ?>
                    <div class="list-group-item">No wargames found.</div>
                <?php else: ?>
                    <?php foreach ($wargames as $game): ?>
                        <a href="?id=<?php echo $game['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo ($selectedGameId == $game['id']) ? 'active' : ''; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($game['name']); ?></h6>
                                <small><?php echo date('M j, Y', strtotime($game['start_date'])); ?></small>
                            </div>
                            <small>
                                <?php 
                                    $endDate = new DateTime($game['end_date']);
                                    $now = new DateTime();
                                    if ($endDate < $now) {
                                        echo '<span class="badge bg-secondary">Completed</span>';
                                    } else {
                                        echo '<span class="badge bg-success">Ongoing</span>';
                                    }
                                ?>
                            </small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <?php if ($selectedGameId && $gameDetails): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo htmlspecialchars($gameDetails['name']); ?></h4>
                    <span class="badge bg-primary">
                        <?php echo date('M j, Y', strtotime($gameDetails['start_date'])); ?> - 
                        <?php echo date('M j, Y', strtotime($gameDetails['end_date'])); ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($gameDetails['description'] ?? 'No description available.')); ?></p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Leaderboard</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($leaderboard)): ?>
                        <div class="p-4 text-center text-muted">
                            No leaderboard data available for this event.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="60">Rank</th>
                                        <th>Player</th>
                                        <th class="text-end">Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     <?php foreach ($leaderboard as $player): ?>
                                         <tr class="rank-<?php echo $player['player_rank']; ?>">
                                             <td>#<?php echo $player['player_rank']; ?></td>
                                             <td>
                                                 <img src="/display.php?file=<?php echo htmlspecialchars($player['avatar'] ?? 'default.png'); ?>"
                                                      class="avatar me-2"
                                                      alt="<?php echo htmlspecialchars($player['username']); ?>"
                                                      onerror="this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='">
                                                 <?php echo htmlspecialchars($player['username']); ?>
                                             </td>
                                            <td class="text-end">
                                                <span class="badge bg-primary"><?php echo number_format($player['score']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="display-1 text-muted mb-4">
                        <i class="fas fa-flag"></i>
                    </div>
                    <h3>Select a Wargame</h3>
                    <p class="lead text-muted mb-4">
                        Choose an event from the list to view its details and leaderboard.
                    </p>
                    <?php if (empty($wargames)): ?>
                        <div class="alert alert-warning">
                            No wargames have been created yet. Check back later for upcoming events!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
