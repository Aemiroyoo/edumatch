<?php
include __DIR__ . '/../includes/db_connect.php';

$uploadDir = __DIR__ . '/../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Fungsi untuk upload file
function uploadFile($file, $uploadDir) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    return null;
}

// Validasi input
if (empty($_POST['user_id']) || empty($_POST['full_name'])) {
    die("Data wajib tidak lengkap!");
}

// Data dari form
$user_id = $_POST['user_id'];
$role = $_POST['role']; // Tambahkan ini
$full_name = $_POST['full_name'];
$skills = $_POST['skills'] ?? null;
$training_field = $_POST['training_field'] ?? null;
$location = $_POST['location'];

// Upload file
$profile_pic = uploadFile($_FILES['profile_picture'], $uploadDir);
$id_card = uploadFile($_FILES['id_card'], $uploadDir);

if (!$profile_pic || !$id_card) {
    die("Gagal mengupload file. Pastikan format file benar (jpg/png/pdf) dan ukuran < 2MB");
}

// Mulai transaction
$conn->begin_transaction();

try {
    // Simpan ke tabel profiles
    $stmt = $conn->prepare("INSERT INTO profiles (user_id, full_name, skills, training_field, location, profile_picture, id_card) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $full_name, $skills, $training_field, $location, $profile_pic, $id_card);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan profil: " . $stmt->error);
    }

    // Jika guru, simpan ke teacher_subjects
    if ($role == 'teacher' && isset($_POST['skills'])) {
        // Ambil subject_id berdasarkan skill yang dipilih
        $skill_name = $_POST['skills'];
        $subject_query = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
        $subject_query->bind_param("s", $skill_name);
        
        if (!$subject_query->execute()) {
            throw new Exception("Gagal mencari mata pelajaran: " . $subject_query->error);
        }
        
        $subject = $subject_query->get_result()->fetch_assoc();
        
        if ($subject) {
            // Simpan relasi guru-mata pelajaran
            $insert_relation = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
            $insert_relation->bind_param("ii", $user_id, $subject['id']);
            
            if (!$insert_relation->execute()) {
                throw new Exception("Gagal menyimpan keahlian guru: " . $insert_relation->error);
            }
        }
    }

    // Commit transaction jika semua sukses
    $conn->commit();
    header("Location: ../login.php?registration=success");
    exit;

} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    die("Error: " . $e->getMessage());
}