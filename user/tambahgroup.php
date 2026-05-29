<?php
include '../koneksi/koneksi.php';
include '../koneksi/session.php';
autentikasi('login.php');
include 'layout/sidebar.php';

$user_id = $_SESSION['id_user'];

if (isset($_POST['create'])) {

  $nama = $_POST['nama_grub'];


  do {
    $code = strtoupper(substr(md5(rand()), 0, 6));
    $cek = mysqli_query($conn, "SELECT * FROM groups WHERE group_code = '$code'");
  } while(mysqli_num_rows($cek) > 0);

  mysqli_query($conn, "INSERT INTO groups (nama_grub, created_by, group_code)
    VALUES ('$nama', '$user_id', '$code')
  ");

  $group_id = mysqli_insert_id($conn);

  mysqli_query($conn, "INSERT INTO member (id_user, id_group, role) VALUES ($user_id, $group_id, 'admin')
  ");

  header("Location: ../user/group.php");
  exit();
}
?>

<?php
include '../koneksi/koneksi.php';

$user_id = $_SESSION['id_user'];

if(isset($_POST['join'])) {
    $code = strtoupper($_POST['group_code']);

    $query = mysqli_query($conn, "SELECT id_group FROM groups WHERE group_code = '$code'");

    if(mysqli_num_rows($query) == 0) {
        echo "kode grup tidak ditemukan!";
    } else {
        $data = mysqli_fetch_assoc($query);
        $group_id = $data['id_group'];

        $cek = mysqli_query($conn, "SELECT * FROM member WHERE id_user = $user_id AND id_group = $group_id");

        if(mysqli_num_rows($cek) > 0) {
            echo "Anda sudah bergabung di grub ini";
        } else {
            mysqli_query($conn, "INSERT INTO member (id_user, id_group, role) VALUES ($user_id, $group_id , 'member')");
            echo "Berhasil bergabung ke grup!";
            header("Location: ../user/isigroup.php?group=$group_id");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanaKita</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="layout">
        <?php
            sidebar('../koneksi/koneksi.php', '../index.php', 'group.php', 'tagihan.php', 'inbox.php','report.php', '../auth/logout.php', 'akun.php', 'group', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/mail.png', '../assets/clock.png', '../assets/person.png', '../assets/logout.png');
        ?>
        <div class="main-content">
            <div class="group-page">

                <h1>Group</h1>

                <div class="group-grid">

                    <!-- tambah group -->
                    <div class="group-card">

                        <h2>Buat Grup Baru</h2>

                        <form method="POST">

                            <input 
                                type="text"
                                name="nama_grub"
                                placeholder="Nama Grup"
                                required
                            >

                            <button type="submit" name="create">
                                Buat Grup
                            </button>

                        </form>

                    </div>

                    <!-- join group -->
                    <div class="group-card">

                        <h2>Gabung Grup</h2>

                        <form method="POST">

                            <input
                                type="text"
                                name="group_code"
                                placeholder="Masukkan kode grup"
                                required
                            >

                            <button type="submit" name="join">
                                Gabung
                            </button>

                        </form>

                    </div>

                </div>

            </div>
        </div>    
    </div>
    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer>     
</body>
</html>

