<?php
    include '../koneksi/koneksi.php';
    include 'layout/sidebar.php';
    include '../koneksi/session.php';
    autentikasi('../auth/login.php');

    $group_id = isset($_GET['group']) ? (int) $_GET['group'] : 0;

    if ($group_id == 0) {
        die("Group tidak valid");
    }

    $user_id = $_SESSION['id_user'];

    if (isset($_POST['kick_member'])) {

        $kick_user = (int) $_POST['kick_user'];
        $kick_group = (int) $_POST['kick_group'];

        $cek_admin = mysqli_query($conn, " SELECT * FROM member
        WHERE id_user = $user_id
        AND id_group = $kick_group
        AND role = 'admin'
        ");

        if (mysqli_num_rows($cek_admin) > 0) {

            if ($kick_user != $user_id) {

                mysqli_query($conn, " DELETE FROM member
                WHERE id_user = $kick_user
                AND id_group = $kick_group
                AND role != 'admin'
                ");
            }
        }
    }
?>

<?php
    $cek = mysqli_query($conn, " SELECT * FROM member WHERE id_user = $user_id AND id_group = $group_id");

    if (mysqli_num_rows($cek) == 0) {
        die("Akses ditolak");
    }

    $data_member = mysqli_fetch_assoc($cek);
    $is_admin = $data_member['role'] == 'admin';

    $query_group = mysqli_query($conn, "SELECT * FROM groups WHERE id_group = $group_id");

    $data_group = mysqli_fetch_assoc($query_group);
?>

<?php
    $query_period = mysqli_query($conn, "SELECT * FROM payment_periods
    WHERE group_id='$group_id'
    ORDER BY end_date DESC
    LIMIT 1
    ");

    if(mysqli_num_rows($query_period)>0){

        $period = mysqli_fetch_assoc($query_period);

        $today = date('Y-m-d');

        // periode sudah lewat
        if($today > $period['end_date']){

            $start_date = date('Y-m-d', strtotime($period['end_date'].' +1 day'));

            if($data_group['payment_period'] == 'weekly'){
                $end_date = date('Y-m-d',strtotime($start_date.' +6 day'));

            }else{

                $end_date = date('Y-m-d', strtotime($start_date.' +29 day'));
            }

            mysqli_query($conn, "INSERT INTO payment_periods(group_id, period_type, start_date, end_date, payment_amount)
                VALUES('$group_id', '".$data_group['payment_period']."', '$start_date', '$end_date', '".$data_group['payment_amount']."')
            ");

        }

    }

?>

<?php
    $range = $_GET['range'] ?? 'week';

    if ($range == 'year') {
        $filter = "INTERVAL 1 YEAR";
    } elseif ($range == 'month') {
        $filter = "INTERVAL 1 MONTH";
    } else {
        $filter = "INTERVAL 7 DAY";
    }

    $query = mysqli_query($conn, "SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
         SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense
         FROM transactions WHERE group_id = $group_id 
         AND
         date >= CURDATE() - $filter
    ");

    $data = mysqli_fetch_assoc($query);

?>

<?php
    $query_saldo = mysqli_query($conn, "SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END)
     - 
     SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) 
     AS saldo FROM transactions WHERE group_id = $group_id
    ");

    $data_saldo = mysqli_fetch_assoc($query_saldo);
    $saldo = $data_saldo['saldo'] ?? 0;
?>

<?php
    if(isset($_POST['save_payment_setting'])){
        $payment_amount = $_POST['payment_amount'];
        $payment_period = $_POST['payment_period'];


        mysqli_query($conn, "UPDATE groups
             SET
             payment_amount = '$payment_amount',
             payment_period = '$payment_period'
             WHERE id_group = '$group_id'
        ");


        $query_period = mysqli_query($conn, "SELECT *
             FROM payment_periods
             WHERE group_id = '$group_id'
        ");


        if(mysqli_num_rows($query_period) <= 0){

            $start_date = date('Y-m-d');

            if($payment_period == 'weekly'){
                $end_date = date('Y-m-d', strtotime('+7 days'));

            }else{

                $end_date = date('Y-m-d',strtotime('+30 days'));
            }

            // insert periode pertama
            mysqli_query($conn, "INSERT INTO payment_periods(group_id, period_type, start_date, end_date, payment_amount)
                VALUES('$group_id', '$payment_period', '$start_date', '$end_date', '$payment_amount')
            ");
        }

        header("Location:isigroup.php?group=$group_id");
        exit;

    }
?>

<?php
    $query_pengeluaran = mysqli_query($conn, "SELECT amount, description, date 
         FROM transactions 
         WHERE group_id = $group_id 
         AND type = 'expense' 
         ORDER BY date DESC LIMIT 5
    ");
?>

<?php
    $query_member = mysqli_query($conn, "SELECT users.id_user, users.username, member.role
         FROM users
         JOIN member ON users.id_user = member.id_user
         WHERE member.id_group = $group_id
    ");

    function statusBayar($conn, $group_id, $member_id){

        $query_period = mysqli_query($conn, "SELECT *
        FROM payment_periods
        WHERE group_id = $group_id
        AND is_closed = 0
        LIMIT 1
        ");

        $period = mysqli_fetch_assoc($query_period);



        $query_total = mysqli_query($conn, "SELECT

        SUM(amount) as total

        FROM transactions

        WHERE

        user_id = $member_id

        AND group_id = $group_id

        AND type='income'

        AND id_period = ".$period['id_period']."

        ");



        $bayar = mysqli_fetch_assoc($query_total);



        $query_credit = mysqli_query($conn, "SELECT payment_credit

        FROM member

        WHERE
        id_user = $member_id

        AND id_group = $group_id

        ");



        $credit = mysqli_fetch_assoc($query_credit);



        $total = ($bayar['total'] ?? 0);

        $dipakai = min($total, $period['payment_amount']);



        $carry =
        max($total - $period['payment_amount'], 0);



        $kurang = max($period['payment_amount'] - ($total + ($credit['payment_credit'] ?? 0)), 0);



        return [

            'target'=>
            $period['payment_amount'],

            'bayar'=>
            $dipakai,

            'carry'=>
            $carry,

            'credit'=>
            ($credit['payment_credit'] ?? 0),

            'kurang'=>
            $kurang

        ];

    }
?>

<?php
    $code_group = $data_group['group_code'];
?>

<?php
    $query_role = mysqli_query($conn, "SELECT role FROM member 
         WHERE id_user = $user_id 
         AND id_group = $group_id
    ");

    $data_role = mysqli_fetch_assoc($query_role);
    $role = $data_role['role'] ?? '';
    ?>

<?php
    $query_member_select = mysqli_query($conn, "SELECT users.id_user, users.username
         FROM users
         JOIN member ON users.id_user = member.id_user
         WHERE member.id_group = $group_id
    ");
?>

<?php

    if(isset($_POST['submit_request'])){

        $request_user = $_SESSION['id_user'];
        $amount = $_POST['amount'];
        $note = $_POST['description'];
        $date = date('Y-m-d');
        $proof = '';



        // upload bukti

        if(!empty($_FILES['proof']['name'])){
            $filename =
            time().'_'.
            $_FILES['proof']['name'];
            move_uploaded_file($_FILES['proof']['tmp_name'], "../assets/".$filename);
            $proof = $filename;
        }



       mysqli_query($conn, "INSERT INTO payment_requests(
            user_id,
            group_id,
            amount,
            proof_image,
            note

        )

        VALUES(

            '$request_user',

            '$group_id',

            '$amount',

            '$proof',

            '$note'

        )");

        $request_id = mysqli_insert_id($conn);



        // cari admin

        $admin_query = mysqli_query($conn,"SELECT id_user  FROM member WHERE id_group='$group_id' AND role='admin' LIMIT 1");



        $admin = mysqli_fetch_assoc($admin_query);



        mysqli_query($conn, "INSERT INTO notifications(

            receiver_id,

            group_id,

            type,

            title,

            message,

            reference_id

        )

        VALUES(

            '".$admin['id_user']."',

            '$group_id',

            'payment_request',

            'Permintaan pembayaran',

            'Ada pembayaran menunggu konfirmasi',

            '$request_id'

        )");
    }

?>

<?php
    if (isset($_POST['keluar'])) {
        $user_id = $_SESSION['id_user'];

        mysqli_query($conn, "DELETE FROM member 
        WHERE id_user = '$user_id' AND id_group = '$group_id'");

        header("Location: group.php");
        exit;
    }
?>

<?php
    if(isset($_POST['make_admin'])){

        $new_admin = $_POST['new_admin'];

        mysqli_query($conn, "UPDATE member 
            SET role='member'
            WHERE id_group = $group_id
            AND role='admin'
        ");

        mysqli_query($conn, "UPDATE member
            SET role='admin'
            WHERE id_group = $group_id
            AND id_user = $new_admin
        ");

        header("Location:isigroup.php?group=$group_id");
        exit;
}
?>

<?php

    if(isset($_POST['send_bill'])){

        $target_user = $_POST['target_user'];



        mysqli_query($conn, "INSERT INTO notifications(

            receiver_id,

            group_id,

            type,

            title,

            message

        )

        VALUES(

            '$target_user',

            '$group_id',

            'bill',

            'Tagihan pembayaran',

            'Admin mengirim pengingat pembayaran'

        )");



        header("Location:isigroup.php?group=$group_id");

        exit;

    }

?>

<?php

    $income_data = [];
    $expense_data = [];
    $labels = [];

    for($i = 29; $i >= 0; $i--){

        $tanggal = date('Y-m-d', strtotime("-$i days"));

        $labels[] = date('d M', strtotime($tanggal));

        // pemasukan
        $income_query = mysqli_query($conn, "SELECT SUM(amount) as total
             FROM transactions
             WHERE group_id = $group_id
             AND type = 'income'
             AND DATE(date) = '$tanggal'
        ");

        $income = mysqli_fetch_assoc($income_query);

        $income_data[] = $income['total'] ?? 0;


        // pengeluaran
        $expense_query = mysqli_query($conn, "SELECT SUM(amount) as total
             FROM transactions
             WHERE group_id = $group_id
             AND type = 'expense'
             AND DATE(date) = '$tanggal'
        ");

        $expense = mysqli_fetch_assoc($expense_query);

        $expense_data[] = $expense['total'] ?? 0;
    }
?>

<?php

if(isset($_POST['save_expense'])){

    $amount = $_POST['amount'];

    $category =
    $_POST['expense_category'];

    $description =
    $_POST['description'];

    $date =
    $_POST['date'];



    mysqli_query($conn, "INSERT INTO transactions(

        user_id,

        group_id,

        amount,

        description,

        expense_category,

        date,

        type

    )

    VALUES(

        '$user_id',

        '$group_id',

        '$amount',

        '$description',

        '$category',

        '$date',

        'expense'

    )");



    header(
        "Location:isigroup.php?group=$group_id"
    );

    exit;

}

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanaKita - <?php echo $data_group['nama_grub']; ?></title>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="../style/script.js">
    <script>
        function openModal() {
            document.getElementById("modalPembayaran").style.display = "block";
        }

        function closeModal() {
            document.getElementById("modalPembayaran").style.display = "none";
        }


        function openExpenseModal(){

        document
        .getElementById(
        'modalPengeluaran'
        )
        .style.display='block';

        }



        function closeExpenseModal(){

        document
        .getElementById(
        'modalPengeluaran'
        )
        .style.display='none';

        }

        // klik di luar modal = tutup
        window.onclick = function (event) {
            let modal = document.getElementById("modalPembayaran");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   
</head>

<body>
    <div class="layout">
        <?php
            sidebar('../koneksi/koneksi.php','../index.php', 'group.php', 'tagihan.php', 'inbox.php','report.php', '../auth/logout.php', 'akun.php', 'group', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/mail.png', '../assets/clock.png', '../assets/person.png', '../assets/logout.png');
        ?>
        <div class="main-content">
            <h1 style="margin-bottom: 3px;">Group > <?php echo $data_group['nama_grub']; ?></h1>
            <h3>Code Group: <?php echo $code_group; ?></h3>
            <div class="wrap-isigrub">
                <div class="four-dalam">
                    <div class="atas-saldo">
                        <div class="saldo-flex">

                            <!-- kiri -->
                            <div class="saldo-kiri">

                                <h1>Saldo</h1>

                                <h2>Rp <?= number_format($saldo) ?></h2>
                                <button class="btn-dark" onclick="openModal()">Ajukan Pembayaran</button>
                                <?php if($role == 'admin'){ ?>
                                    <button class="btn-dark" onclick="openExpenseModal()">Catat Pengeluaran</button>
                                <?php } ?>
                                <div id="modalPembayaran" class="modal">
                                    <div class="modal-content">
                                        <span class="close" onclick="closeModal()">&times;</span>
                                        <h2>Ajukan Pembayaran</h2>
                                        <label>Member</label>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label>Jumlah</label>
                                                <input type="number" name="amount" required>
                                            </div>
                                            <br>

                                            <div class="form-group">
                                                <label>Catatan</label>
                                                <input type="text" name="description">
                                            </div><br>

                                              <div class="form-group">
                                                <label>Bukti Transfer</label>
                                                <input type="file" name="proof" required>
                                            </div><br>

                                            <div class="form-group">
                                                <label>Tanggal</label>
                                                <input type="date" name="date" required>
                                            </div><br>

                                           <button name="submit_request" type="submit">Simpan</button>
                                        </form>

                                    </div>
                                </div>
                                <div
                                    id="modalPengeluaran"
                                    class="modal"
                                >

                                    <div class="modal-content">

                                        <span
                                            class="close"
                                            onclick="closeExpenseModal()"
                                        >

                                            &times;

                                        </span>

                                        <h2>

                                            Catat Pengeluaran

                                        </h2>

                                        <form method="POST">

                                            <div class="form-group">

                                                <label>

                                                    Jumlah

                                                </label>

                                                <input
                                                    type="number"
                                                    name="amount"
                                                    required
                                                >

                                            </div>

                                            <br>

                                            <div class="form-group">

                                                <label>

                                                    Kategori

                                                </label>

                                                <select
                                                    name="expense_category"
                                                    required
                                                >

                                                    <option value="konsumsi">

                                                        Konsumsi

                                                    </option>

                                                    <option value="transport">

                                                        Transport

                                                    </option>

                                                    <option value="atk">

                                                        ATK

                                                    </option>

                                                    <option value="perlengkapan">

                                                        Perlengkapan

                                                    </option>

                                                    <option value="lainnya">

                                                        Lainnya

                                                    </option>

                                                </select>

                                            </div>

                                            <br>

                                            <div class="form-group">

                                                <label>

                                                    Keterangan

                                                </label>

                                                <input
                                                    type="text"
                                                    name="description"
                                                >

                                            </div>

                                            <br>

                                            <div class="form-group">

                                                <label>

                                                    Tanggal

                                                </label>

                                                <input
                                                    type="date"
                                                    name="date"
                                                    required
                                                >

                                            </div>

                                            <br>

                                            <button
                                                name="save_expense"
                                                type="submit"
                                            >

                                                Simpan

                                            </button>

                                        </form>

                                    </div>

                                </div>
                            </div>

                            <!-- kanan -->
                            <div class="aturan-bayar">

                                <h3>Nominal bayar</h3>

                                <h2>
                                    Rp <?= number_format($data_group['payment_amount'] ?? 0) ?>
                                    /
                                    <?= $data_group['payment_period'] == 'weekly' ? 'minggu' : 'bulan' ?>
                                </h2>

                                <?php

                                    $status_user =
                                    statusBayar(

                                    $conn,

                                    $group_id,

                                    $user_id

                                    );

                                ?>

                                <div class="status-bayar">

                                    Bayar:
                                    Rp <?= number_format($status_user['bayar']) ?>

                                    <br>

                                    Tagihan:
                                    Rp <?= number_format($status_user['kurang']) ?>



                                    <?php if($status_user['carry'] > 0){ ?>
                                        <br>

                                        Deposit:
                                        Rp <?= number_format($status_user['carry']) ?>

                                    <?php } ?>

                                </div>

                                <?php if($is_admin) { ?>

                                    <form method="POST" class="form-payment-setting">

                                        <div class="payment-input-row">

                                            <input
                                                type="number"
                                                name="payment_amount"
                                                placeholder="Nominal"
                                                value="<?= $data_group['payment_amount'] ?? 0 ?>"
                                                required
                                            >

                                            <select name="payment_period">

                                                <option
                                                    value="weekly"
                                                    <?= ($data_group['payment_period'] == 'weekly') ? 'selected' : '' ?>
                                                >
                                                    Mingguan
                                                </option>

                                                <option
                                                    value="monthly"
                                                    <?= ($data_group['payment_period'] == 'monthly') ? 'selected' : '' ?>
                                                >
                                                    Bulanan
                                                </option>

                                            </select>

                                        </div>

                                        <button
                                            type="submit"
                                            name="save_payment_setting"
                                        >
                                            Simpan
                                        </button>

                                    </form>

                                <?php } ?>

                            </div>

                        </div>
                    </div>
                    <div class="two-tengah">
                        <div class="pemasukan">
                            <h4>pemasukan</h4>
                            <p>Rp <?php echo number_format($data['total_income'] ?? 0); ?></p>
                            <form method="GET">
                                <input type="hidden" name="group" value="<?php echo $group_id; ?>">
                                <select name="range" onchange="this.form.submit()" style="margin: 0;">
                                    <option value="week" <?php if (($range) == 'week')
                                        echo 'selected'; ?>>7 Hari terakhir</option>
                                    <option value="month" <?php if (($range) == 'month')
                                        echo 'selected'; ?>>30 Hari terakhir</option>
                                    <option value="year" <?php if (($range) == 'year')
                                        echo 'selected'; ?>>1 Tahun terakhir</option>
                                </select>
                            </form>
                        </div>
                        <div class="pengeluaran">
                            <h4>pengeluaran</h4>
                            <p>Rp <?php echo number_format($data['total_expense'] ?? 0); ?></p>
                            <form method="GET">
                                <input type="hidden" name="group" value="<?php echo $group_id; ?>">

                                <select name="range" onchange="this.form.submit()" style="margin: 0;">
                                    <option value="week" <?php if (($range) == 'week')
                                        echo 'selected'; ?>>7 Hari terakhir</option>
                                    <option value="month" <?php if (($range) == 'month')
                                        echo 'selected'; ?>>30 Hari terakhir</option>
                                    <option value="year" <?php if (($range) == 'year')
                                        echo 'selected'; ?>>1 Tahun terakhir</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="bawah-aktivitas">
                        <div class="bawah-grafik">
                            <h4>Grafik</h4>
                            <div class="chart-container">
                                <canvas id="financeChart"></canvas>
                            </div>
                        </div>
                        <div class="bawah-pembelian">
                            <h4>pembelian</h4>
                            <div class="list-pembelian">
                                <?php while ($row = mysqli_fetch_assoc($query_pengeluaran)) {?>
                                    <div class="pembelian-item">

                                        <div class="left">
                                            <?= $row['description'] ?>
                                            -
                                            Rp <?= number_format($row['amount']) ?>
                                        </div>

                                        <div class="right">
                                            <?= $row['date'] ?>
                                        </div>

                                    </div>

                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="list-member">
                    <h4>Anggota</h4>
                    <form method="POST" action="">
                        <input type="hidden" name="group_id" value="group_id">
                        <button type="submit" name="keluar" style="margin-bottom: 10px;">Keluar Grup</button>
                    </form>
                    <?php while ($row = mysqli_fetch_assoc($query_member)) { ?>

                        <div class="member-item">

                            <span>
                                <?= $row['username'] ?>

                                <?php if ($row['role'] == 'admin') { ?>
                                    <small>(Admin)</small>
                                <?php } ?>
                            </span>

                            <?php
                            if (
                                $is_admin && $row['id_user'] != $user_id && $row['role'] != 'admin'
                            ) { ?>

                                <?php
                                if(
                                    $is_admin && $row['id_user'] != $user_id
                                ){ ?>

                                <div class="dropdown">

                                    <button class="manage-btn">
                                        Kelola
                                    </button>

                                    <div class="dropdown-content">

                                        <?php

                                         $status = statusBayar($conn, $group_id, $row['id_user']);

                                        ?>

                                        <div class="dropdown-item">

                                            Bayar:
                                            Rp <?= number_format($status['bayar']) ?>

                                        </div>

                                        <?php if($status['carry'] > 0){ ?>

                                            <div class="dropdown-item">

                                                 Deposit:
                                                 Rp <?= number_format($status['carry']) ?>

                                            </div>

                                        <?php } ?>

                                        <div class="dropdown-item">

                                        kurang:
                                        Rp <?= number_format($status['kurang']) ?>

                                        </div>

                                        <form method="POST">

                                            <input type="hidden" name="target_user" value="<?= $row['id_user'] ?>">

                                            <button type="submit" name="send_bill" class="dropdown-item">

                                                Kirim Tagihan

                                            </button>

                                        </form>

                                        <!-- jadikan admin -->
                                        <form method="POST">

                                            <input type="hidden" name="new_admin" value="<?= $row['id_user'] ?>">

                                            <button type="submit" name="make_admin" class="dropdown-item">
                                                Jadikan Admin
                                            </button>

                                        </form>

                                        <!-- kick -->
                                        <form method="POST">

                                            <input type="hidden" name="kick_user" value="<?= $row['id_user'] ?>">

                                            <input type="hidden" name="kick_group" value="<?= $group_id ?>">

                                            <button type="submit" name="kick_member"class="dropdown-item danger" onclick="return confirm('Kick member ini?')">
                                                Kick Member
                                            </button>

                                        </form>

                                    </div>

                                </div>

                                <?php } ?>

                            <?php } ?>

                        </div>

                    <?php } ?>
                </div>
            </div>
            <script>

                const incomeData = <?= json_encode($income_data) ?>;
                const expenseData = <?= json_encode($expense_data) ?>;

                const ctx = document.getElementById('financeChart');

                new Chart(ctx, {

                    type:'line',

                    data:{

                        labels: <?= json_encode($labels) ?>,

                        datasets:[

                            {
                                label:'Pengeluaran',

                                data:expenseData,

                                borderColor:'#FF3B30',

                                backgroundColor:'transparent',

                                borderWidth:2,

                                tension:0,

                                pointRadius:0
                            },

                            {
                                label:'Pemasukan',

                                data:incomeData,

                                borderColor:'#39D353',

                                backgroundColor:'transparent',

                                borderWidth:2,

                                tension:0,

                                pointRadius:0
                            }

                        ]
                    },

                    options:{

                        responsive:true,

                        maintainAspectRatio:false,

                        plugins:{

                            legend:{

                                position:'bottom',

                                labels:{

                                    usePointStyle:true,

                                    pointStyle:'circle',

                                    padding:30,

                                    font:{
                                        size:16
                                    }
                                }
                            }
                        },

                        scales:{

                            x:{
                                display:false,

                                grid:{
                                    display:false
                                }
                            },

                            y:{
                                display:false,

                                grid:{
                                    display:false
                                }
                            }
                        }
                    }
                });

            </script>
        </div>    
    </div>
    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer> 
</body>

</html>