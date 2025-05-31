<?php
include '../includes/db_connect.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'login') {
  $email = $_POST['email'];
  $password = $_POST['password'];
  
  $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Password salah']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan']);
  }
}
?>