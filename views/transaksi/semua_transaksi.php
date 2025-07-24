<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';
require_once __DIR__ . '/..//../crypto/core/crypto_helper.php';

// Mendapatkan username dari session
$username = $_SESSION['username'];

// Ambil parameter pencarian jika ada
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Ambil halaman dan limit untuk pagination
$halaman = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$halaman = max(1, $halaman); // minimal halaman 1

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = max(1, $limit); // minimal 1

$offset = ($halaman - 1) * $limit;

$query = "
SELECT 
    t.id AS id, 
    u.username AS username,
    ts.jenis_saldo,
    ps.jenis_konversi,
    GROUP_CONCAT(DISTINCT 
        CASE 
            WHEN ts.id_transaksi IS NOT NULL THEN 
                CASE 
                    WHEN ts.jenis_saldo = 'tarik_emas' THEN 'Tarik Saldo (Emas)'
                    WHEN ts.jenis_saldo = 'tarik_uang' THEN 'Tarik Saldo (Uang)'
                END
            WHEN ps.id_transaksi IS NOT NULL THEN 
                CASE 
                    WHEN ps.jenis_konversi = 'konversi_emas' THEN 'Pindah Saldo (Emas)'
                    WHEN ps.jenis_konversi = 'konversi_uang' THEN 'Pindah Saldo (Uang)'
                END
            WHEN ss.id_transaksi IS NOT NULL THEN 'Setor Sampah'
            WHEN js.id_transaksi IS NOT NULL THEN 'Jual Sampah'
        END 
    SEPARATOR ', ') AS jenis_transaksi,
    IFNULL(
        CASE
            WHEN COUNT(ss.id_transaksi) > 0 THEN CONCAT(COUNT(ss.id_transaksi), ' item')
            WHEN COUNT(js.id_transaksi) > 0 THEN CONCAT(COUNT(js.id_transaksi), ' item')
            WHEN ts.id_transaksi IS NOT NULL THEN ts.jumlah_tarik
            WHEN ps.id_transaksi IS NOT NULL THEN ps.jumlah
            ELSE '0'
        END, '0'
    ) AS jumlah,
    t.date AS date, 
    t.time AS time,
    GROUP_CONCAT(DISTINCT CONCAT(s.jenis, ' : ', ss.jumlah_kg, ' KG') SEPARATOR '<br>') AS detail_sampah
FROM 
    transaksi t
LEFT JOIN tarik_saldo ts ON t.id = ts.id_transaksi
LEFT JOIN pindah_saldo ps ON t.id = ps.id_transaksi
LEFT JOIN setor_sampah ss ON t.id = ss.id_transaksi
LEFT JOIN jual_sampah js ON t.id = js.id_transaksi
LEFT JOIN sampah s ON (ss.id_sampah = s.id OR js.id_sampah = s.id)
LEFT JOIN user u ON t.id_user = u.id
WHERE 
    t.id LIKE '%$search%' OR 
    u.username LIKE '%$search%' OR 
    ts.jenis_saldo LIKE '%$search%' OR 
    ps.jenis_konversi LIKE '%$search%' OR 
    (ss.id_transaksi IS NOT NULL AND 'Setor Sampah' LIKE '%$search%') OR 
    (js.id_transaksi IS NOT NULL AND 'Jual Sampah' LIKE '%$search%') OR 
    (ts.id_transaksi IS NOT NULL AND (
        ('Tarik Saldo (Emas)' LIKE '%$search%' AND ts.jenis_saldo = 'tarik_emas') OR
        ('Tarik Saldo (Uang)' LIKE '%$search%' AND ts.jenis_saldo = 'tarik_uang')
    )) OR
    (ps.id_transaksi IS NOT NULL AND (
        ('Pindah Saldo (Emas)' LIKE '%$search%' AND ps.jenis_konversi = 'konversi_emas') OR
        ('Pindah Saldo (Uang)' LIKE '%$search%' AND ps.jenis_konversi = 'konversi_uang')
    ))
GROUP BY 
    t.id, t.date, t.time, u.username, ts.jenis_saldo, ps.jenis_konversi
ORDER BY 
    t.date DESC, t.time DESC
LIMIT $limit OFFSET $offset
";


// Eksekusi query
$transaksi_result = query($query);
// Hitung total data untuk pagination
$total_query = "SELECT COUNT(*) AS total FROM transaksi";
$total_result = query($total_query);
$total_rows = $total_result[0]['total'];
$total_pages = ceil($total_rows / $limit);

