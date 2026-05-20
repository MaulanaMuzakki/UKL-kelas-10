<?php
include '../koneksi/koneksi.php';
include '../koneksi/session.php';
autentikasi('../auth/login.php');
include 'layout/sidebar.php';

$user_id = $_SESSION['id_user'];

$query_group = mysqli_query($conn, "SELECT 
groups.id_group,
groups.nama_grub,
groups.payment_period

FROM groups

JOIN member 
ON groups.id_group = member.id_group

WHERE member.id_user = $user_id
");
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan Saya</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="layout">
        <?php
        sidebar('../index.php', 'group.php', 'tagihan.php', '../auth/logout.php', 'akun.php', 'bills', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/person.png', '../assets/logout.png');
        ?>
        <div class="main-content">

            <h1>Tagihan Saya</h1>

            <?php while($group = mysqli_fetch_assoc($query_group)) { ?>

                <?php

                $id_group = $group['id_group'];

                $payment_period = $group['payment_period'];

                if($payment_period == 'weekly'){

                    $cek_bayar = mysqli_query($conn, "SELECT * FROM transactions

                    WHERE user_id = $user_id
                    AND group_id = $id_group
                    AND type = 'income'

                    AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
                    ");

                }else{

                    $cek_bayar = mysqli_query($conn, "SELECT * FROM transactions

                    WHERE user_id = $user_id
                    AND group_id = $id_group
                    AND type = 'income'

                    AND date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                    ");
                }

                $sudah_bayar = mysqli_num_rows($cek_bayar) > 0;

                ?>

                <?php $total_tagihan = 0; ?>    
                <?php if(!$sudah_bayar) { ?>

                    <div class="tagihan-item">

                        <div>
                            <h3><?= $group['nama_grub'] ?></h3>
                        </div>

                        <div class="status unpaid">
                            <p>

                                Tagihan 
                                <?= $payment_period == 'weekly' ? 'minggu ini' : 'bulan ini' ?>

                            </p>
                            <p>
                                belum dibayar
                            </p>
                        </div>

                    </div>
                    <?php $total_tagihan++; ?>

                <?php } ?> 

            <?php } ?>
            
            <?php if($total_tagihan == 0) { ?>
                <div class="kosong">
                <h2>Semua tagihan sudah dibayar</h2>
                </div>
            <?php } ?>

        </div>
    </div>  
    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer>   
</body>
</html>