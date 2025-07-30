<?php
$current_page = $_GET['page'] ?? '';
?>
<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';


// Check if 'id' is set in the URL and call the delete function
if (isset($_GET["id"])) {
    $id = $_GET["id"];

    if (hapusSampah($id) > 0) {
        echo "
            <script>
                alert('Data Berhasil Dihapus');
                document.location.href = 'index.php?page=sampah';
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Data Gagal Dihapus');
                document.location.href = 'index.php?page=sampah';
            </script>
        ";
    }
}

// Call the new function to retrieve sampah data
$query_all = getSampahData();
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
                    <h1>Sampah</h1>
                </div>
            </div>



            <div class="main--content">
                <div class="main--content--monitoring">


                    <!-- Ini Tabel -->
                    <div class="tabular--wrapper">
                        <div class="row align-items-start">
                            <div class="user--info">
                                <h3 class="main--title">Data Sampah</h3>
                                <a href="index.php?page=tambah_sampah">
                                    <button type="button" name="button" class="inputbtn border-right">Tambah</button>
                                </a>

                                <a href="index.php?page=manage_kategori">
                                    <button type="button" name="button" class="inputbtn border-right">Manage Kategori</button>
                                </a>

                            </div>
                        </div>
                        <?php
                        if (isset($_SESSION['message'])) {
                            echo "<h4>" . $_SESSION['message'] . "</h4>";
                            unset($_SESSION['message']);
                        }
                        ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kategori</th>
                                        <th>Jenis</th>
                                        <th>Harga Nasabah</th>
                                        <th>Harga Pengepul</th>
                                        <th>Jumlah (KG)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($query_all as $row): ?>
                                        <tr>
                                            <td><?= $row["id"]; ?></td>
                                            <td><?= $row["kategori_name"]; ?></td>
                                            <td><?= $row["jenis"]; ?></td>
                                            <td>Rp. <?= number_format($row["harga"], 0, ',', '.'); ?></td>
                                            <td>Rp. <?= number_format($row["harga_pusat"], 0, ',', '.'); ?></td>
                                            <td><?= $row["jumlah"]; ?> KG</td>
                                            <td>
                                                <li class="liaksi">
                                                    <button type="submit" name="submit">
                                                        <a href="index.php?page=ubah_sampah&id=<?= $row['id']; ?>"
                                                            class="inputbtn6">Ubah</a>
                                                    </button>
                                                </li>
                                                <li class="liaksi">
                                                    <button type="submit" name="submit">
                                                        <a href="index.php?page=sampah&delete_id=<?= htmlspecialchars($row["id"]); ?>"
                                                            class="inputbtn7"
                                                            onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                                                    </button>
                                                </li>
                                            </td>
                                        </tr>
                                        <?php $i++; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <!-- Batas Akhir Tabel -->
                </div>
            </div>



        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
</body>

</html>