<?php
$current_page = $_GET['page'] ?? '';

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';

checkSession();

$username = $_SESSION['username'];

$fromDate = $_GET['fromDate'] ?? '';
$toDate = $_GET['toDate'] ?? '';
$urutan = $_GET['urutan'] ?? 'terbaru';
$jenisTransaksi = $_GET['jenisTransaksi'] ?? '';

if ($jenisTransaksi == 'setor_sampah') {
    $query = "
        SELECT 
            DATE_FORMAT(t.date, '%Y-%m') AS bulan,
            SUM(ss.jumlah_rp) AS total_setor,
            0 AS total_jual,
            SUM(ss.jumlah_kg) AS total_kg_setor,
            0 AS total_kg_jual
        FROM transaksi t
        INNER JOIN setor_sampah ss ON t.id = ss.id_transaksi
        WHERE t.jenis_transaksi = 'setor_sampah'
    ";
} elseif ($jenisTransaksi == 'jual_sampah') {
    $query = "
        SELECT 
            DATE_FORMAT(t.date, '%Y-%m') AS bulan,
            0 AS total_setor,
            SUM(js.jumlah_rp) AS total_jual,
            0 AS total_kg_setor,
            SUM(js.jumlah_kg) AS total_kg_jual
        FROM transaksi t
        INNER JOIN jual_sampah js ON t.id = js.id_transaksi
        WHERE t.jenis_transaksi = 'jual_sampah'
    ";
} elseif ($jenisTransaksi == 'tarik_saldo') {
    $query = "
        SELECT 
            DATE_FORMAT(t.date, '%Y-%m') AS bulan,
            0 AS total_setor,
            0 AS total_jual,
            0 AS total_kg_setor,
            0 AS total_kg_jual
        FROM transaksi t
        WHERE t.jenis_transaksi = 'tarik_saldo'
    ";
} else {
    // Semua jenis transaksi
    $query = "
        SELECT 
            DATE_FORMAT(t.date, '%Y-%m') AS bulan,
            COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'setor_sampah' THEN ss.jumlah_rp ELSE 0 END), 0) AS total_setor,
            COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'jual_sampah' THEN js.jumlah_rp ELSE 0 END), 0) AS total_jual,
            COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'setor_sampah' THEN ss.jumlah_kg ELSE 0 END), 0) AS total_kg_setor,
            COALESCE(SUM(CASE WHEN t.jenis_transaksi = 'jual_sampah' THEN js.jumlah_kg ELSE 0 END), 0) AS total_kg_jual
        FROM transaksi t
        LEFT JOIN setor_sampah ss ON t.id = ss.id_transaksi
        LEFT JOIN jual_sampah js ON t.id = js.id_transaksi
        WHERE 1 = 1
    ";
}

// Filter tanggal
if (!empty($fromDate)) {
    $query .= " AND t.date >= '$fromDate'";
}
if (!empty($toDate)) {
    $query .= " AND t.date <= '$toDate'";
}

// Tidak perlu filter jenisTransaksi lagi di sini!

// Grouping dan urutan
$query .= " GROUP BY bulan";
$query .= ($urutan == 'terlama') ? " ORDER BY bulan ASC" : " ORDER BY bulan DESC";

