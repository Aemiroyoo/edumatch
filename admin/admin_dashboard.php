<?php
session_start();
include __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Mendaftarkan admin baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_admin'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];

    $stmt = $conn->prepare("INSERT INTO admins (username, password, full_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $full_name);
    $stmt->execute();
}

// Ambil data untuk dashboard
$users_stmt = $conn->query("SELECT u.id, u.role, p.full_name, p.profile_picture FROM users u JOIN profiles p ON u.id = p.user_id");
$requests_stmt = $conn->query("SELECT r.id, r.status, 
                             (SELECT full_name FROM profiles WHERE user_id = r.student_id) as student_name,
                             (SELECT full_name FROM profiles WHERE user_id = r.teacher_id) as teacher_name
                             FROM requests r ORDER BY r.created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - EduMatch</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #ddd; }
        .logout-btn { background-color: #f44336; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .tab-container { display: flex; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; background: #eee; margin-right: 5px; border-radius: 5px 5px 0 0; }
        .tab.active { background: white; border-bottom: 2px solid #4CAF50; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .profile-img { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid #ddd;
        }
        .profile-img-error {
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 12px;
            border: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="tab-container">
        <div class="tab active" onclick="openTab('users')">Manajemen User</div>
        <div class="tab" onclick="openTab('requests')">Monitoring Request</div>
        <div class="tab" onclick="openTab('register')">Daftarkan Admin</div>
    </div>

    <div id="users" class="tab-content active">
        <div class="card">
            <h2>Daftar User</h2>
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_stmt->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php 
                            $profile_picture = $user['profile_picture'] ?? 'default.jpg';
                            $image_path = '../uploads/' . $profile_picture;
                            
                            // Cek apakah file gambar ada
                            if (file_exists(__DIR__ . '/' . $image_path) && !empty($user['profile_picture'])): 
                            ?>
                                <img src="<?= $image_path ?>" class="profile-img" alt="Profile Picture">
                            <?php else: ?>
                                <div class="profile-img-error">No Photo</div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <a href="admin_view_profile.php?user_id=<?= $user['id'] ?>">Lihat Profil</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="requests" class="tab-content">
        <div class="card">
            <h2>History Request Terbaru</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Murid</th>
                        <th>Guru</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $requests_stmt = $conn->query("SELECT r.id, r.status, r.created_at,
                                                (SELECT full_name FROM profiles WHERE user_id = r.student_id) as student_name,
                                                (SELECT full_name FROM profiles WHERE user_id = r.teacher_id) as teacher_name
                                                FROM requests r ORDER BY r.id DESC LIMIT 10");
                    
                    while ($request = $requests_stmt->fetch_assoc()): ?>
                    <tr>
                        <td><?= $request['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                        <td><?= htmlspecialchars($request['student_name']) ?></td>
                        <td><?= htmlspecialchars($request['teacher_name']) ?></td>
                        <td>
                            <span style="
                                color: <?= 
                                    $request['status'] == 'accepted' ? 'green' : 
                                    ($request['status'] == 'rejected' ? 'red' : 
                                    ($request['status'] == 'completed' ? 'blue' : 'orange'))
                                ?>; 
                                font-weight: bold;
                            ">
                                <?= htmlspecialchars($request['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="admin_view_chat.php?request_id=<?= $request['id'] ?>" 
                            style="color: #2196F3; text-decoration: none;">
                            Lihat Percakapan
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="register" class="tab-content">
        <div class="card">
            <h2>Daftarkan Admin Baru</h2>
            <form method="post">
                <div style="margin-bottom: 15px;">
                    <label>Username:</label>
                    <input type="text" name="username" required style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Password:</label>
                    <input type="password" name="password" required style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Nama Lengkap:</label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 8px;">
                </div>
                <button type="submit" name="register_admin">Daftarkan</button>
            </form>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Update active tab
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>