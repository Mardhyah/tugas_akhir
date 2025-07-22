<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../fungsi.php';

// Variabel untuk menyimpan pesan atau error
$message = "";

// Cek apakah pengguna sudah login
checkSession();

// Mendapatkan username dari session
$username = $_SESSION['username'];

// Fetch data jenis dan harga (Move this section up)
$jenis_query = "SELECT id, jenis, harga, id_kategori, harga_pusat FROM sampah";
$jenis_result = $koneksi->query($jenis_query);

// Simpan data jenis sampah ke dalam array (Move this section up)
$jenis_sampah = [];
if ($jenis_result->num_rows > 0) {
    while ($row = $jenis_result->fetch_assoc()) {
        $jenis_sampah[$row['id']] = [
            'jenis' => $row['jenis'],
            'harga' => $row['harga'],
            'harga_pusat' => $row['harga_pusat'],
            'id_kategori' => $row['id_kategori']
        ];
    }
}
// Ambil data pengguna dari database
$data = getUserData($koneksi, $username);
$id_user = $data['id']; // Mendapatkan id_user dari data user yang sedang login

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $jenis_transaksi = 'jual_sampah';

    // Ambil data dari POST
    $id_kategoris = $_POST['id_kategori'] ?? [];
    $id_jeniss = $_POST['id_jenis'] ?? [];
    $jumlahs = $_POST['jumlah'] ?? [];

    // Generate new ID for transaksi
    $id_trans_query = "SELECT no FROM transaksi ORDER BY no DESC LIMIT 1";
    $result = $koneksi->query($id_trans_query);
    $last_no = ($result->num_rows > 0) ? $result->fetch_assoc()['no'] : 0;
    $new_no = $last_no + 1;

    // Create new transaksi ID
    $id_transaksi = 'TRANS' . date('Y') . str_pad($new_no, 6, '0', STR_PAD_LEFT);

    // Set the default timezone to Asia/Jakarta
    date_default_timezone_set('Asia/Jakarta');
    $date = date('Y-m-d'); // Get the current date
    $time = date('H:i:s'); // Get the current time

    // Insert into transaksi table
    $transaksi_query = mysqli_query($koneksi, "INSERT INTO transaksi (no, id, id_user, jenis_transaksi, date, time) VALUES (NULL, '$id_transaksi', '$id_user','$jenis_transaksi', '$date', '$time')");

    if ($transaksi_query) {
        // Loop untuk setiap kategori dan jenis sampah
        for ($i = 0; $i < count($id_kategoris); $i++) {
            $id_jenis = $id_jeniss[$i];
            $jumlah_kg = $jumlahs[$i];
            $harga = $jenis_sampah[$id_jenis]['harga']; // harga dari nasabah
            $harga_pusat = $jenis_sampah[$id_jenis]['harga_pusat']; // harga dari pusat
            $harga_nasabah = $jumlah_kg * $harga;
            $jumlah_rp = $jumlah_kg * $harga_pusat;

            // Insert ke tabel jual_sampah
            $jual_sampah_query = "INSERT INTO jual_sampah (no, id_transaksi, id_sampah, jumlah_kg, harga_nasabah, jumlah_rp) 
                                  VALUES (NULL, '$id_transaksi', '$id_jenis', '$jumlah_kg', '$harga_nasabah', '$jumlah_rp')";

            if ($koneksi->query($jual_sampah_query) === FALSE) {
                $message = "Error: " . $koneksi->error;
                break;
            }

            // Update stok sampah di tabel sampah
            $update_sampah_query = "UPDATE sampah SET jumlah = jumlah - $jumlah_kg WHERE id = '$id_jenis'";
            if ($koneksi->query($update_sampah_query) === FALSE) {
                $message = "Error updating sampah: " . $koneksi->error;
                break;
            }
        }
        // if (empty($message)) {
        //     $message = "Transaction successful!";
        //Uncomment this line when ready to redirect
        echo "<script>
    window.open('index.php?page=nota&id_transaksi=$id_transaksi', '_blank');
    window.location.href='index.php?page=jual_sampah';
</script>";
        exit;
    } else {
        $message = "Error inserting into transaksi: " . $koneksi->error;
    }
}

