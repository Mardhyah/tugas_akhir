<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "db_keycrypto";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi Gagal:" . mysqli_connect_error());
}
