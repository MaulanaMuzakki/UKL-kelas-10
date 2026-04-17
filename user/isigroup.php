<?php
include '../koneksi/koneksi.php';
include '../koneksi/session.php';
autentikasi('../auth/login.php');

$group_id = isset($_GET['group']) ? (int)$_GET['group'] : 0;

if ($group_id == 0) {
  die("Group tidak valid");
}

$user_id = $_SESSION['id_user'];

$cek = mysqli_query($conn, " SELECT * FROM member WHERE id_user = $user_id AND id_group = $group_id");

if (mysqli_num_rows($cek) == 0) {
  die("Akses ditolak");
}

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

$data = mysqli_fetch_assoc($query);;
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
    $query_member = mysqli_query($conn, "SELECT username FROM users JOIN member ON users.id_user = member.id_user WHERE member.id_group = $group_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanaKita - <?php echo $data_group['nama_grub']; ?></title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <h1>Group > <?php echo $data_group['nama_grub']; ?></h1>
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
                            <option value="week" <?php if(($range)=='week') echo 'selected'; ?>>7 Hari</option>
                            <option value="month" <?php if(($range)=='month') echo 'selected'; ?>>30 Hari</option>
                            <option value="year" <?php if(($range)=='year') echo 'selected'; ?>>1 Tahun</option>
                        </select>
                    </form>
                </div>
                <div class="pengeluaran">
                    <h4>pengeluaran</h4>
                    <p>Rp <?php echo number_format($data['total_expense'] ?? 0); ?></p> 
                    <form method="GET">
                    <input type="hidden" name="group" value="<?php echo $group_id; ?>">

                    <select name="range" onchange="this.form.submit()">
                        <option value="week" <?php if(($range)=='week') echo 'selected'; ?>>7 Hari</option>
                        <option value="month" <?php if(($range)=='month') echo 'selected'; ?>>30 Hari</option>
                        <option value="year" <?php if(($range)=='year') echo 'selected'; ?>>1 Tahun</option>
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
                    <p><?php while($row = mysqli_fetch_assoc($query_pengeluaran)) { echo $row['description'] . ' - Rp ' . number_format($row['amount']) . '<br>'; } ?></p>
                </div>
            </div>
        </div>
        <div class="list-member">
            <h4>Anggota</h4>
            <p><?php while($row = mysqli_fetch_assoc($query_member)) { echo $row['username'] . '<br>'; } ?></p>
        </div>
    </div>
</body>
</html>