$transaksi_result = mysqli_query($koneksi, $query);
if (!$transaksi_result) {
    die('Query failed: ' . mysqli_error($koneksi));
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
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .custom-modal .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        width: 90%;
        max-width: 500px;
        border-radius: 10px;
        position: relative;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 10px;
    }

    .modal-title {
        font-size: 18px;
        font-weight: bold;
    }

    .modal-body {
        padding: 10px 0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-check {
        margin-bottom: 10px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        padding-top: 10px;
    }

    .modal-footer button {
        flex: 1 1 45%;
    }

    .btn-close {
        background-color: #6c757d;
        color: #fff;
        border: none;
        padding: 10px;
        font-size: 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-close:hover {
        background-color: #5a6268;
    }

    .btn-ok {
        background-color: #28886a;
        color: #fff;
        border: none;
        padding: 10px;
        font-size: 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-ok:hover {
        background-color: #17654c;
    }

    /* Responsive tweaks */
    @media (max-width: 480px) {
        .modal-footer {
            flex-direction: column;
        }

        .modal-footer button {
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
            <?php include_once __DIR__ . '/../layouts/breadcrumb.php'; ?>

        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <span>Halaman</span>
                    <h1>Rekap Transaksi</h1>
                </div>
            </div>


            <div id="wrapper">

                <!-- Ini Main-Content -->
                <div class="main--content">
                    <div class="main--content--monitoring">
                        <div class="header--wrapper">

                            <button type="button" class="inputbtn" id="openModalBtn">
                                Filter Transaksi
                            </button>

                        </div>

                        <!-- Filter Modal -->
                        <!-- Modal Manual -->
                        <div id="filterTransaksiModal" class="custom-modal">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Filter Transaksi</h5>
                                    <button type="button" class="close" id="closeModalBtn">&times;</button>
                                </div>
                                <form method="GET" action="">
                                    <input type="hidden" name="page" value="rekap_transaksi">
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="fromDate">From</label>
                                            <input type="date" class="form-control" id="fromDate" name="fromDate" value="<?php echo $fromDate ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="toDate">To</label>
                                            <input type="date" class="form-control" id="toDate" name="toDate" value="<?php echo $toDate ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="urutan">Urutan</label>
                                            <select class="form-control" id="urutan" name="urutan">
                                                <option value="terbaru" <?php if (!empty($urutan) && $urutan == 'terbaru') echo 'selected'; ?>>Terbaru</option>
                                                <option value="terlama" <?php if (!empty($urutan) && $urutan == 'terlama') echo 'selected'; ?>>Terlama</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="jenisTransaksi">Jenis transaksi</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenisTransaksi" id="setorSampah" value="setor_sampah" <?php if (!empty($jenisTransaksi) && $jenisTransaksi == 'setor_sampah') echo 'checked'; ?>>
                                                <label class="form-check-label" for="setorSampah">Setor sampah</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenisTransaksi" id="jualSampah" value="jual_sampah" <?php if (!empty($jenisTransaksi) && $jenisTransaksi == 'jual_sampah') echo 'checked'; ?>>
                                                <tr>
                                                    <th>Frekuensi Menabung</th>
                                                    <td><?= htmlspecialchars($row['frekuensi_menabung']) ?> kali</td>
                                                </tr>
                                                <label class="form-check-label" for="jualSampah">Jual sampah</label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenisTransaksi" id="semuaJenis" value="" <?php if (empty($jenisTransaksi)) echo 'checked'; ?>>
                                                <label class="form-check-label" for="semuaJenis">Semua jenis</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn-close" onclick="closeModal()">Close</button>
                                        <button type="submit" class="btn-ok">OK</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Start of Transaction Table Section -->
                        <div class="tabular--wrapper">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th>Total Setor Sampah (Rp)</th>
                                        <th>Total Jual Sampah (Rp)</th>
                                        <th>Total Kg Setor Sampah</th>
                                        <th>Total Kg Jual Sampah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($transaksi_result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($transaksi_result)): ?>
                                            <tr>
                                                <td><?php echo $row['bulan']; ?></td>
                                                <td><?php echo number_format($row['total_setor'], 2); ?></td>
                                                <td><?php echo number_format($row['total_jual'], 2); ?></td>
                                                <td><?php echo number_format($row['total_kg_setor'], 2); ?></td>
                                                <td><?php echo number_format($row['total_kg_jual'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No transactions found for the selected filters.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- End of Transaction Table Section -->

                    </div>
                </div>
                <!-- Batas Akhir Main-Content -->
            </div>
            <!-- Bootstrap 4 atau 5 CSS di <head> -->




        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
    <script>
        const modal = document.getElementById('filterTransaksiModal');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        openBtn.onclick = () => modal.style.display = 'block';
        closeBtn.onclick = () => modal.style.display = 'none';
        cancelBtn.onclick = () => modal.style.display = 'none';

        // Tutup modal jika klik di luar kotak modal
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>

</body>

</html> <?php
        $current_page = $_GET['page'] ?? '';
        ?>