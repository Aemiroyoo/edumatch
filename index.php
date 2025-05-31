<?php
session_start();
// Jika user sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>EduMatch - Pertemukan Guru dan Murid</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .hero {
            background: linear-gradient(135deg, #6e48aa 0%, #9d50bb 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }
        .cta-buttons {
            margin-top: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin: 0 0.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #fff;
            color: #6e48aa;
        }
        .btn-secondary {
            border: 2px solid #fff;
            color: #fff;
        }
    </style>
</head>
<body>
    <section class="hero">
        <h1>Selamat Datang di EduMatch</h1>
        <p>Platform untuk menghubungkan murid dengan guru profesional sesuai kebutuhan belajar Anda</p>
        
        <div class="cta-buttons">
            <a href="login.php" class="btn btn-primary">Masuk</a>
            <a href="register.php" class="btn btn-secondary">Daftar</a>
        </div>
    </section>

    <section style="padding: 2rem; text-align: center;">
        <h2>Fitur Kami</h2>
        <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 1rem;">
            <div style="flex: 1; max-width: 300px;">
                <h3>🔍 Cari Guru</h3>
                <p>Temukan guru berdasarkan mata pelajaran, lokasi, atau keahlian</p>
            </div>
            <div style="flex: 1; max-width: 300px;">
                <h3>💬 Chat Langsung</h3>
                <p>Negosiasi jadwal dan harga melalui sistem chat terintegrasi</p>
            </div>
            <div style="flex: 1; max-width: 300px;">
                <h3>📅 Kelola Jadwal</h3>
                <p>Sistem penjadwalan otomatis untuk sesi belajar</p>
            </div>
        </div>
    </section>
</body>
</html>