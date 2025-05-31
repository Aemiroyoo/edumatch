<?php 
// Start session untuk error handling
session_start();
include __DIR__ . '/includes/db_connect.php';

// Tangani error dari proses registrasi sebelumnya
$error = $_GET['error'] ?? $_SESSION['register_error'] ?? null;
unset($_SESSION['register_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - EduMatch</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .register-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #4f46e5;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn-register {
            width: 100%;
            padding: 12px;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            background-color: #4338ca;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #6b7280;
        }
        
        .login-link a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: #ef4444;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            padding: 10px;
            background-color: #fee2e2;
            border-radius: 6px;
        }
        
        .role-description {
            font-size: 14px;
            color: #6b7280;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>Daftar EduMatch</h1>
            <p>Bergabunglah sebagai guru atau murid</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php
                $errorMessages = [
                    'empty_fields' => 'Semua field harus diisi',
                    'invalid_email' => 'Email tidak valid',
                    'email_exists' => 'Email sudah terdaftar',
                    'password_short' => 'Password minimal 6 karakter',
                    'upload_failed' => 'Gagal mengupload file'
                ];
                echo $errorMessages[$error] ?? 'Terjadi kesalahan saat registrasi';
                ?>
            </div>
        <?php endif; ?>
        
        <form action="process/process_register.php" method="post">
            <div class="form-group">
                <label for="role">Saya ingin mendaftar sebagai:</label>
                <select id="role" name="role" required>
                    <option value="">Pilih peran</option>
                    <option value="student">Murid</option>
                    <option value="teacher">Guru</option>
                </select>
                <div class="role-description" id="roleDescription">
                    Pilih peran Anda dalam platform
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Contoh: budi123" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Contoh: anda@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            
            <button type="submit" class="btn-register">Daftar Sekarang</button>
        </form>
        
        <div class="login-link">
            Sudah punya akun? <a href="login.php">Masuk disini</a>
        </div>
    </div>

    <script>
        // Dinamik deskripsi role
        const roleSelect = document.getElementById('role');
        const roleDescription = document.getElementById('roleDescription');
        
        roleSelect.addEventListener('change', function() {
            if (this.value === 'student') {
                roleDescription.textContent = 'Daftar sebagai murid untuk mencari guru sesuai kebutuhan belajar Anda';
            } else if (this.value === 'teacher') {
                roleDescription.textContent = 'Daftar sebagai guru untuk menawarkan jasa mengajar Anda';
            } else {
                roleDescription.textContent = 'Pilih peran Anda dalam platform';
            }
        });
    </script>
</body>
</html>