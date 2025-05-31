<?php
session_start();
include __DIR__ . '/includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $notes = $_POST['notes'] ?? '';

    // Validasi data
    if (!$request_id || empty($schedule_date)) {
        header("Location: dashboard.php?status=invalid_data");
        exit;
    }

    // Verifikasi bahwa request ini milik guru yang login
    $stmt = $conn->prepare("SELECT id FROM requests WHERE id = ? AND teacher_id = ? AND status = 'completed'");
    $stmt->bind_param("ii", $request_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: dashboard.php?status=invalid_request");
        exit;
    }

    // Simpan jadwal
    $stmt = $conn->prepare("INSERT INTO schedules (request_id, schedule_date, start_time, end_time, notes) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $request_id, $schedule_date, $start_time, $end_time, $notes);

    if ($stmt->execute()) {
        header("Location: dashboard.php?status=schedule_created");
    } else {
        header("Location: dashboard.php?status=schedule_failed");
    }
    exit;
}

header("Location: dashboard.php");
exit;
?>