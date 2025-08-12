<?php
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/..//../fungsi.php';
require_once __DIR__ . '/..//../crypto/core/crypto_helper.php';
// Cek apakah pengguna sudah login
checkSession();

// Mendapatkan username dari session
$username = $_SESSION['username'];

// Ambil data pengguna dari database
$data = getUserData($koneksi, $username);
$id_user = $data['id']; // Mendapatkan id_user dari data user yang sedang login

// Query untuk mengambil data transaksi, detail transaksi, dan informasi pengguna untuk user yang sedang login
$sqlTransaksi = "
SELECT t.id AS id_transaksi, t.jenis_transaksi, t.date, t.time, 
       ts.jenis_saldo, ts.jumlah_tarik, 
       ss.id_sampah, ss.jumlah_kg, ss.jumlah_rp, s.jenis AS jenis_sampah, 
       js.id_sampah AS id_jual_sampah, js.jumlah_kg AS jumlah_jual_kg, js.jumlah_rp AS total_penjualan, s.jenis AS jenis_jual_sampah,
       ps.jumlah, ps.hasil_konversi, ps.jenis_konversi, 
       u.id AS id_user, u.username
FROM transaksi t
LEFT JOIN tarik_saldo ts ON t.id = ts.id_transaksi
LEFT JOIN setor_sampah ss ON t.id = ss.id_transaksi
LEFT JOIN jual_sampah js ON t.id = js.id_transaksi
LEFT JOIN pindah_saldo ps ON t.id = ps.id_transaksi 
LEFT JOIN user u ON t.id_user = u.id
LEFT JOIN sampah s ON ss.id_sampah = s.id OR js.id_sampah = s.id
WHERE u.id = ?
ORDER BY t.date DESC, t.time DESC
";


$stmtTransaksi = $koneksi->prepare($sqlTransaksi);
$stmtTransaksi->bind_param("i", $id_user);
$stmtTransaksi->execute();
$resultTransaksi = $stmtTransaksi->get_result();

// Cek jika query berhasil
if ($resultTransaksi === false) {
    echo "Error: " . $koneksi->error;
    exit;
}


// Query untuk ambil terakhir menabung dan frekuensi menabung
$sqlMenabung = "
    SELECT 
        MAX(t.date) AS terakhir_menabung,
        COUNT(DISTINCT CONCAT(t.date, ' ', DATE_FORMAT(t.time, '%H:%i'))) AS frekuensi_menabung
    FROM 
        transaksi t
    JOIN 
        setor_sampah ss ON t.id = ss.id_transaksi
    WHERE 
        t.id_user = ?
";


$stmtMenabung = $koneksi->prepare($sqlMenabung);
$stmtMenabung->bind_param("i", $id_user);
$stmtMenabung->execute();
$resultMenabung = $stmtMenabung->get_result();

if ($resultMenabung && $rowMenabung = $resultMenabung->fetch_assoc()) {
    $data['terakhir_menabung'] = $rowMenabung['terakhir_menabung'];
    $data['frekuensi_menabung'] = $rowMenabung['frekuensi_menabung'];
} else {
    $data['terakhir_menabung'] = null;
    $data['frekuensi_menabung'] = 0;
}


$uang = $data['uang'] ?? 0;
$emas = $data['emas'] ?? 0;

// Ambil harga jual emas terkini (dalam Rp/gram)
$current_gold_price_sell = getCurrentGoldPricesell();

// Hitung saldo emas dalam bentuk rupiah
$gold_equivalent = $emas * $current_gold_price_sell;


// Query untuk mengambil data kategori dan sampah
$sql = "SELECT ks.name AS kategori, s.jenis, s.harga 
        FROM sampah s 
        JOIN kategori_sampah ks ON s.id_kategori = ks.id";
$result = $koneksi->query($sql);

if ($result === false) {
    echo "Error: " . $koneksi->error;
    exit;
}

