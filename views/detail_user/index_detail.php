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
    $query = "SELECT u.id, u.username, u.nama, u.nik, u.email, u.notelp, u.alamat, u.created_at AS tanggal_bergabung, u.role, 
                     d.uang, d.emas 
              FROM user u
              LEFT JOIN dompet d ON u.id = d.id_user
              WHERE u.username = ?";

    $stmt = $koneksi->prepare($query);
    if (!$stmt) {
        die("Prepare statement failed: " . $koneksi->error);
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
    <link rel="stylesheet" href="/bank_sampah/assets/css/style.css">
    <style>
        .containeruser {
            max-width: 1200px;
            margin: 50px auto 20px;
            /* ← tambahkan margin-top 50px */
            padding: 20px;
        }

        .card-wrapper {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            flex-wrap: nowrap;
        }

        .left-card {
            flex: 1;
            max-width: 40%;
        }

        .right-card {
            flex: 1.5;
            max-width: 60%;
        }

        .card-title {
            background-color: #25745A;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 70px;
            height: 70px;
        }

        .user-info h3 {
            margin: 0;
        }

        .user-info p {
            margin: 4px 0 0;
            font-size: 14px;
            color: #444;
        }

        .right-card h3 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .value {
            font-weight: bold;
        }

        /* 🌐 RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .card-wrapper {
                flex-direction: column;
                gap: 20px;
            }

            .left-card,
            .right-card {
                max-width: 100%;
            }

            .user-card {
                flex-direction: column;
                text-align: center;
            }

            .user-info h3,
            .user-info p {
                text-align: center;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .info-row .value {
                font-weight: normal;
            }
        }
    </style>
</head>

<body>
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>

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
                <div class="card--container">
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
                                </div>
                            </div>

                            <!-- Kartu Kanan -->
                            <div class="right-card card">
                                <h3>Informasi User</h3>
                                <div class="info-row">
                                    <span>Nama</span><span class="value"><?= htmlspecialchars($data['nama']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span>NIK</span><span class="value"><?= htmlspecialchars($data['nik']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span>Telepon</span><span class="value"><?= htmlspecialchars($data['notelp']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span>Alamat</span><span class="value"><?= htmlspecialchars($data['alamat']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
        </main>
    </section>

    <script src="script.js"></script>
</body>

</html>