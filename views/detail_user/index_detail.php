<?php
$current_page = $_GET['page'] ?? '';

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../config/koneksi.php';

// Cek apakah user sudah login
function checkSession()
{
    if (!isset($_SESSION['username'])) {
        header('Location: index.php?page=login');
        exit();
    }
}
checkSession();

// Ambil data user dari DB
function getUserData($koneksi, $username)
{
    $query = "SELECT u.id, u.username, u.nama, u.nik, u.nip, u.no_rek, u.gol, u.bidang,
                     u.tgl_lahir, u.kelamin, u.email, u.notelp, u.alamat,
                     u.created_at AS tanggal_bergabung, u.role, 
                     d.uang, d.emas 
              FROM user u
              LEFT JOIN dompet d ON u.id = d.id_user
              WHERE u.username = ?
              LIMIT 1";

    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        die("Prepare statement failed: " . $koneksi->error . "\nQuery: " . $query);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}


// Ambil data berdasarkan session
$username = $_SESSION['username'];
$data = getUserData($koneksi, $username);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../assets/css/style.css">


</head>

<body>
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <?php include_once __DIR__ . '/../layouts/breadcrumb.php'; ?>

        </nav>

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <span>Halamanm</span>
                    <h1>Detail User</h1>
                </div>
            </div>
            <div class="main--content">
                <div class="header--wrapper">
                </div>

                <!-- Ini card-container -->
                <!-- <div class="card--container"> -->
                <div class="containeruser">
                    <div class="card-wrapper">
                        <!-- Kartu Kiri -->
                        <div class="left-card">
                            <div class="card-title" id="inputRole">
                                <?= htmlspecialchars($data['role']) ?>
                            </div>
                            <div class="card user-card">
                                <img src="https://img.icons8.com/ios-filled/100/000000/user-male-circle.png" alt="avatar" class="avatar" />
                                <div class="user-info">
                                    <h3><?= htmlspecialchars($data['username']) ?></h3>
                                    <p><?= htmlspecialchars($data['email']) ?></p>
                                </div>
                            </div><br>

                            <!-- Tombol Ubah Password -->
                            <?php if ($data['role'] === 'nasabah') : ?>
                                <!-- Tombol Ubah Password -->
                                <div class="text-center mt-3">
                                    <a href="index.php?page=ubah_password&id=<?= $data['id'] ?>" class="btn btn-warning">Ubah Password</a>
                                </div>
                            <?php endif; ?>

                        </div>


                        <!-- Kartu Kanan -->
                        <div class="right-card card">
                            <h3>Informasi User</h3>

                            <div class="info-row">
                                <span>Nama</span><span class="value"><?= htmlspecialchars($data['nama'] ?? '-') ?></span>
                            </div>
                            <div class="info-row">
                                <span>Email</span><span class="value"><?= htmlspecialchars($data['email'] ?? '-') ?></span>
                            </div>
                            <div class="info-row">
                                <span>Username</span><span class="value"><?= htmlspecialchars($data['username'] ?? '-') ?></span>
                            </div>
                            <div class="info-row">
                                <span>Nomor Telepon</span><span class="value"><?= htmlspecialchars($data['notelp'] ?? '-') ?></span>
                            </div>
                            <div class="info-row">
                                <span>Alamat</span><span class="value"><?= htmlspecialchars($data['alamat'] ?? '-') ?></span>
                            </div>

                            <?php if ($_SESSION['role'] !== 'admin') : ?>
                                <div class="info-row">
                                    <span>No. Rekening</span><span class="value"><?= htmlspecialchars($data['no_rek'] ?? '-') ?></span>
                                </div>
                                <div class="info-row">
                                    <span>NIK</span><span class="value"><?= htmlspecialchars($data['nik'] ?? '-') ?></span>
                                </div>
                                <div class="info-row">
                                    <span>Golongan</span><span class="value"><?= htmlspecialchars($data['gol'] ?? '-') ?></span>
                                </div>
                                <div class="info-row">
                                    <span>NIP</span>
                                    <span class="value">
                                        <?= (empty($data['nip']) || $data['nip'] === '0') ? '-' : htmlspecialchars($data['nip']) ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span>Bidang</span><span class="value"><?= htmlspecialchars($data['bidang'] ?? '-') ?></span>
                                </div>
                                <div class="info-row">
                                    <span>Tanggal Lahir</span>
                                    <span class="value">
                                        <?= !empty($data['tgl_lahir']) ? date('d M Y', strtotime($data['tgl_lahir'])) : '-' ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span>Jenis Kelamin</span><span class="value"><?= htmlspecialchars($data['kelamin'] ?? '-') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
        </main>
    </section>

    <script src="script.js"></script>
</body>

</html>