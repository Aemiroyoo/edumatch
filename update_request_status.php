<?php
session_start();
include __DIR__ . '/includes/db_connect.php';

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    try {
        // Handle teacher actions (accept/reject)
        if ($role === 'teacher') {
            $stmt = $conn->prepare("UPDATE requests SET status = ? 
                                  WHERE id = ? AND teacher_id = ? AND status = 'pending'");
            $stmt->bind_param("sii", $status, $request_id, $user_id);
        } 
        // Handle student action (complete)
        elseif ($role === 'student' && $status === 'completed') {
            $stmt = $conn->prepare("UPDATE requests SET status = 'completed' 
                                  WHERE id = ? AND student_id = ? AND status = 'accepted'");
            $stmt->bind_param("ii", $request_id, $user_id);
        }
        // Invalid action
        else {
            header("Location: dashboard.php?status=invalid_action");
            exit;
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: dashboard.php?status=update_success");
            } else {
                header("Location: dashboard.php?status=update_failed&reason=no_rows_updated");
            }
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
        exit;
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: dashboard.php?status=update_failed&reason=db_error");
        exit;
    }
}

header("Location: dashboard.php");
exit;
?>