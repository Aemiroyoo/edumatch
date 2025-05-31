<?php
include __DIR__ . '/includes/db_connect.php';

// Validasi parameter
if (!isset($_GET['user_id']) || !isset($_GET['role'])) {
    die("Parameter tidak lengkap!");
}

$user_id = $_GET['user_id'];
$role = $_GET['role'];

// Ambil daftar mata pelajaran dari database
$subjects = $conn->query("SELECT * FROM subjects ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lengkapi Profil</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
            line-height: 1.6;
        }
        .form-container {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], 
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        select {
            height: 40px;
            background-color: white;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .note {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Lengkapi Profil</h2>
        <form action="process/process_profile.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
            
            <div class="form-group">
                <label for="full_name">Nama Lengkap:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            
            <?php if ($role == 'teacher') : ?>
                <div class="form-group">
                    <label for="skills">Keahlian Utama:</label>
                    <select id="skills" name="skills" required>
                        <option value="">-- Pilih Keahlian --</option>
                        <?php while ($subject = $subjects->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($subject['name']) ?>">
                                <?= htmlspecialchars($subject['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <p class="note">Pilih satu keahlian utama Anda</p>
                </div>
            <?php else : ?>
                <div class="form-group">
                    <label for="training_field">Bidang yang Dipelajari:</label>
                    <select id="training_field" name="training_field" required>
                        <option value="">-- Pilih Bidang --</option>
                        <?php 
                        // Reset pointer hasil query
                        $subjects->data_seek(0);
                        while ($subject = $subjects->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($subject['name']) ?>">
                                <?= htmlspecialchars($subject['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="location">Lokasi:</label>
                <input type="text" id="location" name="location" required>
            </div>
            
            <div class="form-group">
                <label for="profile_picture">Foto Profil (max 2MB):</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
            </div>
            
            <div class="form-group">
                <label for="id_card">Upload KTP/Kartu Pelajar:</label>
                <input type="file" id="id_card" name="id_card" accept="image/*,application/pdf" required>
                <p class="note">Format: JPG, PNG, atau PDF (max 2MB)</p>
            </div>
            
            <button type="submit">Simpan Profil</button>
        </form>
    </div>
</body>
</html>