// Buat query grafik berdasarkan role pengguna
if ($data['role'] == 'admin') {
    // Jika admin, tampilkan grafik jual sampah
    $sqlChart = "
    SELECT 
        DATE_FORMAT(t.date, '%Y-%m') AS month,
        SUM(js.jumlah_kg) AS total_kg,
        SUM(js.jumlah_rp) AS total_rp
    FROM 
        transaksi t
    JOIN 
        jual_sampah js ON t.id = js.id_transaksi
    WHERE 
        t.jenis_transaksi = 'jual_sampah'
    GROUP BY 
        month
    ORDER BY 
        month ASC";
} else {
    // Jika nasabah, tampilkan grafik setor sampah
    $sqlChart = "
    SELECT 
        DATE_FORMAT(t.date, '%Y-%m') AS month,
        SUM(ss.jumlah_kg) AS total_kg,
        SUM(ss.jumlah_rp) AS total_rp
    FROM 
        transaksi t
    JOIN 
        setor_sampah ss ON t.id = ss.id_transaksi
    WHERE 
        t.jenis_transaksi = 'setor_sampah' AND t.id_user = ?
    GROUP BY 
        month
    ORDER BY 
        month ASC";
}

$stmt = $koneksi->prepare($sqlChart);
if ($data['role'] != 'admin') {
    $stmt->bind_param("i", $id_user); // Bind jika nasabah
}
$stmt->execute();
$resultChart = $stmt->get_result();

// Initialize arrays to hold the data
$months = [];
$totalKg = [];
$totalRp = [];

if ($resultChart->num_rows > 0) {
    while ($row = $resultChart->fetch_assoc()) {
        $months[] = $row['month'];
        $totalKg[] = $row['total_kg'];
        $totalRp[] = $row['total_rp'];
    }
}


// Total nasabah aktif dan sudah diverifikasi
$queryNasabah = mysqli_query($koneksi, "SELECT COUNT(*) AS total_nasabah FROM user WHERE role = 'nasabah' AND status = 1 AND is_verified = 1");
$dataNasabah = mysqli_fetch_assoc($queryNasabah);
$totalNasabah = $dataNasabah['total_nasabah'];

// Total nasabah terverifikasi
$queryVerified = mysqli_query($koneksi, "SELECT COUNT(*) AS total_verified FROM user WHERE role = 'nasabah' AND status = 1 AND is_verified = 1");
$dataVerified = mysqli_fetch_assoc($queryVerified);
$totalVerified = $dataVerified['total_verified'];

// Total nasabah belum diverifikasi
$queryPending = mysqli_query($koneksi, "SELECT COUNT(*) AS total_pending FROM user WHERE role = 'nasabah' AND status = 1 AND is_verified = 0");
$dataPending = mysqli_fetch_assoc($queryPending);
$totalPending = $dataPending['total_pending'];


// Total Jual ke Pengepul
$queryJual = mysqli_query($koneksi, "SELECT SUM(jumlah_rp) AS total_jual FROM jual_sampah");
$dataJual = mysqli_fetch_assoc($queryJual);
$totalJual = $dataJual['total_jual'] ?? 0;


$notifQuery = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah 
    FROM user 
    WHERE role = 'nasabah' 
        AND is_verified = 0 
        AND verify_status = 'verified' 
        AND status = 1
");
$notif = mysqli_fetch_assoc($notifQuery);

// Close the statement
$stmt->close();


