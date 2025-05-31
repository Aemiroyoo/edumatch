<?php
include __DIR__ . '/includes/db_connect.php';
session_start();

// Cek apakah user sudah login sebagai murid
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Guru - EduMatch</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="search-container">
        <!-- Header Section -->
        <div class="header-section">
            <a href="dashboard.php" class="back-btn">
                <span>←</span> Kembali
            </a>
            <h1 class="page-title">Cari Guru</h1>
            <p class="page-subtitle">Temukan guru terbaik sesuai kebutuhan Anda</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="get" action="" class="filter-form">
                <select name="subject_id" class="filter-select">
                    <option value="">🎯 Semua Mata Pelajaran</option>
                    <?php
                    // Ambil daftar mata pelajaran
                    $subjects = $conn->query("SELECT * FROM subjects");
                    while ($subject = $subjects->fetch_assoc()) {
                        $selected = ($subject['id'] == ($_GET['subject_id'] ?? '')) ? 'selected' : '';
                        echo '<option value="'.$subject['id'].'" '.$selected.'>'
                             .htmlspecialchars($subject['name']).'</option>';
                    }
                    ?>
                </select>
                <button type="submit" class="filter-btn">🔍 Filter</button>
            </form>
        </div>

        <!-- Teachers Grid -->
        <div class="teachers-grid">
            <?php
            // Proses pengiriman request jika ada
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'])) {
                $teacher_id = $_POST['teacher_id'];
                $student_id = $_SESSION['user_id'];

                $stmt = $conn->prepare("INSERT INTO requests (student_id, teacher_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $student_id, $teacher_id);
                
                if ($stmt->execute()) {
                    header("Location: dashboard.php?status=request_sent");
                    exit;
                } else {
                    header("Location: search_teachers.php?status=request_failed");
                    exit;
                }
            }

            // Proses filter
            $subject_id = $_GET['subject_id'] ?? null;

            $sql = "SELECT users.id, profiles.full_name, profiles.skills, profiles.profile_picture 
                    FROM users 
                    JOIN profiles ON users.id = profiles.user_id
                    WHERE users.role = 'teacher'";

            if ($subject_id) {
                $sql .= " AND users.id IN (
                            SELECT teacher_id FROM teacher_subjects 
                            WHERE subject_id = ?
                          )";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $subject_id);
                $stmt->execute();
                $teachers = $stmt->get_result();
            } else {
                $teachers = $conn->query($sql);
            }

            // Tampilkan hasil
            if ($teachers->num_rows > 0) {
                while ($teacher = $teachers->fetch_assoc()) {
                    $profilePicture = $teacher['profile_picture'] ?? 'default.jpg';
                    $fullName = htmlspecialchars($teacher['full_name']);
                    $skills = htmlspecialchars($teacher['skills']);
                    $teacherId = $teacher['id'];
                    
                    echo "
                    <div class='teacher-card'>
                        <div class='teacher-info'>
                            <img src='uploads/{$profilePicture}' 
                                 alt='{$fullName}' 
                                 class='teacher-avatar'>
                            <div class='teacher-details'>
                                <h3>{$fullName}</h3>
                                <div class='teacher-skills'>
                                    <strong>🎓 Keahlian:</strong><br>
                                    {$skills}
                                </div>
                            </div>
                        </div>
                        <div class='teacher-actions'>
                            <button class='request-btn' onclick='showConfirmation({$teacherId})'>
                                📧 Ajukan Request
                            </button>
                            <div id='confirmation-{$teacherId}' class='confirmation-box'>
                                <p>✨ Yakin ingin mengajukan request ke <strong>{$fullName}</strong>?</p>
                                <div class='confirmation-btns'>
                                    <form method='post' style='display:contents;'>
                                        <input type='hidden' name='teacher_id' value='{$teacherId}'>
                                        <button type='submit' class='confirm-btn'>✅ Ya, Kirim</button>
                                    </form>
                                    <button class='cancel-btn' onclick='hideConfirmation({$teacherId})'>❌ Batal</button>
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "
                <div class='no-results'>
                    <div class='no-results-icon'>🔍</div>
                    <h3>Tidak ada guru yang ditemukan</h3>
                    <p>Coba ubah filter pencarian atau periksa kembali nanti</p>
                </div>";
            }
            ?>
        </div>
    </div>

    <script>
        function showConfirmation(teacherId) {
            const confirmationBox = document.getElementById("confirmation-" + teacherId);
            confirmationBox.style.display = "block";
            
            // Smooth scroll ke confirmation box
            confirmationBox.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
        
        function hideConfirmation(teacherId) {
            const confirmationBox = document.getElementById("confirmation-" + teacherId);
            confirmationBox.style.display = "none";
        }

        // Auto hide confirmation boxes when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.teacher-card')) {
                const confirmationBoxes = document.querySelectorAll('.confirmation-box');
                confirmationBoxes.forEach(function(box) {
                    box.style.display = 'none';
                });
            }
        });

        // Add loading state to filter button
        document.querySelector('.filter-form').addEventListener('submit', function() {
            const filterBtn = document.querySelector('.filter-btn');
            filterBtn.innerHTML = '⏳ Memuat...';
            filterBtn.disabled = true;
        });
    </script>
</body>
</html>