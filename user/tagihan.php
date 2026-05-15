<?php
include '../koneksi/koneksi.php';
include '../koneksi/session.php';

$user_id = $_SESSION['id_user'];

$query_group = mysqli_query($conn, "SELECT groups.id_group, groups.nama_grub FROM groups
JOIN member ON groups.id_group = member.id_group
WHERE member.id_user = $user_id
");
?>

<link rel="stylesheet" href="../style/style.css">
<div class="tagihan-container">

    <h2>Tagihan Saya</h2>

    <?php while($group = mysqli_fetch_assoc($query_group)) { ?>

        <?php

        $id_group = $group['id_group'];

        $cek_bayar = mysqli_query($conn, "SELECT * FROM transactions
        WHERE user_id = $user_id
        AND group_id = $id_group
        AND MONTH(date) = MONTH(CURRENT_DATE())
        AND YEAR(date) = YEAR(CURRENT_DATE())
        ");

        $sudah_bayar = mysqli_num_rows($cek_bayar) > 0;

        ?>

        <?php if(!$sudah_bayar) { ?>

            <div class="tagihan-item">

                <div>
                    <h3><?= $group['nama_grub'] ?></h3>
                </div>

                <div class="status unpaid">
                     <p>
                        Tagihan bulan <?= date('F Y') ?>
                    </p>
                    <p>
                        belum dibayar
                    </p>
                </div>

            </div>

        <?php } ?>

    <?php } ?>

</div>