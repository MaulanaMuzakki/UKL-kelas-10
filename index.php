<?php
include 'koneksi/koneksi.php';
include 'user/layout/sidebar.php';
include 'koneksi/session.php';
autentikasi('auth/login.php');
?>



<?php
$user_id = $_SESSION['id_user'];

$query_saldo = mysqli_query($conn, "SELECT
SUM(CASE WHEN type='income' THEN amount ELSE 0 END) - SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS saldo
FROM transactions
WHERE group_id IN (
    SELECT id_group
    FROM member
    WHERE id_user = $user_id
)
");

$data_saldo = mysqli_fetch_assoc($query_saldo);
?>

<?php
$query_bayar = mysqli_query($conn, "SELECT SUM(amount) as total
FROM transactions
WHERE user_id = $user_id
AND type = 'income'
AND MONTH(date) = MONTH(CURRENT_DATE())
AND YEAR(date) = YEAR(CURRENT_DATE())
");

$data_bayar = mysqli_fetch_assoc($query_bayar);
?>

<?php
$query_group = mysqli_query($conn, "SELECT groups.id_group, groups.nama_grub FROM groups
JOIN member ON groups.id_group = member.id_group
WHERE member.id_user = $user_id
");
?>

<?php

$query_pembayaran = mysqli_query($conn, "SELECT 
transactions.amount,
transactions.date,
groups.nama_grub
FROM transactions
JOIN groups ON transactions.group_id = groups.id_group
WHERE transactions.user_id = $user_id
AND transactions.type = 'income'
ORDER BY transactions.date DESC
LIMIT 3
");

?>

<?php

$query_pengeluaran_dashboard = mysqli_query($conn, "SELECT
transactions.description,
transactions.amount,
transactions.date,
groups.nama_grub
FROM transactions
JOIN groups ON transactions.group_id = groups.id_group
JOIN member ON groups.id_group = member.id_group
WHERE member.id_user = $user_id
AND transactions.type = 'expense'
ORDER BY transactions.date DESC
LIMIT 3
");

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
    <div class="layout">
        <?php
            sidebar('index.php', 'user/group.php', 'user/tagihan.php', 'auth/logout.php', 'user/akun.php', 'dashboard', 'assets/chart-2.png', 'assets/people.png', 'assets/card-pos.png', 'assets/person.png', '    assets/logout.png');
        ?>
        <div class="main-content">
            <h1>Dashboard</h1>
            <div class="dashboard-atas">
                <div class="dashboard1">
                    <h3>Total Saldo</h3>
                    <h1 style="color: green;">Rp<?= number_format($data_saldo['saldo'] ?? 0, 0, ',', '.') ?></h1>
                </div>
                <div class="dashboard1">
                    <h3>Pembayaran bulan ini</h3>
                    <h1 style="color: Blue;">Rp<?= number_format($data_bayar['total'] ?? 0, 0, ',', '.') ?></h1>
                </div>
                <div class="dashboard1">
                    <h3>Tagihan Aktif</h3>
                        <div class="tagihan-mini">
                            <?php $total_show = 0; ?>
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
                                    <?php $total_show++; ?>

                                    <div class="tagihan-itemmini">

                                        <div>
                                            <h3><?= $group['nama_grub'] ?></h3>
                                        </div>

                                        <div class="statusnya unpaidnya">
                                            <p>
                                                Tagihan bulan <?= date('F Y') ?>
                                            </p>
                                        </div>

                                    </div>

                                <?php } ?>
                                <?php
                                    if($total_show >= 5){
                                        break;
                                    }
                                ?>

                            <?php } ?>
                            

                        </div>
                </div>
            </div>

            <br>

            <div class="dashboard-bawah">
                    <div class="dashboard1">
                        <h3>Pembayaran terakhir</h3>
                        <?php while($row = mysqli_fetch_assoc($query_pembayaran)) { ?>

                            <div class="dashboard-item">

                                <div class="item-left">

                                    <h4><?= $row['nama_grub'] ?></h4>

                                    <p>
                                        Rp <?= number_format($row['amount']) ?>
                                    </p>

                                </div>

                                <div class="item-right">

                                    <?= date('d M Y', strtotime($row['date'])) ?>

                                </div>

                            </div>

                        <?php } ?>
                    </div>
                    <div class="dashboard1">
                        <h3>pengeluaran grub terbaru</h3>
                        <?php while($row = mysqli_fetch_assoc($query_pengeluaran_dashboard)) { ?>

                        <div class="dashboard-item">

                            <div class="item-left">

                                <h4><?= $row['description'] ?></h4>

                                <p>
                                    <?= $row['nama_grub'] ?>
                                    •
                                    Rp <?= number_format($row['amount']) ?>
                                </p>

                            </div>

                            <div class="item-right">

                                <?= date('d M Y', strtotime($row['date'])) ?>

                            </div>

                        </div>

                        <?php } ?>
                    </div>
            </div>
        </div>
    </div>
    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer>        
</body>
</html>
