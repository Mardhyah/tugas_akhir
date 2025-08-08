<?php
$current_page = $_GET['page'] ?? '';

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/..//../config/koneksi.php';

// Ambil data nasabah yang belum diverifikasi
$query = mysqli_query($koneksi, "SELECT * FROM user WHERE role = 'nasabah' AND is_verified = 0 AND verify_status = 'verified' AND status = 1");

// Cek jika ada parameter aksi dan id di URL
if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id = intval($_GET['id']);
    $aksi = $_GET['aksi'];

    if ($aksi === 'acc') {
        $query = "UPDATE user SET is_verified = 1 WHERE id = $id";
    } elseif ($aksi === 'tolak') {
        $query = "DELETE FROM user WHERE id = $id";
    } else {
        echo "<script>alert('Aksi tidak valid.'); window.location='index.php?page=notifikasi_nasabah';</script>";
        exit;
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Aksi berhasil.'); window.location='index.php?page=notifikasi_nasabah';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan.'); window.location='index.php?page=notifikasi_nasabah';</script>";
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Nasabah</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<style>
    .btn-acc {
        background-color: green;
        color: white;
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 4px;
    }

    .btn-tolak {
        background-color: red;
        color: white;
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 4px;
    }

    table {
        border-collapse: collapse;
    }
</style>

<body>
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div id="wrapper">
                <div class="head-title">
                    <div class="left">
                        <span>Halaman</span>
                        <h1>Notifikasi Nasabah</h1>
                    </div>
                </div>

                <div class="main--content">
                    <div class="card--container">
                        <div class="card">
                            <h3>Daftar Nasabah Belum Diverifikasi</h3>
                            <div class="table-container">
                                <?php if (mysqli_num_rows($query) > 0): ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>NIK</th>
                                                <th>Alamat</th>
                                                <th>Tanggal Lahir</th>
                                                <th>No Telepon</th>
                                                <th>Bidang</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            while ($row = mysqli_fetch_assoc($query)) : ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                                    <td><?= htmlspecialchars($row['nik']); ?></td>
                                                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                                                    <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                                    <td><?= htmlspecialchars($row['notelp']); ?></td>
                                                    <td><?= htmlspecialchars($row['bidang']); ?></td>
                                                    <td>
                                                        <a href="index.php?page=notifikasi_nasabah&id=<?= $row['id']; ?>&aksi=acc" class="btn-acc">Verifikasi</a>
                                                        <a href="index.php?page=notifikasi_nasabah&id=<?= $row['id']; ?>&aksi=tolak" class="btn-tolak">Tolak</a>

                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p style="margin-top: 20px;">Tidak ada notifikasi nasabah baru.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

        </main>
    </section>


    <script src="script.js"></script>
</body>

</html>