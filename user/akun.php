<?php
include '../koneksi/koneksi.php';
include '../koneksi/session.php';
autentikasi('../auth/login.php');
include 'layout/sidebar.php';

$user_id = $_SESSION['id_user'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $user_id
");

$user = mysqli_fetch_assoc($query);
?>

<?php
$query_group = mysqli_query($conn, "SELECT COUNT(*) as total_group FROM member WHERE id_user = $user_id
");

$data_group = mysqli_fetch_assoc($query_group);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="layout">
        <?php
            sidebar('../koneksi/koneksi.php', '../index.php', 'group.php', 'tagihan.php', 'inbox.php', 'report.php', '../auth/logout.php', 'akun.php', 'profil', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/mail.png', '../assets/clock.png', '../assets/person.png', '../assets/logout.png');
        ?>
        <div class="main-content">
            <div class="account-container">
            
                <h2>Informasi Akun</h2>

                <div class="profile-image">
                    <img src="../assets/user.png" alt="Profile">
                </div>

                <div class="info-item">
                    <span>Username</span>
                    <strong><?= $user['username'] ?></strong>
                </div>

                <div class="info-item">
                    <span>ID User</span>
                    <strong><?= $user['id_user'] ?></strong>
                </div>

                <div class="info-item">
                    <span>Akun Dibuat</span>
                    <strong><?= $user['created_at'] ?></strong>
                </div>

                <div class="info-item">
                    <span>Jumlah Grup</span>
                    <strong><?= $data_group['total_group'] ?></strong>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer> 
</body>
