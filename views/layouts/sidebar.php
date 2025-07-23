<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/koneksi.php';

if (!isset($_SESSION['username'])) {
    header('Location: index.php?page=login');
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
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

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

            <?php if ($user_role === 'admin') : ?>
                <li class="<?= in_array($current_page, ['sampah', 'tambah_sampah', 'edit_sampah', 'manage_kategori', 'ubah_sampah']) ? 'active' : '' ?>">
                    <a href="index.php?page=sampah">
                        <i class='bx bxs-trash'></i>
                        <span class="text">Sampah</span>
                    </a>
                </li>
                <li class="<?= in_array($current_page, ['setor_sampah', 'tarik_saldo', 'jual_sampah']) ? 'active' : '' ?>">
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
                <li class="<?= in_array($current_page, ['admin', 'tambah_admin', 'ubah_admin']) ? 'active' : '' ?>">
                    <a href="index.php?page=admin">
                        <i class='bx bxs-user-badge'></i>
                        <span class="text">Admin</span>
                    </a>
                </li>

                <li class="<?= in_array($current_page, ['nasabah', 'tambah_nasabah', 'ubah_nasabah', 'detail_nasabah']) ? 'active' : '' ?>">
                    <a href="index.php?page=nasabah">
                        <i class='bx bxs-user-account'></i>
                        <span class="text">Nasabah</span>
                    </a>
                </li>

            <?php endif; ?>

            <?php if (in_array($user_role, ['admin', 'nasabah'])) : ?>
                <li class="<?= in_array($current_page, ['detail_user', 'ubah_password']) ? 'active' : '' ?>">
                    <a href="index.php?page=detail_user">
                        <i class='bx bxs-user-account'></i>
                        <span class="text">Detail User</span>
                    </a>
                </li>

            <?php endif; ?>

        </ul>

        <ul class="side-menu">
            <li>
                <a href="index.php?page=login" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>

        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- KONTEN -->
    <section id="content">
        <?php
        $page = $_GET['page'] ?? 'dashboard';

        $allowed_pages_admin = [
            'dashboard',
            'sampah',
            'setor_sampah',
            'semua_transaksi',
            'rekap_transaksi',
            'admin',
            'nasabah',
            'detail_user',
        ];


        $allowed_pages_nasabah = [
            'dashboard',
            'detail_user',
        ];


        ?>
    </section>
    <!-- KONTEN -->

</body>

</html>