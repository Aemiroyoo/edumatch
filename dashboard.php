<?php
session_start();
include __DIR__ . '/includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek status notifikasi
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'update_success':
            echo '<script>alert("Status request berhasil diperbarui!");</script>';
            break;
        case 'update_failed':
            echo '<script>alert("Gagal memperbarui status request.");</script>';
            break;
        case 'invalid_action':
            echo '<script>alert("Aksi tidak valid.");</script>';
            break;
        case 'schedule_created':
    echo '<script>alert("Jadwal berhasil dibuat!");</script>';
    break;
case 'schedule_failed':
    echo '<script>alert("Gagal membuat jadwal.");</script>';
    break;
case 'invalid_request':
    echo '<script>alert("Request tidak valid atau tidak ditemukan.");</script>';
    break;
    }
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Ambil data user untuk ditampilkan
$stmt = $conn->prepare("SELECT full_name FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - EduMatch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome-section">
                <div class="user-avatar">
                    <?= strtoupper(substr($profile['full_name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="welcome-text">
                    <h1>Selamat datang, <?= htmlspecialchars($profile['full_name'] ?? 'Pengguna') ?></h1>
                    <p>Anda login sebagai <?= $role === 'teacher' ? 'Guru' : 'Murid' ?></p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>

        <div class="main-content">
            <?php if ($role === 'teacher') : ?>
                <!-- Tampilan untuk Guru -->
                <h2 class="section-title">
                    <i class="fas fa-users"></i>
                    Daftar Request Murid
                </h2>
                
                <?php
                $stmt = $conn->prepare("SELECT r.*, p.full_name, p.profile_picture 
                                    FROM requests r
                                    JOIN profiles p ON r.student_id = p.user_id
                                    WHERE r.teacher_id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $requests = $stmt->get_result();
                
                if ($requests->num_rows > 0) {
                    while ($request = $requests->fetch_assoc()) {
                        echo "<div class='card'>
                                <div class='request-card'>
                                    <div>";
                        
                        if (!empty($request['profile_picture']) && file_exists(__DIR__ . '/uploads/' . $request['profile_picture'])) {
                            echo "<img src='uploads/".htmlspecialchars($request['profile_picture'])."' class='profile-image' alt='Profile'>";
                        } else {
                            echo "<div class='profile-placeholder'>".strtoupper(substr($request['full_name'], 0, 1))."</div>";
                        }
                        
                        echo "</div>
                                <div class='request-info'>
                                    <h3>".htmlspecialchars($request['full_name'])."</h3>
                                    <div class='info-row'>
                                        <i class='fas fa-user'></i>
                                        <span>Request dari Murid</span>
                                    </div>";
                        
                        // Status badge
                        $status_class = '';
                        $status_text = '';
                        $status_icon = '';
                        
                        switch ($request['status']) {
                            case 'accepted':
                                $status_class = 'status-accepted';
                                $status_text = 'Diterima';
                                $status_icon = 'fas fa-check-circle';
                                break;
                            case 'rejected':
                                $status_class = 'status-rejected';
                                $status_text = 'Ditolak';
                                $status_icon = 'fas fa-times-circle';
                                break;
                            case 'completed':
                                $status_class = 'status-completed';
                                $status_text = 'Selesai';
                                $status_icon = 'fas fa-flag-checkered';
                                break;
                            default:
                                $status_class = 'status-pending';
                                $status_text = 'Menunggu Konfirmasi';
                                $status_icon = 'fas fa-clock';
                        }
                        
                        echo "<div class='info-row'>
                                <i class='fas fa-info-circle'></i>
                                <span class='status-badge ".$status_class."'>
                                    <i class='".$status_icon."'></i>
                                    ".$status_text."
                                </span>
                            </div>";

                        // Action buttons berdasarkan status
                        echo "<div class='action-buttons'>";
                        
                        switch ($request['status']) {
                            case 'accepted':
                                echo "<a href='chat.php?request_id=".$request['id']."' class='btn btn-chat'>
                                        <i class='fas fa-comments'></i>
                                        Buka Chat
                                      </a>";
                                break;
                            case 'completed':
                                // Cek apakah sudah ada jadwal
                                $schedule_stmt = $conn->prepare("SELECT * FROM schedules WHERE request_id = ?");
                                $schedule_stmt->bind_param("i", $request['id']);
                                $schedule_stmt->execute();
                                $schedule = $schedule_stmt->get_result()->fetch_assoc();
                                
                                if ($schedule) {
                                    echo "</div>
                                          <div class='schedule-info'>
                                            <h4><i class='fas fa-calendar-alt'></i> Jadwal Belajar</h4>
                                            <div class='schedule-details'>
                                                <div class='schedule-detail'>
                                                    <i class='fas fa-calendar'></i>
                                                    <span>" . date('d/m/Y', strtotime($schedule['schedule_date'])) . "</span>
                                                </div>
                                                <div class='schedule-detail'>
                                                    <i class='fas fa-clock'></i>
                                                    <span>" . date('H:i', strtotime($schedule['start_time'])) . " - " . date('H:i', strtotime($schedule['end_time'])) . "</span>
                                                </div>
                                            </div>
                                            <div class='schedule-detail'>
                                                <i class='fas fa-sticky-note'></i>
                                                <span>" . htmlspecialchars($schedule['notes'] ?: 'Tidak ada catatan') . "</span>
                                            </div>
                                          </div>";
                                } else {
                                    // Form buat jadwal
                                    echo "</div>
                                          <div class='schedule-form'>
                                            <h4><i class='fas fa-calendar-plus'></i> Buat Jadwal Belajar</h4>
                                            <form method='post' action='create_schedule.php'>
                                                <input type='hidden' name='request_id' value='".$request['id']."'>
                                                
                                                <div class='form-group'>
                                                    <label><i class='fas fa-calendar'></i> Tanggal</label>
                                                    <input type='date' name='schedule_date' class='form-control' required min='".date('Y-m-d')."'>
                                                </div>
                                                
                                                <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                                                    <div class='form-group'>
                                                        <label><i class='fas fa-clock'></i> Waktu Mulai</label>
                                                        <input type='time' name='start_time' class='form-control' required>
                                                    </div>
                                                    
                                                    <div class='form-group'>
                                                        <label><i class='fas fa-clock'></i> Waktu Selesai</label>
                                                        <input type='time' name='end_time' class='form-control' required>
                                                    </div>
                                                </div>
                                                
                                                <div class='form-group'>
                                                    <label><i class='fas fa-sticky-note'></i> Catatan</label>
                                                    <textarea name='notes' rows='3' class='form-control' placeholder='Tambahkan catatan atau instruksi khusus...'></textarea>
                                                </div>
                                                
                                                <button type='submit' class='btn btn-schedule'>
                                                    <i class='fas fa-plus'></i>
                                                    Buat Jadwal
                                                </button>
                                            </form>
                                          </div>";
                                }
                                break;
                            default: // pending
                                echo "<form method='post' action='update_request_status.php' style='display: inline;'>
                                        <input type='hidden' name='request_id' value='".$request['id']."'>
                                        <input type='hidden' name='status' value='accepted'>
                                        <button type='submit' class='btn btn-accept'>
                                            <i class='fas fa-check'></i>
                                            Terima
                                        </button>
                                      </form>
                                      <form method='post' action='update_request_status.php' style='display: inline;'>
                                        <input type='hidden' name='request_id' value='".$request['id']."'>
                                        <input type='hidden' name='status' value='rejected'>
                                        <button type='submit' class='btn btn-reject'>
                                            <i class='fas fa-times'></i>
                                            Tolak
                                        </button>
                                      </form>";
                        }
                        
                        echo "</div>
                                </div>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<div class='empty-state'>
                            <i class='fas fa-inbox'></i>
                            <h3>Belum ada request dari murid</h3>
                            <p>Request dari murid akan muncul di sini</p>
                          </div>";
                }
                ?>

            <?php elseif ($role === 'student') : ?>
                <!-- Tampilan untuk Murid -->
                <h2 class="section-title">
                    <i class="fas fa-graduation-cap"></i>
                    Menu Utama
                </h2>
                
                <a href="search_teachers.php" class="search-btn">
                    <i class="fas fa-search"></i>
                    Cari Guru
                </a>
                
                <!-- Daftar guru yang sudah di-request -->
                <?php
                try {
                    $stmt = $conn->prepare("SELECT r.*, p.full_name, p.skills, r.status, 
                                           s.schedule_date, s.start_time, s.end_time, s.notes as schedule_notes
                                           FROM requests r
                                           JOIN profiles p ON r.teacher_id = p.user_id
                                           LEFT JOIN schedules s ON r.id = s.request_id
                                           WHERE r.student_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $my_requests = $stmt->get_result();
                    
                    if ($my_requests->num_rows > 0) {
                        echo "<h3 class='section-title'>
                                <i class='fas fa-chalkboard-teacher'></i>
                                Guru yang Saya Request
                              </h3>";
                        
                        while ($request = $my_requests->fetch_assoc()) {
                            // Status styling
                            $status_class = '';
                            $status_text = '';
                            $status_icon = '';
                            
                            switch ($request['status']) {
                                case 'accepted':
                                    $status_class = 'status-accepted';
                                    $status_text = 'Diterima';
                                    $status_icon = 'fas fa-check-circle';
                                    break;
                                case 'rejected':
                                    $status_class = 'status-rejected';
                                    $status_text = 'Ditolak';
                                    $status_icon = 'fas fa-times-circle';
                                    break;
                                case 'completed':
                                    $status_class = 'status-completed';
                                    $status_text = 'Selesai';
                                    $status_icon = 'fas fa-flag-checkered';
                                    break;
                                default:
                                    $status_class = 'status-pending';
                                    $status_text = 'Menunggu Jawaban Guru';
                                    $status_icon = 'fas fa-clock';
                            }
                            
                            echo "<div class='card'>
                                    <div class='request-info'>
                                        <h3>".htmlspecialchars($request['full_name'])."</h3>
                                        
                                        <div class='info-row'>
                                            <i class='fas fa-brain'></i>
                                            <span>".htmlspecialchars($request['skills'])."</span>
                                        </div>
                                        
                                        <div class='info-row'>
                                            <i class='fas fa-info-circle'></i>
                                            <span class='status-badge ".$status_class."'>
                                                <i class='".$status_icon."'></i>
                                                ".$status_text."
                                            </span>
                                        </div>";

                            // Tampilkan jadwal jika ada
                            if ($request['schedule_date']) {
                                echo "<div class='schedule-info'>
                                        <h4><i class='fas fa-calendar-alt'></i> Jadwal Belajar</h4>
                                        <div class='schedule-details'>
                                            <div class='schedule-detail'>
                                                <i class='fas fa-calendar'></i>
                                                <span>" . date('d/m/Y', strtotime($request['schedule_date'])) . "</span>
                                            </div>
                                            <div class='schedule-detail'>
                                                <i class='fas fa-clock'></i>
                                                <span>" . date('H:i', strtotime($request['start_time'])) . " - " . date('H:i', strtotime($request['end_time'])) . "</span>
                                            </div>
                                        </div>
                                        <div class='schedule-detail'>
                                            <i class='fas fa-sticky-note'></i>
                                            <span>" . htmlspecialchars($request['schedule_notes'] ?: 'Tidak ada catatan dari guru') . "</span>
                                        </div>
                                      </div>";
                            }

                            // Action buttons
                            if ($request['status'] == 'accepted') {
                                echo "<div class='action-buttons'>
                                        <a href='chat.php?request_id=".$request['id']."' class='btn btn-chat'>
                                            <i class='fas fa-comments'></i>
                                            Mulai Chat
                                        </a>
                                        <form method='post' action='update_request_status.php' style='display: inline;'>
                                            <input type='hidden' name='request_id' value='".$request['id']."'>
                                            <input type='hidden' name='status' value='completed'>
                                            <button type='submit' class='btn btn-complete'>
                                                <i class='fas fa-flag-checkered'></i>
                                                Tandai Selesai
                                            </button>
                                        </form>
                                      </div>";
                            }

                            echo "</div>
                                </div>";
                        }
                    } else {
                        echo "<div class='empty-state'>
                                <i class='fas fa-search'></i>
                                <h3>Belum ada guru yang di-request</h3>
                                <p>Mulai cari dan request guru untuk memulai belajar</p>
                              </div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='card'>
                            <p style='color: #e74c3c;'><i class='fas fa-exclamation-triangle'></i> Gagal memuat data: ".htmlspecialchars($e->getMessage())."</p>
                          </div>";
                }
                ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>