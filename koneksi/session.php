<?php
session_start();

function autentikasi($lokasi) {
    if(!isset($_SESSION['id_user'])) {
    header("Location: $lokasi");
    exit();
}
}

?>