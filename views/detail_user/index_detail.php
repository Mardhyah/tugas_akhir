<?php
$current_page = $_GET['page'] ?? '';

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../config/koneksi.php';

// Cek apakah user sudah login
function checkSession()
{
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
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
    <style>
        .containeruser {
            max-width: 1100px;
            /* ukuran container lebih besar */
            margin: 40px auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .card-wrapper {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            background-color: #f5f5f5;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Kartu Kiri */
        .left-card {
            flex: 1;
            min-width: 280px;
        }

        .card-title {
            background-color: #25745A;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .user-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .user-info h3 {
            margin: 0;
            font-size: 20px;
        }

        .user-info p {
            margin: 5px 0 0;
            color: #555;
        }

        /* Kartu Kanan */
        .right-card {
            flex: 2;
            min-width: 300px;
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .right-card h3 {
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row .value {
            font-weight: 500;
            color: #333;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .card-wrapper {
                flex-direction: column;
            }
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background-color: #f0ad4e;
            color: white;
        }

        .btn-warning:hover {
            background-color: #ec971f;
        }

        .text-center {
            text-align: center;
        }
    </style>

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
                    <span>Halaman</span>
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