<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "p@ssKeiCrypto";
$db_name = "banksampah";

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$koneksi) {
    die("Koneksi Gagal:" . mysqli_connect_error());
}
