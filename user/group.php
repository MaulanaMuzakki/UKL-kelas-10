<?php
include '../koneksi/koneksi.php';
include 'layout/sidebar.php';
include '../koneksi/session.php';
autentikasi('../auth/login.php');
$user_id = $_SESSION['id_user'];

$query = mysqli_query($conn, "SELECT groups.id_group, groups.nama_grub FROM groups JOIN member ON groups.id_group = member.id_group WHERE member.id_user = $user_id");
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
            SideBar('../index.php', 'group.php', 'tagihan.php', '../auth/logout.php', 'akun.php','group', '../assets/chart-2.png', '../assets/people.png', '../assets/card-pos.png', '../assets/person.png', '../assets/logout.png');
        ?>
        <div class="main-content">
            <div class="atasan-group">
                <h1>Group</h1>
                <h3 class="tambah-grub"><a href="tambahgroup.php" style=" background-color:#f5e1c3; padding: 8px; text-decoration: none; font-weight: 700; color:black; border-radius:11px;">+ Tambah Group</a></h3>
            </div>
            <div class="outer">
                <?php while($row = mysqli_fetch_assoc($query)) { ?>
                    <div class="group1">
                        <a name="group" href="isigroup.php?group=<?php echo $row['id_group']; ?>"><?php echo $row['nama_grub']; ?></a>
                        <br>

                        <div class="group-img">
                        <img src="../assets/grub4.jpg" alt="Kelas">
                        </div>
                    </div>
                <?php } ?>   
            </div> 
        </div>    
    </div>
    <footer class="footer">
        © 2026 DanaKita. All rights reserved.
    </footer> 
</body>
</html>