function isEncryptedFormat($text)
{
    // cek apakah string base64 dan minimal panjang tertentu (misal 24)
    if (!is_string($text)) return false;
    if (strlen($text) < 24) return false;
    return base64_decode($text, true) !== false;
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
    <title>AdminHub</title>
</head>

<style>
    .form-group {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 500;
        margin-bottom: 8px;
        color: #333;
    }

    .form-group input[type="text"],
    .form-group input[type="search"],
    .form-group input[type="number"],
    .form-group select {
        padding: 10px 14px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    }




    /* Overlay modal */
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
    }

    /* Modal box */
    .custom-modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 30px;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        position: relative;
        animation: slideDown 0.3s ease;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Animasi muncul dari atas */
    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Tombol tutup (x) */
    .custom-close {
        position: absolute;
        right: 20px;
        top: 18px;
        font-size: 26px;
        color: #999;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .custom-close:hover {
        color: #333;
    }

    /* Konten dalam modal */
    .custom-modal-content h3 {
        margin-top: 0;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .custom-modal-content p {
        margin: 10px 0;
        color: #444;
    }

    #modal-close-btn {
        padding: 8px 20px;
        background: #0e5d2fff;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        float: right;
        transition: 0.3s;
        margin-top: -20px;
        /* naikkan tombol */
        margin-bottom: 0;
        /* pastikan tidak terlalu ke bawah */
    }


    #modal-close-btn:hover {
        background-color: #178344ff;
    }

    .pagination-wrapper {
        margin-top: 20px;
        text-align: center;
    }

    .pagination {
        display: inline-flex;
        list-style-type: none;
        padding: 0;
        margin: 0;
        gap: 6px;
    }

    .pagination li {
        display: inline;
    }

    .pagination li a {
        padding: 6px 12px;
        background-color: #f2f2f2;
        border: 1px solid #ccc;
        text-decoration: none;
        color: #333;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .pagination li a:hover {
        background-color: #ddd;
    }

    .pagination li.active a {
        font-weight: bold;
        background-color: #333;
        color: #fff;
        border-color: #333;
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
                    <h1>Semua Transaksi</h1>
                </div>
            </div>


            <div id="wrapper">

                <!-- Ini Main-Content -->
                <div class="main--content">
                    <div class="main--content--monitoring">
                        <div class="header--wrapper">

                        </div>

                        <!-- Start of Transaction Table Section -->
                        <div class="tabular--wrapper">
                            <!-- Form Pencarian -->
                            <form method="GET" action="">
                                <div class="form-group">
                                    <label for="search">Cari Transaksi:</label>
                                    <input type="text" name="search" id="search" class="form-control"
                                        placeholder="Masukkan ID Transaksi, Username, atau Jenis Transaksi"
                                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="limit">Tampilkan:</label>
                                    <select name="limit" id="limit" class="form-control">
                                        <option value="5" <?php if ($limit == 5) echo 'selected'; ?>>5</option>
                                        <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                                        <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                                        <option value="40" <?php if ($limit == 40) echo 'selected'; ?>>40</option>
                                    </select>
                                </div>

                                <!-- Hidden fields -->
                                <input type="hidden" name="page" value="semua_transaksi">
                                <input type="hidden" name="hal" value="1">

                                <button type="submit" class="inputbtn">Cari</button>
                            </form>



                            <!-- End of Form Pencarian -->

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID Transaksi</th>
                                        <th>Username</th>
                                        <th>Jenis Transaksi</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($transaksi_result) > 0): ?>
                                        <?php foreach ($transaksi_result as $row): ?>
                                            <?php
                                            $jumlah = $row['jumlah'];
                                            $jumlah_display = $jumlah;
                                            $jenisTransaksi = strtolower($row['jenis_transaksi']);
                                            $jenisSaldo = isset($row['jenis_saldo']) ? strtolower($row['jenis_saldo']) : '';
                                            $idTransaksi = $row['id_transaksi'] ?? $row['id'];

                                            if (strpos($jenisTransaksi, 'setor') !== false && strpos($jenisTransaksi, 'sampah') !== false) {
                                                $result_rp = query("SELECT jumlah_rp FROM setor_sampah WHERE id_transaksi = '$idTransaksi'");

                                                $total_rp_decrypted = 0;
                                                $total_item = count($result_rp);

                                                foreach ($result_rp as $item) {
                                                    $encrypted_rp = $item['jumlah_rp'];
                                                    try {
                                                        $decrypted_rp = decryptWithAES($encrypted_rp);
                                                        if (is_numeric($decrypted_rp)) {
                                                            $total_rp_decrypted += (float)$decrypted_rp;
                                                        }
                                                    } catch (Exception $e) {
                                                        // gagal dekripsi, bisa skip atau log error
                                                    }
                                                }

                                                $jumlah_display = $total_item . ' item (Rp ' . number_format($total_rp_decrypted, 2, ',', '.') . ')';
                                            } elseif (isEncryptedFormat($jumlah)) {
                                                try {
                                                    $decrypted = decryptWithAES($jumlah);

                                                    if ($jenisSaldo === 'emas' || $jenisSaldo === 'tarik_emas' || strpos($jenisSaldo, 'emas') !== false) {
                                                        $jumlah_display = number_format((float)$decrypted, 2, ',', '.') . ' gram';
                                                    } elseif (is_numeric($decrypted)) {
                                                        $jumlah_display = "Rp. " . number_format((float)$decrypted, 2, ',', '.');
                                                    } else {
                                                        $jumlah_display = '❌ Gagal dekripsi';
                                                    }
                                                } catch (Exception $e) {
                                                    $jumlah_display = '❌ Gagal dekripsi';
                                                }
                                            }

                                            ?>

                                            <tr>
                                                <td><?= $row['id']; ?></td>
                                                <td><?= $row['username']; ?></td>
                                                <td><?= $row['jenis_transaksi']; ?></td>
                                                <td><?= $jumlah_display; ?></td>
                                                <td><?= $row['date']; ?></td>
                                                <td><?= $row['time']; ?></td>
                                                <td>
                                                    <button type="button" class="inputbtn6 detail-button"
                                                        data-id="<?= $row['id']; ?>"
                                                        data-username="<?= $row['username']; ?>"
                                                        data-jenis="<?= $row['jenis_transaksi']; ?>"
                                                        data-jumlah="<?= $jumlah_display; ?>"
                                                        data-date="<?= $row['date']; ?>"
                                                        data-detail-sampah="<?= $row['detail_sampah']; ?>">
                                                        Detail
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7">Tidak ada transaksi ditemukan.</td>
                                        </tr>
                                    <?php endif; ?>


                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="pagination-wrapper">
                                <ul class="pagination">
                                    <?php
                                    $max_links = 15;
                                    $start = max(1, $halaman - floor($max_links / 2));
                                    $end = $start + $max_links - 1;
                                    if ($end > $total_pages) {
                                        $end = $total_pages;
                                        $start = max(1, $end - $max_links + 1);
                                    }
                                    ?>

                                    <!-- Tombol Previous -->
                                    <?php if ($halaman > 1): ?>
                                        <li>
                                            <a href="?page=semua_transaksi&hal=<?= $halaman - 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Tombol Angka -->
                                    <?php for ($i = $start; $i <= $end; $i++): ?>
                                        <li class="<?= ($halaman == $i) ? 'active' : '' ?>">
                                            <a href="?page=semua_transaksi&hal=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Tombol Next -->
                                    <?php if ($halaman < $total_pages): ?>
                                        <li>
                                            <a href="?page=semua_transaksi&hal=<?= $halaman + 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>






                        </div>
                        <!-- End of Transaction Table Section -->
                    </div>
                </div>
                <!-- Batas Akhir Main-Content -->
            </div>


            <!-- Custom Modal -->
            <div id="detailModal" class="custom-modal">
                <div class="custom-modal-content">
                    <span class="custom-close">&times;</span>
                    <h3>Detail Transaksi</h3>
                    <p><strong>ID Transaksi:</strong> <span id="modal-id"></span></p>
                    <p><strong>Username:</strong> <span id="modal-username"></span></p>
                    <p><strong>Jenis Transaksi:</strong> <span id="modal-jenis"></span></p>
                    <p><strong>Jumlah:</strong> <span id="modal-jumlah"></span></p>
                    <p><strong>Detail Sampah:</strong><br><span id="modal-detail-sampah"></span></p>
                    <p><strong>Tanggal:</strong> <span id="modal-date"></span></p>

                    <button id="modal-close-btn">Tutup</button>
                </div>
            </div>


            <!-- End of Detail Modal -->

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const modal = document.getElementById('detailModal');
                    const closeX = document.querySelector('.custom-close');
                    const closeBtn = document.getElementById('modal-close-btn');

                    // Tutup modal
                    closeX.onclick = () => modal.style.display = 'none';
                    closeBtn.onclick = () => modal.style.display = 'none';
                    window.onclick = (e) => {
                        if (e.target == modal) modal.style.display = 'none';
                    };

                    // Buka modal saat tombol diklik
                    document.querySelectorAll('.detail-button').forEach(btn => {
                        btn.addEventListener('click', function() {
                            document.getElementById('modal-id').textContent = this.dataset.id;
                            document.getElementById('modal-username').textContent = this.dataset.username;
                            document.getElementById('modal-jenis').textContent = this.dataset.jenis;
                            document.getElementById('modal-jumlah').textContent = this.dataset.jumlah;
                            document.getElementById('modal-date').textContent = this.dataset.date;
                            document.getElementById('modal-detail-sampah').innerHTML = this.dataset.detailSampah;

                            modal.style.display = 'block';
                        });
                    });
                });
            </script>


            <script>
                $(document).on('click', '.detail-button', function() {
                    var id = $(this).data('id');
                    var username = $(this).data('username');
                    var jenis = $(this).data('jenis');
                    var jumlah = $(this).data('jumlah');
                    var date = $(this).data('date');
                    var detailSampah = $(this).data('detail-sampah');

                    $('#modal-id').text(id);
                    $('#modal-username').text(username);
                    $('#modal-jenis').text(jenis);
                    $('#modal-jumlah').text(jumlah);
                    $('#modal-date').text(date);
                    $('#modal-detail-sampah').html(detailSampah);

                    $('#detailModal').modal('show');
                });
            </script>

            <script>
                // Event listener untuk mengirim form secara otomatis ketika dropdown limit berubah
                document.getElementById('limit').addEventListener('change', function() {
                    this.form.submit();
                });
            </script>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->




    <script src="script.js"></script>

</body>

</html>