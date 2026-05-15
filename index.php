<?php
include 'user/layout/sidebar.php';
sidebar('dashboard.php', 'user/group.php', 'bills.php', 'auth/logout.php', 'user/akun.php');
include 'koneksi/session.php';
autentikasi('auth/login.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanaKita</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div>
        <h1>Dashboard</h1>
        <div class="dashboard-atas">
            <div class="dashboard1">
                <h3>Total Saldo</h3>
                <p>....</p>
            </div>
            <div class="dashboard1">
                <h3>Pembayaran bulan ini</h3>
                <p>....</p>
            </div>
            <div class="dashboard1">
                <h3>Tagihan Aktif</h3>
                <p>....</p>
            </div>
        </div>

        <br>

        <div class="dashboard-bawah">
            <div class="dashboard1">
                <h3>Grafik</h3>
                <p>....</p>
            </div>
            <div class="kanan-tumpuk">
                <div class="dashboard1">
                    <h3>Pembayaran terakhir</h3>
                    <p>....</p>
                </div>
                <div class="dashboard1">
                    <h3>pengeluaran grub terbaru</h3>
                    <p>....</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>