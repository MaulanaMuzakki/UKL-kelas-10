<?php
include '../koneksi/koneksi.php';
?>

// proses login

<?php
session_start();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $db_password = $row['password'];

        $_SESSION['id_user'] = $row['id_user'];

        if(password_verify($password, $db_password)) {
            header("Location: ../index.php");
            exit();
        }

        else {
            echo "Password salah!";
        }
    } else {
        echo "Nama pengguna tidak ditemukan!";
    }
}
?>

// proses register

<?php
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $hash_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $password = $hash_password;

    $sql = "INSERT INTO users (username, email, password)
             VALUES ('$username', '$email', '$password')";
    if (mysqli_query($conn, $sql)) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>
