<?php
include '../koneksi/koneksi.php';
include 'layout/sidebar.php';
sidebar('../index.php', 'group.php', '../history.php', '../bills.php', '../auth/logout.php');
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
$range = $_GET['range'] ?? 'week';

if ($range == 'year') {
    $filter = "INTERVAL 1 YEAR";
} elseif ($range == 'month') {
    $filter = "INTERVAL 1 MONTH";
} else {
    $filter = "INTERVAL 7 DAY";
}

$query = mysqli_query($conn, "SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense FROM transactions WHERE group_id = $group_id AND date >= CURDATE() - $filter
");

$data = mysqli_fetch_assoc($query);
;
?>
<?php
$query_saldo = mysqli_query($conn, "SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) - SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS saldo FROM transactions WHERE group_id = $group_id
");

$data_saldo = mysqli_fetch_assoc($query_saldo);
$saldo = $data_saldo['saldo'] ?? 0;
?>

<?php
$query_pengeluaran = mysqli_query($conn, "SELECT amount, description, date FROM transactions WHERE group_id = $group_id AND type = 'expense' ORDER BY date DESC LIMIT 5
");
?>

<?php
$query_member = mysqli_query($conn, "SELECT 
    users.id_user,
    users.username,
    member.role
FROM users
JOIN member ON users.id_user = member.id_user
WHERE member.id_group = $group_id
");
?>

<?php
$code_group = $data_group['group_code'];
?>

<?php
$query_role = mysqli_query($conn, "SELECT role FROM member WHERE id_user = $user_id AND id_group = $group_id
");
$data_role = mysqli_fetch_assoc($query_role);
$role = $data_role['role'] ?? '';
?>

<?php
if (isset($_POST['bayar'])) {
    $user_id = $_SESSION['id_user'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $type = $_POST['type'];

    $query = "INSERT INTO transactions (user_id, group_id, amount, description, date, type) VALUES ('$user_id', '$group_id', '$amount', '$description', '$date', '$type')";

    mysqli_query($conn, $query);
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

        // klik di luar modal = tutup
        window.onclick = function (event) {
            let modal = document.getElementById("modalPembayaran");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</head>

<body>
    <h1>Group > <?php echo $data_group['nama_grub']; ?></h1>
    <h3>Code Group: <?php echo $code_group; ?></h3>
    <?php if ($role == 'admin') { ?>
        <button onclick="openModal()">Catat Pembayaran</button>

        <div id="modalPembayaran" class="modal">
            <div class="modal-content">

                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Tambah Pembayaran</h2>

                <form method="POST">
                    <label>Jumlah</label>
                    <input type="number" name="amount" required><br>

                    <label>jenis</label>
                    <select name="type" required>
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                    </select><br>

                    <label>Keterangan</label>
                    <input type="text" name="description"><br>

                    <label>Tanggal</label>
                    <input type="date" name="date" required><br>

                    <button name="bayar" type="submit">Simpan</button>
                </form>

            </div>
        </div>
    <?php } ?>
    <div class="wrap-isigrub">
        <div class="four-dalam">
            <div class="atas-saldo">
                <h4>Saldo</h4>
                <p>Rp <?php echo number_format($saldo); ?></p>
            </div>
            <div class="two-tengah">
                <div class="pemasukan">
                    <h4>pemasukan</h4>
                    <p>Rp <?php echo number_format($data['total_income'] ?? 0); ?></p>
                    <form method="GET">
                        <input type="hidden" name="group" value="<?php echo $group_id; ?>">
                        <select name="range" onchange="this.form.submit()">
                            <option value="week" <?php if (($range) == 'week')
                                echo 'selected'; ?>>7 Hari</option>
                            <option value="month" <?php if (($range) == 'month')
                                echo 'selected'; ?>>30 Hari</option>
                            <option value="year" <?php if (($range) == 'year')
                                echo 'selected'; ?>>1 Tahun</option>
                        </select>
                    </form>
                </div>
                <div class="pengeluaran">
                    <h4>pengeluaran</h4>
                    <p>Rp <?php echo number_format($data['total_expense'] ?? 0); ?></p>
                    <form method="GET">
                        <input type="hidden" name="group" value="<?php echo $group_id; ?>">

                        <select name="range" onchange="this.form.submit()">
                            <option value="week" <?php if (($range) == 'week')
                                echo 'selected'; ?>>7 Hari</option>
                            <option value="month" <?php if (($range) == 'month')
                                echo 'selected'; ?>>30 Hari</option>
                            <option value="year" <?php if (($range) == 'year')
                                echo 'selected'; ?>>1 Tahun</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="bawah-aktivitas">
                <div class="bawah-grafik">
                    <h4>Grafik</h4>
                    <p>....</p>
                </div>
                <div class="bawah-pembelian">
                    <h4>pembelian</h4>
                    <p><?php while ($row = mysqli_fetch_assoc($query_pengeluaran)) {
                        echo $row['description'] . ' - Rp ' . number_format($row['amount']) . '<br>';
                    } ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="list-member">
            <h4>Anggota</h4>
            <form method="POST" action="">
                <input type="hidden" name="group_id" value="group_id">
                <button type="submit" name="keluar">Keluar Grup</button>
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
                        $is_admin &&
                        $row['id_user'] != $user_id &&
                        $row['role'] != 'admin'
                    ) {
                        ?>

                        <form method="POST">

                            <input type="hidden" name="kick_user" value="<?= $row['id_user'] ?>">

                            <input type="hidden" name="kick_group" value="<?= $group_id ?>">

                            <button type="submit" name="kick_member" class="kick-btn"
                                onclick="return confirm('Keluarkan member ini?')">
                                Kick
                            </button>

                        </form>

                    <?php } ?>

                </div>

            <?php } ?>
        </div>
    </div>
</body>

</html>