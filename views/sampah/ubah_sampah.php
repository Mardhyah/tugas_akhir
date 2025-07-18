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
    <link rel="stylesheet" href="/bank_sampah/assets/css/style.css">
    <title>AdminHub</title>
</head>

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

                                <!-- <label for="id_kategori">ID Kategori</label><br>
                                <input type="text" placeholder="Masukkan ID Kategori" name="id_kategori"
                                    value="<?= $sampah["id_kategori"] ?>" required><br><br> -->

                                <label for="jenis">Jenis</label><br>
                                <input type="text" placeholder="Masukkan Jenis Sampah" name="jenis"
                                    value="<?= $sampah["jenis"] ?>" required><br><br>

                                <label for="harga_pengepul">Harga Pengepul</label><br>
                                <input type="text" id="harga_pengepul" placeholder="Masukkan Harga Pengepul" name="harga_pusat"
                                    value="<?= $sampah["harga_pusat"] ?>" required><br><br>
                                <small id="persentaseHelp" class="form-text text-muted">Masukkan persentase keuntungan dari
                                    harga pengepul.</small> <br>
                                <label for="keuntungan_percent">Persentase Keuntungan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="keuntungan_percent"
                                        placeholder="Masukkan Persentase Keuntungan" name="keuntungan_percent" required
                                        aria-describedby="persentaseHelp">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>


                                <label for="keuntungan">Keuntungan (Hasil)</label><br>
                                <input type="text" id="keuntungan" placeholder="Keuntungan" readonly><br><br>

                                <label for="harga_nasabah">Harga Nasabah (Hasil)</label><br>
                                <input type="text" id="harga_nasabah" placeholder="Harga Nasabah" name="harga"
                                    value="<?= $sampah["harga"] ?>" readonly><br><br>

                                <label for="jumlah">Jumlah</label><br>
                                <input type="text" placeholder="Masukkan Jumlah" name="jumlah" value="<?= $sampah["jumlah"] ?>"
                                    required><br><br>



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