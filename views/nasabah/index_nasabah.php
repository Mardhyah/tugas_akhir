<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
// Pastikan koneksi sudah tersedia
require_once 'config/koneksi.php';
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';

// Ambil parameter limit, p (pagination), dan pencarian nama
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$p = max(1, $p);
$offset = ($p - 1) * $limit;
$search_nama = isset($_GET['search_nama']) ? trim($_GET['search_nama']) : '';

$search_nama = isset($_GET['search_nama']) ? trim($_GET['search_nama']) : '';

// ========= HANDLE RECOVER & DELETE ACTIONS =========

// Hapus (soft delete) nasabah
if (isset($_GET['delete_id']) || (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']))) {
    $delete_id = isset($_GET['delete_id']) ? (int)$_GET['delete_id'] : (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "UPDATE user SET status = 0 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    $_SESSION['message'] = "Data berhasil dihapus!";
    echo "<script>window.location.href='index.php?page=nasabah';</script>";
    exit();
}


// Kembalikan satu nasabah
if (isset($_GET['recover_id'])) {
    $recover_id = (int)$_GET['recover_id'];
    $stmt = mysqli_prepare($koneksi, "UPDATE user SET status = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $recover_id);
    mysqli_stmt_execute($stmt);
    $_SESSION['message'] = "Data berhasil dikembalikan!";
    echo "<script>window.location.href='index.php?page=nasabah';</script>";
    exit();
}

// Kembalikan semua nasabah
if (isset($_GET['restore_all'])) {
    mysqli_query($koneksi, "UPDATE user SET status = 1 WHERE role = 'nasabah' AND status = 0");
    $_SESSION['message'] = "Semua data berhasil dikembalikan!";
    echo "<script>window.location.href='index.php?page=nasabah';</script>";
    exit();
}

// ========= HITUNG JUMLAH TOTAL DATA =========
$total_query = "SELECT COUNT(*) AS total 
                FROM user 
                WHERE role = 'nasabah' AND status = 1 AND is_verified = 1";

if (!empty($search_nama)) {
    $search_escaped = mysqli_real_escape_string($koneksi, $search_nama);
    $total_query .= " AND nama LIKE '%$search_escaped%'";
}
$total_result = mysqli_query($koneksi, $total_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);

// ========= PAGINATION =========
$pagination_limit = 10;
$start_page = max(1, $p - floor($pagination_limit / 2));
$end_page = min($total_pages, $start_page + $pagination_limit - 1);
if ($end_page - $start_page < $pagination_limit - 1) {
    $start_page = max(1, $end_page - $pagination_limit + 1);
}

// ========= AMBIL DATA NASABAH AKTIF =========
$query = "SELECT id, username, nama, email, notelp, nik, no_rek, is_verified 
          FROM user 
          WHERE role = 'nasabah' AND status = 1 AND is_verified = 1";

if (!empty($search_nama)) {
    $query .= " AND nama LIKE '%$search_nama%'";
}
$query .= " ORDER BY LENGTH(id), CAST(id AS UNSIGNED) LIMIT $limit OFFSET $offset";
$nasabah_result = mysqli_query($koneksi, $query);

// ========= AMBIL DATA NASABAH YANG TERHAPUS =========
$deleted_users_query = "SELECT id, username, nama, email, notelp, nik, no_rek FROM user WHERE role = 'nasabah' AND status = 0";
$deleted_users = mysqli_query($koneksi, $deleted_users_query);
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
    input[name="search_nama"] {
        width: 600px;
        /* Panjang input */
        padding: 10px 14px;
        font-size: 16px;
        border: 2px solid #ccc;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.3s, box-shadow 0.3s;
        background-color: #fff;
    }

    input[name="search_nama"]:focus {
        border-color: #007bff;
        box-shadow: 0 0 6px rgba(0, 123, 255, 0.25);
    }

    .form-group {
        margin-bottom:
            20px;
        display:
            flex;
        flex-direction:
            column;
    }

    .form-group label {
        font-weight:
            500;
        margin-bottom:
            8px;
        color:
            #333;
    }

    .form-group input[type="text"],
    .form-group input[type="search"],
    .form-group input[type="number"],
    .form-group select {
        padding:
            10px 14px;
        border:
            1px solid #ccc;
        border-radius:
            8px;
        font-size:
            14px;
        font-family:
            'Poppins',
            sans-serif;
        transition:
            border-color 0.2s,
            box-shadow 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color:
            #007bff;
        outline:
            none;
        box-shadow:
            0 0 0 3px rgba(0,
                123,
                255,
                0.15);
    }

    .pagination-wrapper {
        margin-top:
            20px;
        text-align:
            center;
    }

    .pagination {
        display:
            inline-flex;
        list-style-type:
            none;
        padding:
            0;
        margin:
            0;
        gap:
            6px;
    }

    .pagination li {
        display:
            inline;
    }

    .pagination li a {
        padding:
            6px 12px;
        background-color:
            #f2f2f2;
        border:
            1px solid #ccc;
        text-decoration:
            none;
        color:
            #333;
        border-radius:
            4px;
        transition:
            background-color 0.3s;
    }

    .pagination li a:hover {
        background-color:
            #ddd;
    }

    .pagination li.active a {
        font-weight:
            bold;
        background-color:
            #333;
        color:
            #fff;
        border-color:
            #333;
    }

    /*
===
Button
Biru
(Tambahan)
===
*/
    .inputbtn8 {
        padding:
            5px 10px;
        border-radius:
            5px;
        color:
            white;
        font-size:
            13px;
        text-decoration:
            none;
        background-color:
            #007bff;
        /*
Biru
*/
    }

    .inputbtn8:hover {
        background-color:
            #0056b3;
    }

    /*
===
Responsive
untuk
.inputbtn8
===
*/
    @media (max-width: 768px) {
        .inputbtn8 {
            font-size:
                12px;
            padding:
                6px 12px;
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


            <div id="wrapper">
                <div class="head-title">
                    <div class="left">
                        <span>Halaman</span>
                        <h1>Nasabah</h1>
                    </div>
                </div>
                <!-- Ini Main-Content -->
                <div class="main--content">

                    <!-- Ini card-container -->
                    <div class="card--container">

                        <!-- Tabel Nasabah Aktif -->
                        <div class="tabular--wrapper">
                            <div class="search--wrapper">
                                <form method="GET" action="">
                                    <input type="hidden" name="page" value="nasabah">

                                    <input type="text" name="search_nama" placeholder="Cari nama nasabah..."
                                        value="<?= htmlspecialchars($search_nama) ?>"
                                        title="Cari nama nasabah">

                                    <button type="submit" class="inputbtn">Cari</button>

                                    <div class="form-group">
                                        <label for="limit">Tampilkan:</label>
                                        <select name="limit" id="limit" class="form-control" onchange="this.form.submit()">
                                            <option value="5" <?php if ($limit == 5) echo 'selected'; ?>>5</option>
                                            <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
                                            <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
                                            <option value="40" <?php if ($limit == 40) echo 'selected'; ?>>40</option>
                                            <option value="0" <?php if ($limit == 0) echo 'selected'; ?>>Semua</option>
                                        </select>
                                    </div>
                                </form>

                            </div>

                            <div class="row align-items-start">
                                <div class="user--info">
                                    <h3 class="main--title">Data Nasabah</h3>
                                    <a href="index.php?page=tambah_nasabah"><button type="button" class="inputbtn">Tambah</button></a>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-info">
                                    <?= htmlspecialchars($_SESSION['message']); ?>
                                </div>
                                <?php unset($_SESSION['message']); ?>
                            <?php endif; ?>

                            <div class="table-container">
                                <?php if (empty($nasabah_result)): ?>
                                    <div class="alert alert-warning">
                                        <strong>Nasabah tidak ditemukan!</strong> Silakan coba NIK yang lain.
                                    </div>
                                <?php else: ?>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Username</th>
                                                <th>Nama</th>
                                                <th>Email</th>
                                                <th>No Telp</th>
                                                <th>NIK</th>
                                                <th>No Rek</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($nasabah_result as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row["id"]); ?></td>
                                                    <td><?= htmlspecialchars($row["username"]); ?></td>
                                                    <td><?= htmlspecialchars($row["nama"]); ?></td>
                                                    <td><?= htmlspecialchars($row["email"]); ?></td>
                                                    <td><?= htmlspecialchars($row["notelp"]); ?></td>
                                                    <td><?= htmlspecialchars($row["nik"]); ?></td>
                                                    <td><?= htmlspecialchars($row["no_rek"]); ?></td>
                                                    <td>
                                                        <a href="index.php?page=detail_nasabah&id=<?= htmlspecialchars($row["id"]); ?>" class="inputbtn6">Detail</a>
                                                        <a href="index.php?page=ubah_nasabah&id=<?= htmlspecialchars($row["id"]); ?>" class="inputbtn8">Ubah</a>
                                                        <a href="index.php?page=nasabah&action=delete&id=<?= $row['id']; ?>" class="inputbtn7"
                                                            onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                                    </td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <div class="pagination-wrapper">
                                        <ul class="pagination">
                                            <?php if ($p > 1): ?>
                                                <li class="page-item">
                                                    <a href="index.php?page=nasabah&p=<?= $p - 1; ?>&limit=<?= $limit; ?>&search_nama=<?= urlencode($search_nama); ?>" class="page-link">Previous</a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <li class="page-item <?= $p == $i ? 'active' : '' ?>">
                                                    <a href="index.php?page=nasabah&p=<?= $i; ?>&limit=<?= $limit; ?>&search_nama=<?= urlencode($search_nama); ?>" class="page-link"><?= $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($p < $total_pages): ?>
                                                <li class="page-item">
                                                    <a href="index.php?page=nasabah&p=<?= $p + 1; ?>&limit=<?= $limit; ?>&search_nama=<?= urlencode($search_nama); ?>" class="page-link">Next</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>

                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tombol Tampilkan -->
                        <button id="showDeletedUsersBtn" class="inputbtn">
                            Tampilkan Data Nasabah Terhapus
                        </button>

                        <!-- Wrapper Tabel (awalnya disembunyikan) -->
                        <div id="deletedUsersTable" style="display: none; margin-top: 20px;">
                            <?php if (mysqli_num_rows($deleted_users) === 0): ?>
                                <div class="alert alert-warning">
                                    <strong>Tidak ada nasabah yang dihapus!</strong>
                                </div>
                            <?php else: ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>No Telp</th>
                                            <th>NIK</th>
                                            <th>No Rek</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($deleted_users as $row): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row["id"]); ?></td>
                                                <td><?= htmlspecialchars($row["username"]); ?></td>
                                                <td><?= htmlspecialchars($row["nama"]); ?></td>
                                                <td><?= htmlspecialchars($row["email"]); ?></td>
                                                <td><?= htmlspecialchars($row["notelp"]); ?></td>
                                                <td><?= htmlspecialchars($row["nik"]); ?></td>
                                                <td><?= htmlspecialchars($row["no_rek"]); ?></td>
                                                <td>
                                                    <a href="index.php?page=nasabah&recover_id=<?= $row['id'] ?>"
                                                        class="btn btn-success"
                                                        onclick="return confirm('Yakin ingin mengembalikan data ini?')">
                                                        Kembalikan
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>


        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->
    <script>
        document.getElementById('showDeletedUsersBtn').addEventListener('click', function(e) {
            e.preventDefault(); // mencegah reload jika button di dalam <form>
            const tableDiv = document.getElementById('deletedUsersTable');
            tableDiv.style.display = tableDiv.style.display === 'none' ? 'block' : 'none';
            this.textContent = tableDiv.style.display === 'block' ?
                'Sembunyikan Data Nasabah Terhapus' :
                'Tampilkan Data Nasabah Terhapus';
        });
    </script>


    <script src="script.js"></script>
</body>

</html>