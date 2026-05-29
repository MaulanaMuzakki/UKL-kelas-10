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
?><?php

$query_masuk = mysqli_query($conn, "SELECT
SUM(amount) AS total
FROM transactions
WHERE type='income'
AND date >= CURDATE()-INTERVAL 7 DAY
AND group_id IN(
SELECT id_group
FROM member
WHERE id_user=$user_id
)
");

$data_masuk = mysqli_fetch_assoc($query_masuk);



$query_keluar = mysqli_query($conn, "SELECT
SUM(amount) AS total
FROM transactions
WHERE type='expense'
AND date >= CURDATE()-INTERVAL 7 DAY
AND group_id IN(
SELECT id_group
FROM member
WHERE id_user=$user_id
)
");

$data_keluar = mysqli_fetch_assoc($query_keluar);



$query_grup_stat = mysqli_query($conn, "SELECT
COUNT(*) total,
SUM(role='admin') admin_total

FROM member

WHERE id_user=$user_id
");

$data_grup = mysqli_fetch_assoc($query_grup_stat);



$query_pending = mysqli_query($conn, "SELECT
COUNT(*) total

FROM notifications

WHERE receiver_id=$user_id

AND type='payment_request'
");

$data_pending = mysqli_fetch_assoc($query_pending);

?>

<?php

$query_preview = mysqli_query($conn, "SELECT

payment_requests.amount,

users.username

FROM payment_requests

JOIN users

ON users.id_user=
payment_requests.user_id

JOIN member

ON member.id_group=
payment_requests.group_id

WHERE

member.id_user=$user_id

AND member.role='admin'

AND payment_requests.status='pending'

LIMIT 2
");

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

<?php

$query_credit = mysqli_query($conn, "SELECT

SUM(payment_credit) total

FROM member

WHERE id_user = $user_id

");

$data_credit = mysqli_fetch_assoc($query_credit);



$query_belum = mysqli_query($conn, "SELECT

COUNT(*) total

FROM notifications

WHERE receiver_id = $user_id

AND type='bill'

");

$data_belum = mysqli_fetch_assoc($query_belum);

?>

<?php
$tagihan = [];

$query_bill = mysqli_query($conn, "

SELECT

groups.id_group,
groups.nama_grub,

payment_periods.payment_amount,

member.payment_credit

FROM member

JOIN groups
ON groups.id_group = member.id_group

LEFT JOIN payment_periods
ON payment_periods.group_id = groups.id_group
AND payment_periods.is_closed = 0

WHERE member.id_user='$user_id'

");

while($data = mysqli_fetch_assoc($query_bill)){

    $target =
    $data['payment_amount']
    ??
    0;

    $credit =
    $data['payment_credit']
    ??
    0;

    $kurang =
    max(
        $target - $credit,
        0
    );

    if($kurang > 0){

        $tagihan[] = [

            'group'=>
            $data['nama_grub'],

            'id'=>
            $data['id_group'],

            'kurang'=>
            $kurang

        ];

    }

}
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
            sidebar('koneksi/koneksi.php','index.php', 'user/group.php', 'user/tagihan.php', 'user/inbox.php', 'user/report.php', 'auth/logout.php', 'user/akun.php', 'dashboard', 'assets/chart-2.png', 'assets/people.png', 'assets/card-pos.png', 'assets/mail.png', 'assets/clock.png', 'assets/person.png', 'assets/logout.png');
        ?>
        <div class="main-content">
            <h1>Dashboard</h1>
            <div class="dashboard-atas">

                <div class="dashboard-card">

                <h3>Total Saldo</h3>

                <h1 class="green">
                Rp<?= number_format($data_saldo['saldo'] ?? 0,0,',','.') ?>
                </h1>

                <div class="mini-stat">

                <div>

                <small>Masuk 7 hari</small>

                <p>
                Rp<?= number_format($data_masuk['total'] ?? 0) ?>
                </p>

                </div>

                <div>

                <small>Keluar 7 hari</small>

                <p>
                Rp<?= number_format($data_keluar['total'] ?? 0) ?>
                </p>

                </div>

                </div>

                </div>



                <div class="dashboard-card">

                    <h2>
                        Tagihan Aktif
                    </h2>

                    <?php if(count($tagihan)>0){ ?>

                        <div class="bill-list">

                            <?php foreach($tagihan as $bill){ ?>

                                <div class="bill-item">

                                    <div>

                                        <b>

                                            <?= $bill['group'] ?>

                                        </b>

                                        <p>

                                            Belum bayar
                                            Rp<?= number_format($bill['kurang']) ?>

                                        </p>

                                    </div>

                                    <a
                                    class="bill-btn"

                                    href="user/isigroup.php?group=<?= $bill['id'] ?>">

                                        Buka Grup

                                    </a>

                                </div>

                            <?php } ?>

                        </div>

                        <small>

                            Total
                            <?= count($tagihan) ?>
                            tagihan aktif

                        </small>

                    <?php }else{ ?>

                        <div class="dashboard-empty">

                            ✓ Semua tagihan lunas

                        </div>

                    <?php } ?>

                </div>


                <div class="dashboard-card">

            <?php if($data_pending['total'] > 0){ ?>

                <h3>

                    Konfirmasi Bayar

                </h3>

                <h1 class="blue">

                    <?= $data_pending['total'] ?>

                </h1>

                <p>

                    Menunggu persetujuan

                </p>

                <?php
                while(
                    $row =
                    mysqli_fetch_assoc(
                        $query_preview
                    )
                ){
                ?>

                    <div class="dashboard-request">

                        <b>

                            <?= $row['username'] ?>

                        </b>

                        <br>

                        Rp
                        <?= number_format($row['amount']) ?>

                    </div>

                <?php } ?>

                <a
                    class="lihat-tagihan"
                    href="user/inbox.php"
                >

                    Buka Inbox

                </a>

            <?php } else { ?>

                <h3>

                    Grup Saya

                </h3>

                <h1 class="blue">

                    <?= $data_grup['total'] ?? 0 ?>

                </h1>

                <div class="mini-stat">

                    <div>

                        <small>

                            Admin

                        </small>

                        <p>

                            <?= $data_grup['admin_total'] ?? 0 ?>

                        </p>

                    </div>

                    <div>

                        <small>

                            Member

                        </small>

                        <p>

                            <?= ($data_grup['total'] ?? 0)
                            -
                            ($data_grup['admin_total'] ?? 0)
                            ?>

                        </p>

                    </div>

                </div>

            <?php } ?>

            </div>

                </div>


            <div class="dashboard-middle">

                <div class="quick-card">

                    <h3>Aksi Cepat</h3>

                    <div class="quick-grid">

                        <a href="user/group.php">

                            Grup

                        </a>

                        <a href="user/tagihan.php">

                            Tagihan

                        </a>

                        <a href="user/inbox.php">

                            Inbox

                        </a>

                        <a href="user/akun.php">

                            Akun

                        </a>

                    </div>

                </div>



                <div class="quick-card">

                    <h3>Status Saya</h3>

                    <div class="status-box">

                        <div>

                            <small>Tagihan aktif</small>

                            <h2>

                                <?= $data_belum['total'] ?>

                            </h2>

                        </div>



                        <div>

                            <small>Carry Over</small>

                            <h2>

                                Rp<?= number_format($data_credit['total'] ?? 0) ?>

                            </h2>

                        </div>

                    </div>

                    <a
                        class="lihat-tagihan"
                        href="user/tagihan.php"
                    >

                        Lihat Tagihan

                    </a>

                </div>

            </div>

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
