<?php
include '../koneksi/koneksi.php';
include '../koneksi/session.php';
autentikasi('../auth/login.php');
include 'layout/sidebar.php';

$user_id = $_SESSION['id_user'];

$query_group = mysqli_query($conn, "SELECT groups.id_group,groups.nama_grub, groups.payment_period, groups.payment_amount, member.payment_credit
FROM groups
JOIN member
ON groups.id_group = member.id_group
WHERE member.id_user = $user_id
");

$total_tagihan = 0;
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
        sidebar('../koneksi/koneksi.php','../index.php', 'group.php', 'tagihan.php', 'inbox.php','report.php', '../auth/logout.php', 'akun.php', 'bills', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/mail.png', '../assets/clock.png', '../assets/person.png', '../assets/logout.png');
        ?>
        <div class="main-content">

            <h1>Tagihan Saya</h1>

            <?php while($group = mysqli_fetch_assoc($query_group)) { ?>

                <?php

                $id_group = $group['id_group'];

                $payment_period = $group['payment_period'];

                $id_group = $group['id_group'];

                $query_period = mysqli_query($conn, "SELECT *

                FROM payment_periods

                WHERE group_id = $id_group

                AND is_closed = 0

                LIMIT 1
                ");



                $period =
                mysqli_fetch_assoc($query_period);



                if(!$period){

                    continue;

                }



                $query_bayar = mysqli_query($conn, "SELECT

                SUM(amount) as total

                FROM transactions

                WHERE

                user_id = $user_id

                AND group_id = $id_group

                AND type='income'

                AND id_period = ".$period['id_period']

                );



                $data_bayar =
                mysqli_fetch_assoc($query_bayar);



                $total_bayar =
                ($data_bayar['total'] ?? 0);



                $credit =
                ($group['payment_credit'] ?? 0);



                $target =
                $period['payment_amount'];



                $kurang =
                max(

                $target

                -

                ($total_bayar + $credit),

                0

                );



                $lunas =
                $kurang == 0;
                    

                ?> 
                <?php if($kurang > 0) { ?>

                    <div class="tagihan-item">

                        <div>
                            <h3><?= $group['nama_grub'] ?></h3>
                        </div>

                        <div class="status unpaid">
                            <p>

                            Tagihan

                            <?= $group['payment_period']=='weekly'

                            ? 'minggu ini'

                            : 'bulan ini'

                            ?>

                            </p>

                            <p>

                            Target:
                            Rp <?= number_format($target) ?>

                            </p>

                            <p>

                            Sudah bayar:
                            Rp <?= number_format($total_bayar) ?>

                            </p>

                            <p style="color:red;">

                            Kurang:
                            Rp <?= number_format($kurang) ?>
                            <?php

                                $lebih =
                                max(

                                $total_bayar - $target,

                                0

                                );

                            ?>

                            <?php if($lebih > 0){ ?>

                                <p style="color:green;">

                                Carry over:
                                Rp <?= number_format($lebih) ?>

                                </p>

                            <?php } ?>

                            </p>
                        </div>

                    </div>
                    <?php $total_tagihan++; ?>

                <?php } ?> 

            <?php } ?>
            
            <?php if($total_tagihan == 0) { ?>
                <div class="kosong">

                    <h2>

                    Semua tagihan sudah lunas

                    </h2>

                    <p>

                    Tidak ada kekurangan pembayaran

                    </p>

                </div>
            <?php } ?>

        </div>
    </div>  
    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer>   
</body>
</html>