// Fetch data kategori
$kategori_query = "SELECT id, name FROM kategori_sampah";
$kategori_result = $koneksi->query($kategori_query);

// Fetch data jenis dan harga
$jenis_query = "SELECT id, jenis, harga, id_kategori, harga_pusat, jumlah FROM sampah";
$jenis_result = $koneksi->query($jenis_query);

// Simpan data jenis sampah ke dalam array
$jenis_sampah = [];
if ($jenis_result->num_rows > 0) {
    while ($row = $jenis_result->fetch_assoc()) {
        $jenis_sampah[$row['id']] = [
            'jenis' => $row['jenis'],
            'harga' => $row['harga'],
            'harga_pusat' => $row['harga_pusat'],
            'id_kategori' => $row['id_kategori'],
            'jumlah' => $row['jumlah']
        ];
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- My CSS -->
    <link rel="stylesheet" href="/bank_sampah/assets/css/style.css">

    <title>AdminHub</title>
</head>
<script>
    var jenisSampah = <?php echo json_encode($jenis_sampah); ?>;

    function updateHarga(index) {
        var idjenis = document.getElementById('id_jenis_' + index).value;
        var jumlah = document.getElementById('jumlah_' + index).value;
        var harga_nasabah = jenisSampah[idjenis] ? jenisSampah[idjenis].harga : 0;
        var harga = jenisSampah[idjenis] ? jenisSampah[idjenis].harga_pusat : 0;
        var total_Harga_nasabah = jumlah * harga_nasabah;
        var totalHarga = jumlah * harga;

        document.getElementById('harga_' + index).value = 'Rp. ' + totalHarga.toLocaleString('id-ID');
        updateTotalHarga();
    }

    function updateTotalHarga() {
        var totalHarga = 0;
        var hargaInputs = document.querySelectorAll('input[name="harga[]"]');

        hargaInputs.forEach(function(hargaInput) {
            var harga = parseInt(hargaInput.value.replace(/[Rp.,\s]/g, '')) || 0;
            totalHarga += harga;
        });

        document.getElementById('totalHarga').innerText = 'Rp. ' + totalHarga.toLocaleString('id-ID');
    }

    function updateJenis(index) {
        var kategoriSelect = document.getElementById('id_kategori_' + index);
        var jenisSelect = document.getElementById('id_jenis_' + index);
        var selectedKategori = kategoriSelect.value;

        jenisSelect.innerHTML = '<option value="">-- jenis sampah --</option>';
        for (var id in jenisSampah) {
            if (jenisSampah[id].id_kategori == selectedKategori) {
                var option = document.createElement('option');
                option.value = id;
                option.text = jenisSampah[id].jenis;
                jenisSelect.add(option);
            }
        }
        jenisSelect.value = "";
        updateHarga(index);
    }

    function addRow() {
        var tbody = document.querySelector('#transaksiTable tbody');
        var rowCount = tbody.rows.length + 1; // Adjust row count to account for existing rows in tbody
        var row = tbody.insertRow(); // Add row to tbody instead of the table directly


        row.innerHTML = `
                <td><button class="btn btn-danger" onclick="removeRow(this)">&times;</button></td>
                <td>${rowCount}</td>
                <td>
                    <select name="id_kategori[]" id="id_kategori_${rowCount}" class="form-control" onchange="updateJenis(${rowCount})">
                        <option value="">-- kategorikk sampah --</option>
                        <?php
                        if ($kategori_result->num_rows > 0) {
                            while ($row = $kategori_result->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select name="id_jenis[]" id="id_jenis_${rowCount}" class="form-control" onchange="updateHarga(${rowCount})">
                        <option value="">-- jenis sampah --</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="jumlah[]" id="jumlah_${rowCount}" class="form-control" placeholder="Jumlah" oninput="updateHarga(${rowCount})">
                </td>
                <td>
                    <input type="text" name="harga[]" id="harga_${rowCount}" class="form-control" readonly>
                </td>
            `;
    }

    function removeRow(button) {
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        updateTotalHarga();
    }

    function updateHarga(index) {
        var idjenis = document.getElementById('id_jenis_' + index).value;
        var jumlah = parseInt(document.getElementById('jumlah_' + index).value); // Konversi jumlah ke integer
        var harga_nasabah = jenisSampah[idjenis] ? jenisSampah[idjenis].harga : 0;
        var harga = jenisSampah[idjenis] ? jenisSampah[idjenis].harga_pusat : 0;
        var total_Harga_nasabah = jumlah * harga_nasabah;
        var totalHarga = jumlah * harga;

        // Cek stok, konversi stok menjadi integer
        var stok = jenisSampah[idjenis] ? parseInt(jenisSampah[idjenis].jumlah) : 0;
        if (jumlah > stok) {
            alert('Jumlah sampah yang diinput melebihi stok yang tersedia! Stok tersedia: ' + stok + ' KG');
            document.getElementById('jumlah_' + index).value = stok; // Set input jumlah ke stok maksimum
            jumlah = stok; // Sesuaikan variabel jumlah agar perhitungan tetap benar
        }

        document.getElementById('harga_' + index).value = 'Rp. ' + totalHarga.toLocaleString('id-ID');
        updateTotalHarga();
    }
</script>
<style>
    /* Styling for suggestion box */
    #suggestions {
        position: absolute;
        z-index: 1000;
        background-color: white;
        border: 1px solid #ccc;
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
    }

    /* Styling for each suggestion item */
    #suggestions div {
        padding: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    /* Hover effect */
    #suggestions div:hover {
        background-color: #f0f0f0;
    }

    /* Ensure it doesn't overlap with sidebar */
    .sidebar+#suggestions {
        margin-left: 0;
        position: relative;
    }

    /* Mobile-friendly adjustment */
    @media (max-width: 768px) {
        #suggestions {
            width: 100%;
            left: 0;
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
                    <h1>Jual Sampah</h1>
                </div>
                <div class="header--wrapper">

                    <div class="user--info">
                        <a href="index.php?page=setor_sampah">
                            <button type="button" name="button" class="inputbtn">Setor Sampah</button>
                        </a>
                        <a href="index.php?page=tarik_saldo">
                            <button type="button" name="button" class="inputbtn">Tarik Saldo</button>
                        </a>
                        <a href="index.php?page=jual_sampah">
                            <button type="button" name="button" class="inputbtn">Jual Sampah</button>
                        </a>

                    </div>
                </div>
            </div>

            <div class="main--content">
                <div class="main--content--monitoring">
                    <!-- Start of Form Section -->
                    <div class="tabular--wrapper">
                        <!-- Search Section -->

                        <!-- Start of Form Section -->
                        <div class="tabular--wrapper">

                            <!-- Date and Time Section -->
                            <form method="POST" action="">

                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <input type="date" name="tanggal" class="form-control"
                                            value="<?php echo date('Y-m-d'); ?>" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        // Set zona waktu ke WIB (UTC+7)
                                        date_default_timezone_set('Asia/Jakarta');
                                        $current_time = date('H:i');
                                        ?>
                                        <input type="time" name="waktu" class="form-control"
                                            value="<?php echo $current_time; ?>" disabled>
                                    </div>
                                </div>

                                <!-- Table Section -->
                                <table class="table table-bordered" id="transaksiTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>No</th>
                                            <th>Kategori</th>
                                            <th>Jenis</th>
                                            <th>Jumlah(KG)</th>
                                            <th>Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        if ($kategori_result->num_rows > 0) {
                                            while ($row = $kategori_result->fetch_assoc()) {
                                                echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                                            }
                                        }
                                        ?>


                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">Total Harga:</th>
                                            <th id="totalHarga">Rp. 0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <button type="button" class="btn btn-dark mb-3" onclick="addRow()">Tambah Baris</button>
                                <button type="submit" name="submit" class="btn btn-primary mb-3">SUBMIT</button>
                                <!-- Success/Error Message -->
                                <?php if (!empty($message)) { ?>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <p class="text-success"><?php echo $message; ?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </form>
                        </div>



                    </div>
                    <!-- End of Form Section -->
                </div>
            </div>






        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
</body>

</html>