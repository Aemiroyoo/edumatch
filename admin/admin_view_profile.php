<?php
session_start();
include __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
if (!$user_id) {
    header("Location: admin_dashboard.php");
    exit;
}

// Ambil data user
$stmt = $conn->prepare("SELECT u.id, u.role, u.email, p.* 
                       FROM users u 
                       JOIN profiles p ON u.id = p.user_id
                       WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil User - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #ddd; }
        .profile-container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .profile-header { display: flex; align-items: center; margin-bottom: 20px; }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-right: 20px; }
        .profile-details { flex: 1; }
        .back-btn { background-color: #2196F3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detail Profil User</h1>
        <a href="admin_dashboard.php" class="back-btn">Kembali</a>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <img src="uploads/<?= htmlspecialchars($user['profile_picture'] ?? 'default.jpg') ?>" class="profile-img">
            <div class="profile-details">
                <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <div class="profile-info">
            <h3>Informasi Profil</h3>
            <p><strong>Keahlian:</strong> <?= htmlspecialchars($user['skills'] ?? '-') ?></p>
            <p><strong>Deskripsi:</strong> <?= htmlspecialchars($user['description'] ?? '-') ?></p>
            <p><strong>Alamat:</strong> <?= htmlspecialchars($user['address'] ?? '-') ?></p>
            <p><strong>Nomor HP:</strong> <?= htmlspecialchars($user['phone_number'] ?? '-') ?></p>
        </div>
    </div>
</body>
</html>