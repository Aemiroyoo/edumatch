<?php
session_start();
include __DIR__ . '/includes/db_connect.php';

// Cek status request
$request_id = filter_var($_GET['request_id'], FILTER_VALIDATE_INT);
if ($request_id === false) {
    die("Invalid request ID");
}

// Ambil status request
$stmt = $conn->prepare("SELECT status FROM requests WHERE id = ? AND 
                       (student_id = ? OR teacher_id = ?)");
$stmt->bind_param("iii", $request_id, $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request || $request['status'] === 'completed') {
    header("Location: dashboard.php?status=invalid_chat_access");
    exit;
}

// Proses kirim pesan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO chats (request_id, sender_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $_POST['request_id'], $_SESSION['user_id'], $message);
        $stmt->execute();
    }
}

// Ambil history chat - sanitize request_id
$request_id = filter_var($_GET['request_id'], FILTER_VALIDATE_INT);
if ($request_id === false) {
    die("Invalid request ID");
}

$chats = $conn->query("
    SELECT c.*, p.full_name as sender_name 
    FROM chats c
    JOIN profiles p ON c.sender_id = p.user_id
    WHERE c.request_id = $request_id 
    ORDER BY c.created_at
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMatch Chat</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .chat-header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            position: relative;
        }

        .back-button {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            margin-right: 10px;
            cursor: pointer;
            padding: 8px 12px;
            display: flex;
            align-items: center;
        }

        .back-button:hover {
            opacity: 0.8;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background-color: #e5ddd5;
            background-image: url('https://web.whatsapp.com/img/bg-chat-tile-light_a4be512e7195b6b733d9110b408f075d.png');
        }
        .message {
            margin-bottom: 15px;
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 7.5px;
            position: relative;
            word-wrap: break-word;
        }
        .sent {
            background-color: #DCF8C6;
            margin-left: auto;
            margin-right: 0;
        }
        .received {
            background-color: white;
            margin-left: 0;
            margin-right: auto;
        }
        .message strong {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .message p {
            margin: 0;
            font-size: 14px;
        }
        .chat-input {
            display: flex;
            padding: 15px;
            background-color: #f0f0f0;
        }
        .chat-input textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
            outline: none;
            font-size: 14px;
            min-height: 40px;
            max-height: 100px;
        }
        .chat-input button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            margin-left: 10px;
            cursor: pointer;
            font-weight: bold;
        }
        .chat-input button:hover {
            background-color: #45a049;
        }
        .timestamp {
            font-size: 11px;
            color: #999;
            text-align: right;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <button class="back-button" onclick="window.location.href='dashboard.php'">
                &larr; Kembali
            </button>
            EduMatch Chat 
            <!-- - Request ID: <?= htmlspecialchars($request_id) ?> -->
        </div>
        <div class="chat-messages">
            <?php
                if ($chats) {
                    while ($chat = $chats->fetch_assoc()) {
                        $messageClass = ($chat['sender_id'] == $_SESSION['user_id']) ? 'sent' : 'received';
                        echo "<div class='message $messageClass'>
                                <strong>" . ($chat['sender_id'] == $_SESSION['user_id'] ? 'Anda' : htmlspecialchars($chat['sender_name'])) . "</strong>
                                <p>" . htmlspecialchars($chat['message']) . "</p>
                                <div class='timestamp'>" . date('H:i', strtotime($chat['created_at'])) . "</div>
                            </div>";
                    }
                } else {
                    echo "<div class='error'>Error loading chat messages: " . $conn->error . "</div>";
                }
            ?>
        </div>
        <form method="post" action="" class="chat-input">
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">
            <textarea name="message" placeholder="Ketik pesan Anda di sini..." required></textarea>
            <button type="submit">Kirim</button>
        </form>
    </div>

    <script>
        // Auto scroll ke bawah
        const chatMessages = document.querySelector('.chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Auto resize textarea
        const textarea = document.querySelector('textarea');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
    
</body>
</html>