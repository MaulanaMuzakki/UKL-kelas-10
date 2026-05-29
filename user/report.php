<?php

include '../koneksi/koneksi.php';

include '../koneksi/session.php';

include 'layout/sidebar.php';

autentikasi('../auth/login.php');

$user_id =
$_SESSION['id_user'];

?>

<?php

$query_group =
mysqli_query($conn, "SELECT
groups.id_group,
groups.nama_grub
FROM groups
JOIN member
ON member.id_group=
groups.id_group
WHERE member.id_user=
$user_id
");

?>

<?php

$group_id = $_GET['group']?? 0;

$month = $_GET['month'] ?? date('m');

?>

<?php

$query_history =
mysqli_query($conn, "SELECT *
FROM transactions
WHERE
group_id='$group_id'
AND MONTH(date)=
'$month'
ORDER BY
date ASC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Report</title>

    <link
        rel="stylesheet"
        href="../style/style.css"
    >

</head>

<body>

<div class="layout">

    <?php

    sidebar('../koneksi/koneksi.php','../index.php', 'group.php', 'tagihan.php', 'inbox.php', 'report.php', '../auth/logout.php', 'akun.php', 'history', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/mail.png', '../assets/clock.png', '../assets/person.png', '../assets/logout.png');
    ?>

    <div class="main-content">

        <h1>Report</h1>

            <div class="report-card">

                <form
                    method="GET"
                    class="report-filter"
                >

                    <div class="filter-item">

                        <label>

                            Pilih Grup

                        </label>

                        <select name="group">

                            <?php while($group = mysqli_fetch_assoc($query_group)){ ?>

                                <option
                                    value="<?= $group['id_group'] ?>"
                                    <?= $group_id == $group['id_group'] ? 'selected' : '' ?>
                                >

                                    <?= $group['nama_grub'] ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>



                    <div class="filter-item">

                        <label>

                            Pilih Bulan

                        </label>

                        <select name="month">

                            <?php

                            for($i=1;$i<=12;$i++){

                            ?>

                            <option
                                value="<?= $i ?>"
                                <?= $month==$i ? 'selected' : '' ?>
                            >

                                <?= date(
                                    'F',
                                    mktime(
                                        0,
                                        0,
                                        0,
                                        $i,
                                        1
                                    )
                                ) ?>

                            </option>

                            <?php } ?>

                        </select>

                    </div>



                    <button type="submit">

                        Tampilkan

                    </button>

                </form>

            </div>


        <div class="table-wrapper">
            <table class="report-table">

                <thead>

                    <tr>

                        <th>Tanggal</th>

                        <th>Uraian</th>

                        <th>Kategori</th>

                        <th>Debet</th>

                        <th>Kredit</th>

                        <th>Saldo</th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                    $saldo = 0;

                    while($row = mysqli_fetch_assoc($query_history)){

                        if($row['type'] == 'income'){

                            $debet = $row['amount'];

                            $kredit = 0;

                            $saldo += $debet;

                        }else{

                            $debet = 0;

                            $kredit = $row['amount'];

                            $saldo -= $kredit;

                        }

                    ?>

                    <tr>

                        <td>

                            <?= $row['date'] ?>

                        </td>

                        <td>

                            <?= $row['description'] ?>

                        </td>

                        <td>

                            <?= $row['expense_category'] ?? '-' ?>

                        </td>

                        <td class="debet">

                            <?= $debet ? 'Rp '.number_format($debet) : '-' ?>

                        </td>

                        <td class="kredit">

                            <?= $kredit ? 'Rp '.number_format($kredit) : '-' ?>

                        </td>

                        <td>

                            Rp <?= number_format($saldo) ?>

                        </td>

                    </tr>

                    <?php } ?>

                </tbody>

            </table>
        </div>
    </div>

</div>

<footer class="footer">

© 2026 DanaKita. All rights reserved.

</footer>

</body>

</html>