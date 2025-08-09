<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../config/koneksi.php';
include_once __DIR__ . '/../../fungsi.php';

// Dapatkan ID nasabah dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Query untuk mendapatkan data nasabah berdasarkan ID
$query = "SELECT * FROM user WHERE id = ? AND role = 'nasabah' AND status = 1";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$row) {
    echo "<p>Nasabah tidak ditemukan!</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>BankSampah</title>
</head>
<style>
    .back-btn {
        background-color: #6c757d;
        color: #fff;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .back-btn:hover {
        background-color: #5a6268;
        color: #fff;
    }
</style>

<body>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <?php include_once __DIR__ . '/../layouts/breadcrumb.php'; ?>

        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <span>Halaman</span>
                    <h1>Detail Nabasah</h1>
                </div>
            </div>

            <div id="wrapper">

                <!-- Ini Main-Content -->
                <div class="main--content">

                    <!-- Ini card-container -->
                    <div class="card--container">


                        <!-- Tabel Detail Nasabah -->
                        <table class="table table-bordered mt-3">
                            <tr>
                                <th>ID Nasabah</th>
                                <td><?= htmlspecialchars($row['id']); ?></td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                            </tr>
                            <tr>
                                <th>No Telepon</th>
                                <td><?= htmlspecialchars($row['notelp']); ?></td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td><?= htmlspecialchars($row['nik']); ?></td>
                            </tr>
                            <tr>
                                <th>No Rekening</th>
                                <td><?= htmlspecialchars($row['no_rek']); ?></td>
                            </tr>
                            <tr>
                                <th>NIP</th>
                                <td><?= htmlspecialchars($row['nip']); ?></td>
                            </tr>
                            <tr>
                                <th>Golongan</th>
                                <td><?= htmlspecialchars($row['gol']); ?></td>
                            </tr>
                            <tr>
                                <th>Bidang</th>
                                <td><?= htmlspecialchars($row['bidang']); ?></td>
                            </tr>
                            <tr>
                                <th>Frekuensi Menabung</th>
                                <td><?= htmlspecialchars($row['frekuensi_menabung']) ?> kali</td>
                            </tr>

                            <tr>
                                <th>Terakhir Menabung</th>
                                <td><?= htmlspecialchars($row['terakhir_menabung']); ?></td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td><?= htmlspecialchars($row['alamat']); ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <td><?= htmlspecialchars($row['kelamin']); ?></td>
                            </tr>
                        </table><br>

                        <a href="index.php?page=nasabah" class="back-btn">Kembali</a>
                        <a href="index.php?page=ubah_nasabah&id=<?= htmlspecialchars($row["id"]); ?>" class="inputbtn">Ubah</a>
                    </div>
                </div>
            </div>
            <!-- Batas Akhir card-container -->
            </div>

        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>

</body>

</html>