<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../includes/db_connect.php';

// Validasi input
if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
    die("Semua field harus diisi!");
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    die("Email tidak valid!");
}

// Cek duplikat email
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $_POST['email']);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    die("Email sudah terdaftar!");
}

// Proses registrasi
$role = $_POST['role'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

$insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$insert_stmt->bind_param("ssss", $username, $email, $password, $role);

if ($insert_stmt->execute()) {
    $user_id = $conn->insert_id;
    header("Location: ../complete_profile.php?user_id=" . $user_id . "&role=" . $role);
    exit;
} else {
    die("Registrasi gagal: " . $insert_stmt->error);
}
?>