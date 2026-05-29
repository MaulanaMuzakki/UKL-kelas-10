<?php function sidebar($koneksi, $dashboard, $group, $bills, $inbox, $history, $logout, $profil, $active, $dashboard_icon, $group_icon, $bills_icon, $inbox_icon, $history_icon, $profil_icon, $logout_icon) { 
    include $koneksi;

    $user_id = $_SESSION['id_user'] ?? 0;

    $query_notif = mysqli_query($conn, "SELECT COUNT(*) as total
    FROM notifications
    WHERE receiver_id = $user_id
    AND is_read = 0
    ");

    $data_notif = mysqli_fetch_assoc($query_notif);

    $total_notif = $data_notif['total'];    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanaKita</title>
    <link rel="stylesheet" href="../../style/style.css">
</head>
<body>

<div class="sidebar">

    <div class="logo">
        <a href="<?= $dashboard ?>" style="text-decoration: none;">
            <h2>DanaKita</h2>
        </a>
    </div>

    <div class="menu">

        <a href="<?= $dashboard ?>" class="menu-item <?= $active == 'dashboard' ? 'active' : '' ?>">
            <span><img src= <?= $dashboard_icon ?>></span>
            Beranda
        </a>

        <a href="<?= $group ?>" class="menu-item <?= $active == 'group' ? 'active' : '' ?>">
            <span><img src= <?= $group_icon ?>></span>
            Grub
        </a>

        <a href="<?= $bills ?>" class="menu-item <?= $active == 'bills' ? 'active' : '' ?>">
            <span><img src= <?= $bills_icon ?>></span>
            Tagihan
        </a>

        <a href="<?= $inbox ?>" class="menu-item <?= $active == 'inbox' ? 'active' : '' ?>">

            <span>
                <img src=<?= $inbox_icon ?>>
            </span>

            Kotak Masuk

            <?php if($total_notif > 0){ ?>

                <span class="notif-badge">

                    <?= $total_notif ?>

                </span>

            <?php } ?>
        </a>

         <a href="<?= $history ?>" class="menu-item <?= $active == 'history' ? 'active' : '' ?>">
            <span><img src= <?= $history_icon ?>></span>
            Riwayat
        </a>

    </div>

    <div class="bottom-menu">

        <a href="<?= $profil ?>" class="menu-item <?= $active == 'profil' ? 'active' : '' ?>">
            <span><img src= <?= $profil_icon ?>></span>
            Akun
        </a>

        <a href="<?= $logout ?>" class="menu-item">
            <span><img src= <?= $logout_icon ?>></span>
            Keluar
        </a>

    </div>

</div>

</body>
</html>
<?php } ?>