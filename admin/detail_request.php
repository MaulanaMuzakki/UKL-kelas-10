<?php
include '../koneksi/koneksi.php';
include '../koneksi/session.php';
include '../user/layout/sidebar.php';

autentikasi('../auth/login.php');

$user_id = $_SESSION['id_user'];

$id_request = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query_request = mysqli_query($conn, "SELECT
payment_requests.*,
users.username,
groups.nama_grub

FROM payment_requests

JOIN users
ON users.id_user = payment_requests.user_id

JOIN groups
ON groups.id_group = payment_requests.group_id

WHERE id_request = $id_request

AND status='pending'
");

$data = mysqli_fetch_assoc($query_request);

if(!$data){

    die('Data tidak ditemukan');
}
?>

<?php

if(isset($_POST['approve'])){

    $group_id = $data['group_id'];

    $member_id = $data['user_id'];



    $query_period = mysqli_query($conn, "SELECT *

        FROM payment_periods

        WHERE group_id = $group_id

        AND is_closed = 0

        ORDER BY id_period DESC

        LIMIT 1
        ");

    $period = mysqli_fetch_assoc($query_period);



    $payment_amount = $period['payment_amount'];



    $query_credit = mysqli_query($conn, "SELECT payment_credit

        FROM member

        WHERE id_user = $member_id

        AND id_group = $group_id
        ");



    $data_credit = mysqli_fetch_assoc($query_credit);



        $credit = $data_credit['payment_credit'] ?? 0;



        $query_total = mysqli_query($conn, "SELECT

        SUM(amount) as total

        FROM transactions

        WHERE

        user_id = $member_id

        AND group_id = $group_id

        AND type='income'

        AND id_period = ".$period['id_period']

        );



        $data_total =
        mysqli_fetch_assoc($query_total);



        $total_sebelumnya =
        $data_total['total']
        ?? 0;



        $total_bayar =
        $total_sebelumnya

        +

        $data['amount']

        +

        $credit;



        $sisa =
        max(

        $total_bayar

        -

        $payment_amount,

        0

        );



        $kurang =
        max(

        $payment_amount

        -

        $total_bayar,

        0

        );



    mysqli_query($conn, "UPDATE payment_requests
        SET
        status='approved',
        confirmed_at=NOW()

        WHERE id_request='$id_request'");




    mysqli_query($conn, "INSERT INTO transactions(
        user_id,
        group_id,
        amount,
        description,
        date,
        type,
        id_period,
        payment_method
    )

    VALUES(

        '$member_id',

        '$group_id',

        '".$data['amount']."',

        'Pembayaran member',

        CURDATE(),

        'income',

        '".$period['id_period']."',

        'transfer'
    )

    ");



    mysqli_query($conn, "UPDATE member
    SET last_payment_date = CURDATE(), payment_credit = ".max($sisa, 0)."
    WHERE
    id_user = $member_id
    AND id_group = $group_id
    ");




    mysqli_query($conn, "INSERT INTO notifications(
        receiver_id,
        group_id,
        type,
        title,
        message
    )

    VALUES(

        '$member_id',

        '$group_id',

        'payment_approved',

        'Pembayaran diterima',

        'Pembayaran berhasil dikonfirmasi admin'

    )");


  if($kurang == 0){

    $hapus = mysqli_query($conn, "DELETE
    FROM notifications

    WHERE receiver_id = $member_id

    AND group_id = $group_id

    AND type='bill'
    ");

    var_dump($hapus);

  }



    mysqli_query($conn, "DELETE
        FROM notifications

        WHERE reference_id = $id_request
    ");



    header("Location:../user/inbox.php");

    exit;

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Konfirmasi Pembayaran</title>

<link rel="stylesheet" href="../style/style.css">

</head>

<body>

<div class="layout">

    <?php
        sidebar('../koneksi/koneksi.php', '../index.php', '../user/group.php', '../user/tagihan.php', '../user/inbox.php', '../user/report.php', '../auth/logout.php', '../user/akun.php', 'inbox', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/mail.png', '../assets/clock.png', '../assets/person.png', '../assets/logout.png');
    ?>

    <div class="main-content">

        <h1>Konfirmasi Pembayaran</h1>

        <div class="request-card">

            <h2>
                <?= $data['nama_grub'] ?>
            </h2>

            <p>

                Member:
                <?= $data['username'] ?>

            </p>

            <p>

                Nominal:
                Rp <?= number_format($data['amount']) ?>

            </p>

            <p>

                Catatan:
                <?= $data['note'] ?>

            </p>

            <p>

                Status:
                <b>

                    <?= $data['status'] ?>

                </b>

            </p>

            <img src="../assets/<?= $data['proof_image'] ?>">
            <?php if($data['status']=='pending'){ ?>

                <form method="POST">

                    <button name="approve">

                        Setujui

                    </button>

                    <button name="reject">

                        Tolak

                    </button>

                </form>
            <?php } ?>

        </div>

    </div>

</div>

</body>

</html>