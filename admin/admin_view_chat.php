<?php
session_start();
include __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$request_id = filter_var($_GET['request_id'], FILTER_VALIDATE_INT);
if (!$request_id) {
    header("Location: admin_dashboard.php");
    exit;
}

// Ambil data percakapan
$chats = $conn->query("SELECT c.*, p.full_name as sender_name 
                      FROM chats c
                      JOIN profiles p ON c.sender_id = p.user_id
                      WHERE c.request_id = $request_id
                      ORDER BY c.created_at");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Percakapan - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #ddd; }
        .chat-container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-height: 500px; overflow-y: auto; }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f8f9fa; }
        .message-header { font-weight: bold; margin-bottom: 5px; }
        .message-time { font-size: 0.8em; color: #666; }
        .back-btn { background-color: #2196F3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>History Percakapan</h1>
        <a href="admin_dashboard.php" class="back-btn">Kembali</a>
    </div>

    <div class="chat-container">
        <?php if ($chats->num_rows > 0): ?>
            <?php while ($chat = $chats->fetch_assoc()): ?>
                <div class="message">
                    <div class="message-header">
                        <?= htmlspecialchars($chat['sender_name']) ?>
                        <span class="message-time"><?= date('d/m/Y H:i', strtotime($chat['created_at'])) ?></span>
                    </div>
                    <div class="message-content">
                        <?= htmlspecialchars($chat['message']) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada percakapan</p>
        <?php endif; ?>
    </div>
</body>
</html>