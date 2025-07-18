<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bank Sampah</title>
    <link rel="stylesheet" href="/bank_sampah/assets/css/style.css">
</head>
<style>
    * {
        font-family: 'Poppins', sans-serif;
    }
</style>

<body>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Bank Sampah</span>
        </a>

        <?php
        $current_page = $_GET['page'] ?? 'dashboard';
        ?>
        <ul class="side-menu top">
            <li class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <a href="index.php?page=dashboard">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="<?= $current_page === 'sampah' ? 'active' : '' ?>">
                <a href="index.php?page=sampah">
                    <i class='bx bxs-trash'></i>
                    <span class="text">Sampah</span>
                </a>
            </li>
            <li class="<?= $current_page === 'setor_sampah' ? 'active' : '' ?>">
                <a href="index.php?page=setor_sampah">
                    <i class='bx bxs-plus-circle'></i>
                    <span class="text">Tambah Transaksi</span>
                </a>
            </li>
            <li class="<?= $current_page === 'semua_transaksi' ? 'active' : '' ?>">
                <a href="index.php?page=semua_transaksi">
                    <i class='bx bxs-detail'></i>
                    <span class="text">Semua Transaksi</span>
                </a>
            </li>
            <li class="<?= $current_page === 'rekap_transaksi' ? 'active' : '' ?>">
                <a href="index.php?page=rekap_transaksi">
                    <i class='bx bxs-report'></i>
                    <span class="text">Rekap Transaksi</span>
                </a>
            </li>
            <li class="<?= $current_page === 'admin' ? 'active' : '' ?>">
                <a href="index.php?page=admin">
                    <i class='bx bxs-user-badge'></i>
                    <span class="text">Admin</span>
                </a>
            </li>
            <li class="<?= $current_page === 'nasabah' ? 'active' : '' ?>">
                <a href="index.php?page=nasabah">
                    <i class='bx bxs-user-account'></i>
                    <span class="text">Nasabah</span>
                </a>
            </li>
            <li class="<?= $current_page === 'detail_user' ? 'active' : '' ?>">
                <a href="index.php?page=detail_user">
                    <i class='bx bxs-user-account'></i>
                    <span class="text">Detail User</span>
                </a>
            </li>


        </ul>
        </ul>
        <ul class="side-menu">

            <li>
                <a href="logout.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>


    </section>
    <!-- SIDEBAR -->
    </section>


</body>

</html>