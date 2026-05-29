<?php
    include '../koneksi/koneksi.php';
    include '../koneksi/session.php';
    include 'layout/sidebar.php';

    autentikasi('../auth/login.php');

    $user_id = $_SESSION['id_user'];

    mysqli_query($conn, "UPDATE notifications
    SET is_read = 1
    WHERE receiver_id = $user_id
    AND is_read = 0
    ");

    $query_notif = mysqli_query($conn, "SELECT *
         FROM notifications
         WHERE receiver_id = $user_id
         ORDER BY created_at DESC
    ");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <div class="layout">
        <?php
            sidebar('../koneksi/koneksi.php', '../index.php', 'group.php', 'tagihan.php', 'inbox.php', 'report.php', '../auth/logout.php', 'akun.php', 'inbox', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/mail.png', '../assets/clock.png', '../assets/person.png', '../assets/logout.png');
        ?>

        <div class="main-content">
            <h1>Inbox</h1>

            <div class="inbox-list">
                <?php while($notif = mysqli_fetch_assoc($query_notif)){ ?>
                    <a class="notif-link" href="../admin/detail_request.php?id=<?= $notif['reference_id'] ?>">

                        <?php

                            $link = '#';

                            if(
                                $notif['type']
                                ==
                                'payment_request'
                            ){

                                $link =
                                '../admin/detail_request.php?id='. $notif['reference_id'];

                            }else{

                                $link =
                                'isigroup.php?group='
                                .
                                $notif['group_id'];

                            }

                        ?>

                        

                        <div class="notif-item <?= $notif['type'] ?>">

                            <h3>
                                <?= $notif['title'] ?>
                            </h3>

                            <p>
                                <?= $notif['message'] ?>
                            </p>

                            <small>
                                <?= $notif['created_at'] ?>
                            </small>

                            <a href="isigroup.php?group=<?= $notif['group_id'] ?>">

                                Buka Grup

                            </a>
                        </div>

                    </a>

                <?php } ?>

                <?php if(mysqli_num_rows($query_notif) == 0){ ?>

                    <div class="notif-kosong">
                    Belum ada notifikasi
                    </div>
                <?php } ?>

            </div>

        </div>

    </div>

    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer>

</body>

</html>