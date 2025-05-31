<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $teacher_id = $_POST['teacher_id'];
  $student_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("INSERT INTO requests (student_id, teacher_id) VALUES (?, ?)");
  $stmt->bind_param("ii", $student_id, $teacher_id);
  $stmt->execute();

  header("Location: dashboard.php?status=request_sent");
  exit;
}

$teacher_id = $_GET['teacher_id'];
?>
<form method="post" action="">
  <input type="hidden" name="teacher_id" value="<?= $teacher_id ?>">
  <button type="submit">Kirim Request</button>
</form>