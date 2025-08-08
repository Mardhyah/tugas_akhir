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

<style>
    * {
        box-sizing: border-box;
    }

    body,
    html {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        height: 100%;
        overflow-y: auto;
        background-color: #f2f2f2;
    }

    /* CONTAINER UTAMA */
    .container {
        display: flex;
        min-height: 100vh;
        width: 100%;
    }

    /* FORM SECTION */
    .signin-section {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 40px;
        position: relative;
        background-color: #ffffff;
    }

    .signin-section h2 {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 20px;
        text-align: center;
        width: 100%;
    }

    /* FORM WRAPPER */
    .form-container {
        width: 100%;
        max-width: 600px;
        overflow-y: auto;
        max-height: calc(100vh - 140px);
        padding-right: 10px;
        padding: 20px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    /* FORM */
    form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .form-field {
        display: flex;
        flex-direction: column;
    }

    label {
        margin-bottom: 6px;
        font-weight: 500;
    }

    input,
    select,
    textarea {
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        background-color: #f9f9f9;
    }

    textarea {
        resize: vertical;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }

    .inputbtn {
        padding: 12px 24px;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-cancel {
        background-color: #ccc;
        color: black;
    }

    .btn-cancel:hover {
        background-color: #aaa;
    }

    .inputbtn[type="submit"],
    .inputbtn:not(.btn-cancel) {
        background-color: #25745A;
        color: white;
    }

    .inputbtn[type="submit"]:hover,
    .inputbtn:not(.btn-cancel):hover {
        background-color: #1e5e49;
    }

    /* INFO SECTION */
    .info-section {
        flex: 1;
        background: #25745A;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 60px 40px;
        text-align: center;
    }

    .info-section h2 {
        font-size: 36px;
        margin-bottom: 20px;
    }

    .info-section p {
        max-width: 500px;
        font-size: 20px;
        line-height: 1.6;
    }

    /* Responsif Mobile */
    /* Responsif Mobile dan Tablet */
    @media (max-width: 900px) {

        html,
        body {
            height: auto;
            overflow-y: auto;
        }

        .container {
            flex-direction: column;
            height: auto;
            overflow-y: auto;
        }

        .signin-section,
        .info-section {
            width: 100%;
            min-height: auto;
            padding: 30px 20px;
            align-items: center;
            justify-content: center;
        }

        .info-section {
            order: -1;
            /* Pindahkan info-section ke atas di mobile */
            padding: 40px 20px;
        }

        .info-section h2 {
            font-size: 28px;
        }

        .info-section p {
            font-size: 16px;
        }

        .signin-section h2 {
            font-size: 28px;
        }

        .form-container {
            max-height: none;
            overflow-y: visible;
            padding-right: 0;
        }

        .inputbtn,
        .btn-signup {
            width: 100%;
            text-align: center;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }


    .btn-signup {
        margin-top: 40px;
        padding: 14px 28px;
        background-color: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        border-radius: 35px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-signup:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }
</style>