function isEncryptedFormat(string $text): bool
{
    // Cek apakah base64 valid dan memiliki struktur ciphertext AES (biasanya cukup panjang)
    return preg_match('/^[A-Za-z0-9+\/=]+$/', $text) && strlen($text) > 64;
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <title>BankSampah</title>
</head>



<body>


    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav class="navbar">
            <i class='bx bx-menu'></i>


        </nav>

        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <span>Halaman</span>
                    <h1>Dasboard</h1>
                </div>
            </div>

            <ul class="box-info">
                <?php if ($_SESSION['role'] === 'admin') : ?>
                    <li>
                        <i class='bx bxs-group'></i>
                        <span class="text">
                            <h3><?php echo $totalNasabah; ?></h3>
                            <p>Total Nasabah</p>
                        </span>
                    </li>

                    <li style="position: relative;">
                        <i class='bx bxs-bell-ring'></i>
                        <span class="notif-badge"><?= $notif['jumlah']; ?></span>
                        <span class="text">
                            <h3><?= $notif['jumlah']; ?></h3>
                            <a href="index.php?page=notifikasi_nasabah" class="verif-button">Yang Harus Diverifikasi</a>
                        </span>
                    </li>





                <?php endif; ?>




                <?php if ($_SESSION['role'] === 'nasabah') : ?>
                    <div class="nasabah-info-wide-card">
                        <div class="user-card-wide">
                            <!-- Bagian Informasi Akun -->
                            <div class="user-info-section">
                                <div class="user-nik">
                                    <i class="fas fa-id-card"></i>
                                    <span><strong>NIK:</strong> <?php echo $data['nik']; ?></span>
                                </div>
                                <div class="user-name">
                                    <i class="fas fa-user"></i>
                                    <span><strong>Username:</strong> <?php echo $data['username']; ?></span>
                                </div>
                                <div class="user-last-deposit">
                                    <i class="fas fa-clock"></i>
                                    <span>
                                        <strong>Terakhir Menabung:</strong>

                                        <?php
                                        if (!empty($data['terakhir_menabung']) && $data['terakhir_menabung'] !== '0000-00-00 00:00:00') {
                                            echo date('d M Y', strtotime($data['terakhir_menabung']));
                                        } else {
                                            echo 'Belum Pernah';
                                        }
                                        ?>
                                    </span>
                                </div>

                                <div class="user-freq-deposit">
                                    <i class="fas fa-chart-line"></i>
                                    <span>
                                        <strong>Frekuensi Menabung:</strong>
                                        <?php echo isset($data['frekuensi_menabung']) ? (int)$data['frekuensi_menabung'] . ' kali' : '0 kali'; ?>
                                    </span>
                                </div>

                            </div>

                            <!-- Bagian Saldo -->
                            <div class="balance-section">
                                <!-- Saldo Tunai -->
                                <div class="balance-box tunai">
                                    <span class="label">Saldo Tunai Koversi Emas</span>
                                    <span class="value">
                                        <?php echo number_format(round($gold_equivalent, 2), 2, ',', '.'); ?>
                                    </span>
                                    <br>
                                    <small style="font-size: 14px; color: #666;">

                                    </small>
                                </div>

                                <!-- Saldo Emas -->
                                <div class="balance-box emas">
                                    <span class="label">Saldo Emas</span>
                                    <span class="value">
                                        <?php echo number_format((float)$emas, 4, ',', '.'); ?> g
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>






            </ul>




            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <div class="grafik-penyetoran">
                            <?php if ($data['role'] == 'nasabah'): ?>
                                <h3>Grafi Setor Sampah Bulanan</h3><br><br>
                            <?php else: ?>
                                <h3>Grafik Jual Sampah Bulanan</h3><br><br>
                            <?php endif; ?>
                            <canvas id="setorSampahChart"></canvas>
                        </div>
                    </div>


                </div>
                <div class="todo">
                    <div class="head">
                        <h3>Riwayat Transaksi</h3>

                    </div>
                    <div class="history">
                        <div class="transaction-list">
                            <?php
                            if ($resultTransaksi->num_rows > 0) {

                                // 1️⃣ Kelompokkan data berdasarkan id_transaksi
                                $transaksiData = [];
                                while ($row = $resultTransaksi->fetch_assoc()) {
                                    $transaksiData[$row['id_transaksi']][] = $row;
                                }

                                // 2️⃣ Loop per transaksi
                                foreach ($transaksiData as $idTransaksi => $items) {
                                    // Ambil data header (pakai item pertama)
                                    $firstRow = $items[0];
                                    echo "<div class='transaction-item'>";
                                    echo "<div class='transaction-header'>";
                                    echo "<strong>" . ucfirst(str_replace('_', ' ', $firstRow['jenis_transaksi'])) . "</strong>";
                                    echo "<span class='transaction-date'>" . date('d M Y', strtotime($firstRow['date'])) .
                                        " | " . date('H:i:s', strtotime($firstRow['time'])) . "</span>";
                                    echo "</div>";

                                    echo "<div class='transaction-body'>";

                                    // 3️⃣ Loop setiap item di transaksi ini
                                    foreach ($items as $row) {
                                        switch ($row['jenis_transaksi']) {
                                            case 'tarik_saldo':
                                                $jumlahTarik = $row['jumlah_tarik'];
                                                $jumlahDisplay = $jumlahTarik;
                                                $jenisSaldoLower = strtolower($row['jenis_saldo']);

                                                if (isEncryptedFormat($jumlahTarik)) {
                                                    try {
                                                        $decrypted = decryptWithAES($jumlahTarik);
                                                        if (strpos($jenisSaldoLower, 'emas') !== false) {
                                                            // Format emas → max 2 desimal saja (0.01 gram)
                                                            $jumlahDisplay = number_format((float)$decrypted, 2, ',', '.') . ' Gram';
                                                        } else {
                                                            $jumlahDisplay = "Rp. " . number_format((float)$decrypted, 2, ',', '.');
                                                        }
                                                    } catch (Exception $e) {
                                                        $jumlahDisplay = '❌ Gagal dekripsi';
                                                    }
                                                } else {
                                                    if (strpos($jenisSaldoLower, 'emas') !== false) {
                                                        $jumlahDisplay = number_format((float)$jumlahTarik, 2, ',', '.') . ' Gram';
                                                    } else {
                                                        $jumlahDisplay = "Rp. " . number_format((float)$jumlahTarik, 2, ',', '.');
                                                    }
                                                }
                                                echo "<div style='color:red;'>- $jumlahDisplay</div>";
                                                break;

                                            case 'setor_sampah':
                                                echo "<div>Sampah: " . ucfirst($row['jenis_sampah']) .
                                                    " (" . number_format((float)$row['jumlah_kg'], 2) . " Kg)</div>";

                                                $jumlahRp = $row['jumlah_rp'];
                                                $jumlahDisplay = $jumlahRp;

                                                if (isEncryptedFormat($jumlahRp)) {
                                                    try {
                                                        $decrypted = decryptWithAES($jumlahRp);
                                                        $jumlahDisplay = "Rp. " . number_format((float)$decrypted, 2, ',', '.');
                                                    } catch (Exception $e) {
                                                        $jumlahDisplay = '❌ Gagal dekripsi';
                                                    }
                                                } else {
                                                    $jumlahDisplay = "Rp. " . number_format((float)$jumlahRp, 2, ',', '.');
                                                }

                                                echo "<div style='color:#28a745;'>+ $jumlahDisplay</div>";
                                                break;

                                            case 'jual_sampah':
                                                echo "<div>Jenis Sampah: " . ucfirst($row['jenis_jual_sampah']) .
                                                    " (" . number_format($row['jumlah_jual_kg'], 2) . " Kg)</div>";
                                                echo "<div>Total Penjualan: Rp. " . number_format($row['total_penjualan'], 2, ',', '.') . "</div>";
                                                break;
                                        }
                                    }

                                    echo "</div></div>";
                                }
                            } else {
                                echo "<p>Tidak ada transaksi ditemukan.</p>";
                            }
                            ?>
                        </div>

                    </div>
                </div>
                <?php if ($data['role'] == 'nasabah') : ?>
                    <!-- Hanya ditampilkan jika pengguna adalah nasabah -->


                    <div class="table-container">
                        <h3>Jenis-jenis Sampah</h3>
                        <p>*harga dapat berubah sewaktu-waktu</p>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Kategori</th>
                                    <th>Jenis</th>
                                    <th>Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    $no = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . $row['kategori'] . "</td>";
                                        echo "<td>" . $row['jenis'] . "</td>";
                                        echo "<td>Rp. " . number_format($row['harga'], 0, ',', '.') . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>Tidak ada data</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            </div>
            <script>
                // Data untuk grafik setor/jual sampah
                const ctx = document.getElementById('setorSampahChart').getContext('2d');
                const chartData = {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                            label: 'Jumlah KG',
                            data: <?php echo json_encode($totalKg); ?>,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Jumlah RP',
                            data: <?php echo json_encode($totalRp); ?>,
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            borderColor: 'rgba(153, 102, 255, 1)',
                            borderWidth: 1
                        }
                    ]
                };

                const chartOptions = {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                };

                const setorSampahChart = new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: chartOptions
                });
            </script>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
</body>

</html>