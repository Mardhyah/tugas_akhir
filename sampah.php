<?php
include 'header.php';
include 'fungsi.php';

// Check if 'id' is set in the URL and call the delete function
if (isset($_GET["id"])) {
    $id = $_GET["id"];

    if (hapusSampah($id) > 0) {
        echo "
            <script>
                alert('Data Berhasil Dihapus');
                document.location.href='sampah.php';
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Data Gagal Dihapus');
                document.location.href='sampah.php';
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
    <link rel="stylesheet" href="style.css">

    <title>AdminHub</title>
</head>

<body>


    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="switch-mode" hidden>
            <label for="switch-mode" class="switch-mode"></label>
            <a href="#" class="notification">
                <i class='bx bxs-bell'></i>
                <span class="num">8</span>
            </a>
            <a href="#" class="profile">
                <img src="img/people.png">
            </a>
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
                                <a href="tambah_sampah.php"><button type="button" name="button"
                                        class="inputbtn .border-right">Tambah</button></a>
                                <a href="manage_kategori.php"><button type="button" name="button"
                                        class="inputbtn .border-right">Manage Kategori</button></a>
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
                                                        <a href="edit_sampah.php?id=<?= $row["id"]; ?>"
                                                            class="inputbtn6">Ubah</a>
                                                    </button>
                                                </li>
                                                <li class="liaksi">
                                                    <button type="submit" name="submit">
                                                        <a href="sampah.php?id=<?= $row["id"]; ?>"
                                                            class="inputbtn7">Hapus</a>
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

ini sampah.php