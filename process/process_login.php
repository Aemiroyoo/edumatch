<?php
include __DIR__ . '/../includes/db_connect.php';

session_start();

if (empty($_POST['email']) || empty($_POST['password'])) {
    header("Location: ../login.php?error=empty_fields");
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

if (!$conn) {
    die("Koneksi database gagal");
}

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: ../dashboard.php");
        exit;
    } else {
        header("Location: ../login.php?error=wrong_password");
        exit;
    }
} else {
    header("Location: ../login.php?error=email_not_found");
    exit;
}
?>