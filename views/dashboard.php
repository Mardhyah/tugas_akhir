<?php
include_once __DIR__ . '/layouts/sidebar.php';
include_once __DIR__ . '/../fungsi.php';
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

// Close the statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="/adminhub/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

    /* Daftar transaksi */
    .transaction-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
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

                <li>
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3>120</h3>
                        <p>Total Nasabah</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-dollar-circle'></i>
                    <span class="text">
                        <h3>Rp 25.000.000</h3>
                        <p>Total Jual ke Pengepul</p>
                    </span>
                </li>
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
                                            echo "<div style='color:red;'>- Rp. " . number_format($row['jumlah_tarik'], 2, ',', '.') . "</div>";
                                            break;

                                        case 'setor_sampah':
                                            echo "<div>Sampah: " . ucfirst($row['jenis_sampah']) . " (" . number_format($row['jumlah_kg'], 2) . " Kg)</div>";
                                            echo "<div style='color:#28a745;'>+ Rp. " . number_format($row['jumlah_rp'], 2, ',', '.') . "</div>";
                                            break;

                                        case 'pindah_saldo':
                                            echo "<div>Jumlah: Rp. " . number_format($row['jumlah'], 2, ',', '.') . "</div>";
                                            echo "<div style='color:#1E90FF;'>Hasil: " . number_format($row['hasil_konversi'], 4, ',', '.') . " " . $row['jenis_konversi'] . "</div>";
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
                    <div class="additional-info">
                        <div class="user-card">
                            <div class="user-card-header">
                                <span class="wifi-icon"><i class="fas fa-wifi"></i></span>
                                <span class="account-number"><?php echo $data['nik']; ?></span>
                            </div>
                            <div class="user-details">
                                <p>Username: <?php echo $data['username']; ?></p>
                            </div>

                            <div class="user-balance">
                                <!-- <div class="balance-card">
                                <span>Tunai</span>
                                <span>Rp <?php echo number_format($data['uang'], 2, ',', '.'); ?></span>
                            </div> -->
                                <div class="balance-card">
                                    <span>Emas</span>
                                    <span><?php echo number_format($data['emas'], 4, ',', '.'); ?> g</span>
                                </div>
                            </div>
                        </div>
                    </div>

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