<?php
include_once __DIR__ . '/layouts/sidebar.php';
include_once __DIR__ . '/../fungsi.php';
require_once __DIR__ . '/../crypto/core/crypto_helper.php';
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
ORDER BY t.time DESC";


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
        COUNT(*) AS frekuensi_menabung
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


// Total Nasabah
$queryNasabah = mysqli_query($koneksi, "SELECT COUNT(*) AS total_nasabah FROM user WHERE role = 'nasabah'");
$dataNasabah = mysqli_fetch_assoc($queryNasabah);
$totalNasabah = $dataNasabah['total_nasabah'];

// Total Jual ke Pengepul
$queryJual = mysqli_query($koneksi, "SELECT SUM(jumlah_rp) AS total_jual FROM jual_sampah");
$dataJual = mysqli_fetch_assoc($queryJual);
$totalJual = $dataJual['total_jual'] ?? 0;


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


    <title>AdminHub</title>
</head>
<style>
    #grafik-penyetoran {
        width: 90%;
        max-width: 700px;
        /* Lebar maksimum */
        margin: 0 auto;
    }

    #setorSampahChart {
        width: 100% !important;
        height: 300px !important;
        /* Tinggi chart diperbesar */
    }

    /* Container untuk keseluruhan riwayat */
    .history {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 12px;
        max-width: 100%;
        margin-top: 20px;
    }

    /* Daftar transaksi - scrollable */
    .transaction-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        max-height: 400px;
        /* <== scroll aktif jika tinggi lebih dari ini */
        overflow-y: auto;
        padding-right: 10px;
        border: 1px solid #dee2e6;
        border-radius: 10px;
    }

    /* Setiap item transaksi */
    .transaction-item {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .transaction-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Header item transaksi */
    .transaction-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: 'Poppins', sans-serif;
        margin-bottom: 10px;
    }

    .transaction-header strong {
        font-size: 16px;
        color: #333;
    }

    .transaction-date {
        font-size: 14px;
        color: #777;
        font-weight: 500;
    }

    /* Body transaksi */
    .transaction-body {
        font-size: 15px;
        color: #555;
        line-height: 1.6;
        font-family: 'Lato', sans-serif;
    }

    .transaction-body div {
        margin-bottom: 5px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .transaction-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .transaction-body {
            font-size: 14px;
        }

        .transaction-item {
            padding: 15px;
        }
    }


    .nasabah-info-wide-card {
        width: 100%;
        margin-top: 30px;
    }

    .user-card-wide {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        background-color: #fff;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        padding: 24px 32px;
        gap: 20px;
        box-sizing: border-box;
    }

    .user-info-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 10px;
    }

    .user-info-section div {
        font-size: 16px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-info-box {
        background-color: #ffffff;
        border-left: 5px solid #4e73df;
        padding: 14px 18px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-info-box i {
        font-size: 18px;
        color: #4e73df;
    }

    .user-info-box span {
        font-size: 15px;
        color: #333;
    }


    .balance-section {
        flex: 1;
        display: flex;
        justify-content: space-around;
        align-items: center;
        gap: 20px;
    }

    .balance-box {
        flex: 1;
        min-width: 180px;
        text-align: center;
        padding: 20px;
        border-radius: 12px;
        box-shadow: inset 0 0 0 1px #ddd;
    }

    .balance-box.tunai {
        background-color: #e3f2fd;
    }

    .balance-box.emas {
        background-color: #fff3e0;
    }

    .balance-box .label {
        display: block;
        font-size: 15px;
        color: #555;
        margin-bottom: 5px;
    }

    .balance-box .value {
        font-size: 22px;
        font-weight: bold;
    }

    .balance-box.tunai .value {
        color: #1e88e5;
    }

    .balance-box.emas .value {
        color: #fb8c00;
    }

    /* Responsive */
    @media screen and (max-width: 768px) {
        .user-card-wide {
            flex-direction: column;
            align-items: stretch;
        }

        .balance-section {
            flex-direction: column;
        }

        .balance-box {
            width: 100%;
        }
    }
</style>




<body>


    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
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
                    <li>
                        <i class='bx bxs-dollar-circle'></i>
                        <span class="text">
                            <h3>Rp <?php echo number_format($totalJual, 2, ',', '.'); ?></h3>
                            <p>Total Jual ke Pengepul</p>
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
                                <h3>Grafi Setor Sampah Bulanan</h3>
                            <?php else: ?>
                                <h3>Grafik Jual Sampah Bulanan</h3>
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
                                while ($row = $resultTransaksi->fetch_assoc()) {
                                    echo "<div class='transaction-item'>";
                                    echo "<div class='transaction-header'>";
                                    echo "<strong>" . ucfirst(str_replace('_', ' ', $row['jenis_transaksi'])) . "</strong>";
                                    echo "<span class='transaction-date'>" . date('d M Y', strtotime($row['date'])) . " | " . date('H:i:s', strtotime($row['time'])) . "</span>";
                                    echo "</div>";

                                    echo "<div class='transaction-body'>";
                                    switch ($row['jenis_transaksi']) {
                                        case 'tarik_saldo':
                                            echo "<div>Jenis Saldo: " . ucfirst($row['jenis_saldo']) . "</div>";

                                            $jumlahTarik = $row['jumlah_tarik'];
                                            $jumlahDisplay = $jumlahTarik;

                                            if (isEncryptedFormat($jumlahTarik)) {
                                                try {
                                                    $decrypted = decryptWithAES($jumlahTarik);

                                                    if (stripos($decrypted, 'Gram') !== false) {
                                                        $jumlahDisplay = $decrypted;
                                                    } else {
                                                        $jumlahDisplay = "Rp. " . number_format((float)$decrypted, 2, ',', '.');
                                                    }
                                                } catch (Exception $e) {
                                                    $jumlahDisplay = '❌ Gagal dekripsi';
                                                }
                                            } else {
                                                $jumlahDisplay = "Rp. " . number_format((float)$jumlahTarik, 2, ',', '.');
                                            }

                                            echo "<div style='color:red;'>- $jumlahDisplay</div>";
                                            break;

                                        case 'setor_sampah':
                                            echo "<div>Sampah: " . ucfirst($row['jenis_sampah']) . " (" . number_format((float)$row['jumlah_kg'], 2) . " Kg)</div>";

                                            $jumlahRp = $row['jumlah_rp'];
                                            $jumlahDisplay = $jumlahRp;

                                            if (isEncryptedFormat($jumlahRp)) {
                                                try {
                                                    $decrypted = decryptWithAES($jumlahRp);

                                                    if (stripos($decrypted, 'Gram') !== false) {
                                                        $jumlahDisplay = $decrypted;
                                                    } else {
                                                        $jumlahDisplay = "Rp. " . number_format((float)$decrypted, 2, ',', '.');
                                                    }
                                                } catch (Exception $e) {
                                                    $jumlahDisplay = '❌ Gagal dekripsi';
                                                }
                                            } else {
                                                $jumlahDisplay = "Rp. " . number_format((float)$jumlahRp, 2, ',', '.');
                                            }

                                            echo "<div style='color:#28a745;'>+ $jumlahDisplay</div>";
                                            break;



                                        case 'jual_sampah':
                                            echo "<div>Jenis Sampah: " . ucfirst($row['jenis_jual_sampah']) . " (" . number_format($row['jumlah_jual_kg'], 2) . " Kg)</div>";
                                            echo "<div>Total Penjualan: Rp. " . number_format($row['total_penjualan'], 2, ',', '.') . "</div>";
                                            break;
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