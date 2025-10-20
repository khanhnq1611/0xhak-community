<?php
// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'hak_community');
define('DB_USER', getenv('DB_USER') ?: 'hak_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'hak_password');

// Create connection
function getDbConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize database tables
function initializeDatabase($conn) {
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            avatar VARCHAR(255) DEFAULT 'default.png',
            bio TEXT,
            points INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS blogs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            file_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS wargames (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            start_date DATETIME,
            end_date DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS wargame_scores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            wargame_id INT NOT NULL,
            user_id INT NOT NULL,
            score INT NOT NULL DEFAULT 0,
            FOREIGN KEY (wargame_id) REFERENCES wargames(id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            UNIQUE KEY unique_wargame_user (wargame_id, user_id)
        )"
    ];

    try {
        foreach ($queries as $query) {
            $conn->exec($query);
        }
        // Insert sample data if tables are empty
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            // Insert sample users
            $sampleUsers = [
                ["admin", "admin@0xhak.com", password_hash('admin123', PASSWORD_DEFAULT), 1000],
                ["user1", "user1@0xhak.com", password_hash('user1123', PASSWORD_DEFAULT), 850],
                ["user2", "user2@0xhak.com", password_hash('user2123', PASSWORD_DEFAULT), 750],
                ["user3", "user3@0xhak.com", password_hash('user3123', PASSWORD_DEFAULT), 700],
                ["user4", "user4@0xhak.com", password_hash('user4123', PASSWORD_DEFAULT), 650],
                ["user5", "user5@0xhak.com", password_hash('user5123', PASSWORD_DEFAULT), 600],
                ["user6", "user6@0xhak.com", password_hash('user6123', PASSWORD_DEFAULT), 550],
                ["user7", "user7@0xhak.com", password_hash('user7123', PASSWORD_DEFAULT), 500],
                ["user8", "user8@0xhak.com", password_hash('user8123', PASSWORD_DEFAULT), 450],
                ["user9", "user9@0xhak.com", password_hash('user9123', PASSWORD_DEFAULT), 400]
            ];

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, points) VALUES (?, ?, ?, ?)");
            foreach ($sampleUsers as $user) {
                $stmt->execute($user);
            }

            // Insert sample wargames
            $wargames = [
                ["Spring CTF 2023", "Annual Spring Capture The Flag competition", "2023-03-15 10:00:00", "2023-03-17 22:00:00"],
                ["Hack The Box: 0xHAK Edition", "Special HTB competition for 0xHAK members", "2023-06-01 00:00:00", "2023-06-30 23:59:59"],
                ["Autumn CTF 2023", "Autumn edition of our CTF challenges", "2023-09-10 09:00:00", "2023-09-12 21:00:00"]
            ];

            $stmt = $conn->prepare("INSERT INTO wargames (name, description, start_date, end_date) VALUES (?, ?, ?, ?)");
            foreach ($wargames as $game) {
                $stmt->execute($game);
            }

            // Insert sample wargame scores
            $scores = [];
            for ($gameId = 1; $gameId <= 3; $gameId++) {
                for ($userId = 1; $userId <= 10; $userId++) {
                    $score = rand(100, 1000);
                    $scores[] = [$gameId, $userId, $score];
                }
            }

            $stmt = $conn->prepare("INSERT INTO wargame_scores (wargame_id, user_id, score) VALUES (?, ?, ?)");
            foreach ($scores as $score) {
                $stmt->execute($score);
            }
        }
    } catch(PDOException $e) {
        die("Error initializing database: " . $e->getMessage());
    }
}

// Initialize session
session_start();

// Handle file uploads
function handleFileUpload($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'md']) {
    $fileName = basename($file['name']);
    $targetFile = $targetDir . '/' . time() . '_' . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Sorry, only " . implode(', ', $allowedTypes) . " files are allowed.");
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("File is too large. Maximum size is 5MB.");
    }
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $targetFile;
    } else {
        throw new Exception("Error uploading file.");
    }
}

// Get current user
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Initialize database connection and tables
$conn = getDbConnection();
initializeDatabase($conn);
?>
