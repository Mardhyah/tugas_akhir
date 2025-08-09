<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';

// //edit sampah
// function updatedatasampah($data)
// {
//     global $conn;
//     $id = htmlspecialchars($data["id"]);
//     $id_kategori = htmlspecialchars($data["id_kategori"]);
//     $jenis = htmlspecialchars($data["jenis"]);
//     $harga = htmlspecialchars($data["harga"]);
//     $harga_pusat = htmlspecialchars($data["harga_pusat"]);
//     $jumlah = htmlspecialchars($data["jumlah"]);

//     $query = "UPDATE sampah SET id_kategori='$id_kategori',jenis='$jenis',harga='$harga',harga_pusat='$harga_pusat',jumlah='$jumlah' WHERE id='$id'";
//     mysqli_query($conn, $query);
//     return mysqli_affected_rows($conn);
// }

// Ambil ID dari URL
$id = $_GET['id'];

// Query data sampah berdasarkan ID
$sampah = query("SELECT * FROM sampah WHERE id='$id'")[0];

if (isset($_POST["submit"])) {
    // Cek apakah data berhasil diubah
    if (updatedatasampah($_POST) > 0) {
        echo "
        <script>  
            alert('Data Berhasil Diubah');
            document.location.href = 'index.php?page=sampah';
        </script>
    ";
    } else {
        echo "
        <script>  
            alert('Data Gagal Diubah');
            document.location.href = 'index.php?page=sampah';
        </script>
    ";
    }
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
    .head-title .left span {
        font-size: 0.85rem;
        /* sedikit lebih kecil */
        color: #555;
        text-transform: uppercase;
        letter-spacing: 1.1px;
        display: block;
    }

    .head-title .left h1 {
        font-size: 1.6rem;
        /* diperkecil */
        margin-top: 3px;
        /* dikurangi */
        font-weight: 700;
    }

    .card--container {
        margin-top: 20px;
        /* dikurangi */
        padding: 20px;
        /* dikurangi agar form lebih compact */
        background-color: #fefefe;
        border-radius: 12px;
        /* sedikit diperkecil */
        box-shadow: 0 0 6px rgb(0 0 0 / 0.05);
        /* bayangan juga dikurangi */
    }

    .card--container h3.main--title {
        font-weight: 700;
        margin-bottom: 18px;
        /* dikurangi */
        color: #2c3e50;
        border-bottom: 2px solid #4e73df;
        padding-bottom: 6px;
        /* dikurangi */
    }

    .container label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        /* dikurangi */
        color: #2c3e50;
    }

    .container input[type="text"],
    .container input[type="number"],
    .container input[readonly] {
        width: 100%;
        padding: 9px 12px;
        /* padding dikurangi */
        margin-bottom: 14px;
        /* jarak antar input dikurangi */
        border: 1.5px solid #ddd;
        border-radius: 6px;
        /* dikurangi */
        font-size: 0.95rem;
        /* font sedikit lebih kecil */
        transition: border-color 0.3s ease;
    }

    .container input[type="text"]:focus,
    .container input[type="number"]:focus {
        border-color: #4e73df;
        outline: none;
        box-shadow: 0 0 4px #4e73dfaa;
    }

    .container input[readonly] {
        background-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }

    /* Styling untuk input group persen keuntungan */
    .input-group {
        display: flex;
        align-items: center;
        margin-bottom: 14px;
        /* dikurangi */
    }

    .input-group input[type="number"] {
        flex: 1;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right: none;
        padding: 9px 12px;
        font-size: 0.95rem;
    }

    .input-group-append {
        background-color: #f0f0f0;
        padding: 9px 12px;
        /* dikurangi */
        border: 1.5px solid #ddd;
        border-left: none;
        border-top-right-radius: 6px;
        border-bottom-right-radius: 6px;
        font-weight: 700;
        color: #555;
        user-select: none;
    }

    .form-text.text-muted {
        font-size: 0.8rem;
        /* sedikit lebih kecil */
        color: #6c757d;
        margin-top: -12px;
        /* dikurangi */
        margin-bottom: 12px;
        /* dikurangi */
    }

    /* Responsive */
    @media (max-width: 600px) {
        #content {
            margin: 15px;
            padding: 12px;
        }

        .card--container {
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
            <?php include_once __DIR__ . '/../layouts/breadcrumb.php'; ?>

        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <span>Halaman</span>
                    <h1>Ubah Sampah</h1>
                </div>
            </div>




            <div id="wrapper">


                <!-- Ini Main-Content -->
                <div class="main--content">


                    <!-- Ini card-container -->
                    <div class="card--container">
                        <h3 class="main--title">Isi Form Berikut</h3>
                        <form method="POST" action="">
                            <div class="container">

                                <input type="hidden" name="id" value="<?= $sampah["id"] ?>">

                                <input type="hidden" name="id_kategori" value="<?= $sampah["id_kategori"] ?>">


                                <label for="jenis">Jenis</label>
                                <input type="text" placeholder="Masukkan Jenis Sampah" name="jenis"
                                    value="<?= $sampah["jenis"] ?>" required>

                                <label for="harga_pengepul">Harga Pengepul</label>
                                <input type="text" id="harga_pengepul" placeholder="Masukkan Harga Pengepul" name="harga_pusat"
                                    value="<?= $sampah["harga_pusat"] ?>" required>
                                <small id="persentaseHelp" class="form-text text-muted">Masukkan persentase keuntungan dari harga pengepul.</small>

                                <label for="keuntungan_percent">Persentase Keuntungan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="keuntungan_percent"
                                        placeholder="Masukkan Persentase Keuntungan" name="keuntungan_percent" required
                                        aria-describedby="persentaseHelp">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <label for="keuntungan">Keuntungan (Hasil)</label>
                                <input type="text" id="keuntungan" placeholder="Keuntungan" readonly>

                                <label for="harga_nasabah">Harga Nasabah (Hasil)</label>
                                <input type="text" id="harga_nasabah" placeholder="Harga Nasabah" name="harga"
                                    value="<?= $sampah["harga"] ?>" readonly>
                                <!-- 
                                <label for="jumlah">Jumlah</label>
                                <input type="text" placeholder="Masukkan Jumlah" name="jumlah" value="<?= $sampah["jumlah"] ?>"
                                    required> -->

                                <button type="submit" name="submit" class="inputbtn">Update</button>
                            </div>
                        </form>
                    </div>

                </div>
                <!-- Batas Akhir card-container -->
            </div>
            <!-- Bootstrap JS dan dependencies -->

            <script>
                // Menghitung harga nasabah dan keuntungan dari harga pengepul dan persentase keuntungan
                document.getElementById('keuntungan_percent').addEventListener('input', function() {
                    var hargaPengepul = parseFloat(document.getElementById('harga_pengepul').value) || 0;
                    var persenKeuntungan = parseFloat(this.value) || 0;

                    // Hitung keuntungan: harga_pengepul * persenKeuntungan / 100
                    var keuntungan = hargaPengepul * persenKeuntungan / 100;
                    document.getElementById('keuntungan').value = keuntungan.toFixed(2);

                    // Hitung harga nasabah: harga_pengepul + keuntungan
                    var hargaNasabah = hargaPengepul - keuntungan;
                    document.getElementById('harga_nasabah').value = hargaNasabah.toFixed(2);
                });

                document.getElementById('harga_pengepul').addEventListener('input', function() {
                    var persenKeuntungan = parseFloat(document.getElementById('keuntungan_percent').value) || 0;
                    var hargaPengepul = parseFloat(this.value) || 0;

                    // Hitung keuntungan: harga_pengepul * persenKeuntungan / 100
                    var keuntungan = hargaPengepul * persenKeuntungan / 100;
                    document.getElementById('keuntungan').value = keuntungan.toFixed(2);

                    // Hitung harga nasabah setiap kali harga pengepul diubah
                    var hargaNasabah = hargaPengepul + keuntungan;
                    document.getElementById('harga_nasabah').value = hargaNasabah.toFixed(2);
                });
            </script>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>

</body>

</html>