<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "p@ssKeiCrypto";
$db_name = "dbkeycrypto";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi Gagal:" . mysqli_connect_error());
}
