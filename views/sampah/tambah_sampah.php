<?php
$current_page = $_GET['page'] ?? '';
?>


<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';


$categories = getCategories();

if (isset($_POST["submit"])) {
    handleAddWaste($_POST);
}
?>

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- My CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/bank_sampah/assets/css/style.css">

    <title>AdminHub</title>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@400;500;600;700&display=swap');

    /* === General Setup === */

    /* === Main Title === */

    /* === Form Container === */
    .container {
        padding: 1rem;
    }

    /* === Input Styles === */
    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 10px 15px;
        margin-top: 6px;
        margin-bottom: 16px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #f9f9f9;
        font-size: 14px;
        font-family: 'Poppins', 'Lato', sans-serif;
    }

    input[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    /* === Label Styles === */
    label {
        font-weight: 500;
        font-size: 14px;
        color: #555;
    }

    /* === Button Submit (Input) === */
    .inputbtn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 12px 24px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-family: 'Poppins', 'Lato', sans-serif;
    }

    .inputbtn:hover {
        background-color: #45a049;
    }

    /* === Modal Button (Ubah Persentase) === */
    .btn-primary {
        background-color: #00e1ffff;
        border: none;
        font-family: 'Poppins', 'Lato', sans-serif;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    /* === Modal Button Secondary === */
    .btn-secondary {
        background-color: #6c757d;
        border: none;
        font-family: 'Poppins', 'Lato', sans-serif;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
    }

    .btn-secondary:hover {
        background-color: #565e64;
    }

    /* === Modal Content === */
    .modal-content {
        border-radius: 12px;
        font-family: 'Poppins', 'Lato', sans-serif;
    }

    /* === Header Title === */
    .head-title .left span {
        font-size: 14px;
        color: #888;
    }

    .head-title .left h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #333;
    }

    .modal {
        z-index: 1050;
    }
</style>

<body>

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>

            <!-- Breadcrumb -->
            <?php include_once __DIR__ . '/../layouts/breadcrumb.php'; ?>

        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <span>Halaman</span>
                    <h1>Tambah Sampah</h1>
                </div>
            </div>

            <div class="main--content">
                <div class="main--content--monitoring">
                    <!-- Start of Form Section -->
                    <div class="tabular--wrapper">
                        <h3 class="main--title">Isi Form Berikut</h3>
                        <form method="POST" action="">
                            <div class="container">


                                <label for="jenis">Jenis</label><br>
                                <input type="text" placeholder="Masukkan Jenis Sampah" name="jenis" required><br><br>

                                <label for="harga_pengepul">Harga Pengepul</label><br>
                                <input type="text" id="harga_pengepul" placeholder="Masukkan Harga Pengepul"
                                    name="harga_pengepul" required><br><br>

                                <!-- Persentase Keuntungan dengan Button di Samping -->
                                <label for="keuntungan_percent">Persentase Keuntungan (Default: 20%)</label><br>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="keuntungan_percent" name="keuntungan_percent"
                                        value="20" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ubahPersentaseModal">
                                            Ubah Persentase Keuntungan
                                        </button>
                                    </div>
                                </div>
                                <br>

                                <!-- Tambahkan label untuk menampilkan keuntungan -->
                                <label for="keuntungan">Keuntungan (Hasil)</label><br>
                                <input type="text" id="keuntungan" placeholder="Keuntungan" readonly><br><br>

                                <label for="harga_nasabah">Harga Nasabah (Hasil)</label><br>
                                <input type="text" id="harga_nasabah" placeholder="Harga Nasabah" name="harga_nasabah"
                                    readonly><br><br>

                                <label for="kategori">Kategori</label><br>

                                <select name="kategori">
                                    <option value="">Pilih</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <br><br>



                                <button type="submit" name="submit" class="inputbtn">Input</button>
                            </div>
                        </form>
                    </div>

                    <!-- Modal untuk mengubah persentase keuntungan -->
                    <div class="modal fade" id="ubahPersentaseModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Ubah Persentase Keuntungan</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <label for="modal_keuntungan_percent">Masukkan Persentase Keuntungan</label>
                                    <input type="number" class="form-control" id="modal_keuntungan_percent" placeholder="Persentase Keuntungan Baru">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                    <button type="button" class="btn btn-primary" id="saveKeuntungan">Simpan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>



        </main>
        <!-- MAIN -->

    </section>
    <!-- CONTENT -->

    <!-- Bootstrap JS dan dependencies -->

    <script>
        // Mengatur persentase keuntungan default
        var persenKeuntungan = 20;

        // Fungsi untuk menghitung harga nasabah dan keuntungan
        function hitungKeuntungan() {
            var hargaPengepul = parseFloat(document.getElementById('harga_pengepul').value) || 0;

            // Hitung keuntungan: harga_pengepul * persenKeuntungan / 100
            var keuntungan = hargaPengepul * persenKeuntungan / 100;
            document.getElementById('keuntungan').value = keuntungan.toFixed(2);

            // Hitung harga nasabah: harga_pengepul - keuntungan
            var hargaNasabah = hargaPengepul - keuntungan;
            document.getElementById('harga_nasabah').value = hargaNasabah.toFixed(2);
        }

        // Event listener untuk input harga pengepul
        document.getElementById('harga_pengepul').addEventListener('input', hitungKeuntungan);

        // Event listener untuk tombol Simpan pada modal
        document.getElementById('saveKeuntungan').addEventListener('click', function() {
            var inputPersen = parseFloat(document.getElementById('modal_keuntungan_percent').value) || 0;

            // Validasi input: Persentase keuntungan harus lebih besar dari 0
            if (inputPersen > 0) {
                persenKeuntungan = inputPersen;
                document.getElementById('keuntungan_percent').value = persenKeuntungan;
                alert('Persentase keuntungan berhasil diubah menjadi ' + persenKeuntungan + '%');
                $('#ubahPersentaseModal').modal('hide');
            } else {
                alert('Persentase keuntungan harus lebih besar dari 0!');
            }
            hitungKeuntungan();
        });
    </script>
    <script src="script.js"></script>
</body>


</html>