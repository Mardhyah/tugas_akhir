<?php
$current_page = $_GET['page'] ?? '';

include_once __DIR__ . '/../../fungsi.php';
require_once __DIR__ . '/../../crypto/core/crypto_helper.php';

$message = "";

// Ambil harga emas terkini
$current_gold_price_buy  = getCurrentGoldPricebuy();  // harga beli (uang → emas)
$current_gold_price_sell = getCurrentGoldPricesell(); // harga jual (emas → uang)

// Ambil transaksi terakhir untuk generate ID baru
$query  = "SELECT no FROM transaksi ORDER BY no DESC LIMIT 1";
$result = $koneksi->query($query);
$last_id = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['no'] : 0;

// Fungsi generate ID transaksi unik
function generateTransactionID($last_id)
{
    return 'TRANS' . date('Y') . str_pad($last_id + 1, 6, '0', STR_PAD_LEFT);
}

// ----------- PROSES PENCARIAN NASABAH -----------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_value = trim($_POST['search_value']);

    if ($search_value === '') {
        $message = "NIK tidak boleh kosong.";
    } else {
        $user_query = "
            SELECT user.*, dompet.uang, dompet.emas 
            FROM user 
            LEFT JOIN dompet ON user.id = dompet.id_user 
            WHERE user.nik LIKE ? AND user.role = 'Nasabah'
        ";
        $stmt_user = $koneksi->prepare($user_query);
        $like_value = "%{$search_value}%";
        $stmt_user->bind_param("s", $like_value);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();

        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();
            $gold_equivalent = $user_data['emas'] * $current_gold_price_sell;
        } else {
            $message = "User dengan role 'Nasabah' tidak ditemukan.";
        }
    }
}

// ----------- PROSES PENARIKAN -----------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['withdraw'])) {
    $id_user        = $_POST['id_user'];
    $withdraw_type  = $_POST['withdraw_type'] ?? '';
    $jumlah_tarik   = ($withdraw_type === 'money') ? $_POST['jumlah_uang'] : $_POST['jumlah_emas'];
    $jumlah_tarik   = floatval($jumlah_tarik); // pastikan numeric
    $jumlah_tarik_encrypted = encryptWithAES((string)$jumlah_tarik);

    // Ambil saldo emas user
    $stmt_balance = $koneksi->prepare("SELECT emas FROM dompet WHERE id_user = ?");
    $stmt_balance->bind_param("i", $id_user);
    $stmt_balance->execute();
    $user_balance = $stmt_balance->get_result()->fetch_assoc();
    $saldo_emas   = $user_balance['emas'] ?? 0;

    // Validasi input
    if ($jumlah_tarik <= 0) {
        $message = "Jumlah yang ditarik harus lebih dari 0.";
    } elseif ($withdraw_type === 'money') {
        // Konversi uang → emas
        $gold_to_deduct = $jumlah_tarik / $current_gold_price_sell;

        if ($gold_to_deduct > $saldo_emas) {
            $message = "Saldo emas tidak cukup untuk penarikan ini.";
        } else {
            try {
                $koneksi->begin_transaction();

                // Catat transaksi
                $id_transaksi = generateTransactionID($last_id);
                $stmt_transaksi = $koneksi->prepare("
                    INSERT INTO transaksi (no, id, id_user, jenis_transaksi, date, time) 
                    VALUES (NULL, ?, ?, 'tarik_saldo', CURDATE(), CURTIME())
                ");
                $stmt_transaksi->bind_param("ss", $id_transaksi, $id_user);
                $stmt_transaksi->execute();

                // Catat ke tabel tarik_saldo
                $stmt_tarik = $koneksi->prepare("
                    INSERT INTO tarik_saldo (no, id_transaksi, jenis_saldo, jumlah_tarik) 
                    VALUES (NULL, ?, 'tarik_uang', ?)
                ");
                $stmt_tarik->bind_param("ss", $id_transaksi, $jumlah_tarik_encrypted);
                $stmt_tarik->execute();

                // Update saldo emas
                $stmt_update = $koneksi->prepare("
                    UPDATE dompet SET emas = emas - ? WHERE id_user = ?
                ");
                $stmt_update->bind_param("di", $gold_to_deduct, $id_user);
                $stmt_update->execute();

                $koneksi->commit();
                header("Location: index.php?page=nota&id_transaksi=$id_transaksi");
                exit;
            } catch (Exception $e) {
                $koneksi->rollback();
                $message = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    } elseif ($withdraw_type === 'gold') {
        if ($jumlah_tarik < 0.1) {
            $message = "Penarikan emas minimal 0.1 gram.";
        } elseif ($jumlah_tarik > $saldo_emas) {
            $message = "Saldo emas tidak mencukupi.";
        } elseif (($saldo_emas - $jumlah_tarik) < 0.1) {
            $message = "Saldo emas tersisa minimal 0.1 gram.";
        } else {
            try {
                $koneksi->begin_transaction();

                // Catat transaksi
                $id_transaksi = generateTransactionID($last_id);
                $stmt_transaksi = $koneksi->prepare("
                    INSERT INTO transaksi (no, id, id_user, jenis_transaksi, date, time) 
                    VALUES (NULL, ?, ?, 'tarik_saldo', CURDATE(), CURTIME())
                ");
                $stmt_transaksi->bind_param("ss", $id_transaksi, $id_user);
                $stmt_transaksi->execute();

                // Catat ke tabel tarik_saldo
                $stmt_tarik = $koneksi->prepare("
                    INSERT INTO tarik_saldo (no, id_transaksi, jenis_saldo, jumlah_tarik) 
                    VALUES (NULL, ?, 'tarik_emas', ?)
                ");
                $stmt_tarik->bind_param("ss", $id_transaksi, $jumlah_tarik_encrypted);
                $stmt_tarik->execute();

                // Update saldo emas
                $stmt_update = $koneksi->prepare("
                    UPDATE dompet SET emas = emas - ? WHERE id_user = ?
                ");
                $stmt_update->bind_param("di", $jumlah_tarik, $id_user);
                $stmt_update->execute();

                $koneksi->commit();
                header("Location: index.php?page=nota&id_transaksi=$id_transaksi");
                exit;
            } catch (Exception $e) {
                $koneksi->rollback();
                $message = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    } else {
        $message = "Jenis penarikan tidak valid.";
    }
}

// Ambil saldo emas terbaru (untuk ditampilkan di form)
$id_user = $id_user ?? 0;
$stmt_balance = $koneksi->prepare("SELECT emas FROM dompet WHERE id_user = ?");
$stmt_balance->bind_param("i", $id_user);
$stmt_balance->execute();
$emas_balance = $stmt_balance->get_result()->fetch_assoc()['emas'] ?? 0;

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Untuk JavaScript -->
<input type="hidden" id="current_balance_emas" value="<?php echo htmlspecialchars($emas_balance); ?>">




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

    <title>BankSampah</title>
</head>
<script>
    function validateSearchForm() {
        var searchValue = document.getElementById('search_value').value;
        if (searchValue.trim() === '') {
            alert('NIK tidak boleh kosong.');
            return false; // Mencegah form dikirim
        } else if (searchValue.length !== 16 || isNaN(searchValue)) {
            alert('NIK harus berisi 16 digit angka.');
            return false; // Mencegah form dikirim
        }
        return true; // Memungkinkan form dikirim
    }

    function getSuggestions() {
        var search_value = document.getElementById("search_value").value;
        if (search_value.length >= 3) { // Minimal input untuk memulai pencarian
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "get_suggestions.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("suggestions").innerHTML = xhr.responseText;
                    document.getElementById("suggestions").style.display = 'block';
                }
            };
            xhr.send("query=" + search_value);
        } else {
            document.getElementById("suggestions").style.display = 'none';
        }
    }

    function selectSuggestion(nik) {
        document.getElementById("search_value").value = nik;
        document.getElementById("suggestions").style.display = 'none';
    }

    function validateSearchForm() {
        var search_value = document.getElementById("search_value").value;
        if (search_value === "") {
            alert("NIK tidak boleh kosong.");
            return false;
        }
        return true;
    }
</script>
<style>

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
                    <h1>Tarik Saldo</h1>
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
                    <div class="tabular--wrapper">

                        <!-- Search Form -->
                        <form method="POST" action="" onsubmit="return validateSearchForm()">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <input type="text" name="search_value" id="search_value" class="form-control"
                                        placeholder="Search by NIK or Name" maxlength="16" oninput="getSuggestions()" required autocomplete="off">
                                    <div id="suggestions" style="display: none; position: absolute; z-index: 1000; background: #fff; border: 1px solid #ccc;"></div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="search" class="btn btn-dark w-100">CHECK</button>
                                </div>
                            </div>
                        </form>

                        <!-- User Info -->
                        <?php if (isset($user_data)) { ?>
                            <div class="row mb-4">
                                <div class="col-md-5">
                                    <p><strong>ID</strong> : <?= $user_data['id']; ?></p>
                                    <p><strong>NIK</strong> : <?= $user_data['nik']; ?></p>
                                    <p><strong>Email</strong> : <?= $user_data['email']; ?></p>
                                </div>
                                <div class="col-md-5">
                                    <p><strong>Username</strong> : <?= $user_data['username']; ?></p>
                                    <p><strong>Nama Lengkap</strong> : <?= $user_data['nama']; ?></p>
                                    <p><strong>Saldo</strong> : <?= number_format($user_data['emas'], 4, '.', '.'); ?> g = Rp. <?= round($gold_equivalent, 2); ?></p>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <p class="text-danger"><?= $message; ?></p>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- Harga Emas -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                    <input type="text" name="harga_emas_beli" class="form-control"
                                        value="<?= $current_gold_price_buy; ?>" readonly>
                                </div>
                                <small class="form-text text-muted">Harga beli emas per gram</small>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                    <input type="text" name="harga_emas_jual" class="form-control"
                                        value="<?= $current_gold_price_sell; ?>" readonly>
                                </div>
                                <small class="form-text text-muted">Harga jual emas per gram</small>
                            </div>
                        </div>

                        <!-- Form Tarik Saldo -->
                        <?php if (isset($user_data) && !is_null($user_data)) { ?>
                            <form method="POST" action="index.php?page=tarik_saldo" target="notaWindow" onsubmit="return validateWithdrawal();">

                                <!-- Jenis Penarikan -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label><input type="radio" name="withdraw_type" value="money" onclick="toggleWithdrawType()" required> Tarik Uang</label><br>
                                        <label><input type="radio" name="withdraw_type" value="gold" onclick="toggleWithdrawType()"> Tarik Emas</label>
                                    </div>
                                </div>

                                <!-- Input Penarikan Uang -->
                                <div class="row mb-4" id="money_input" style="display: none;">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                            <input type="number" step="0.01" name="jumlah_uang" id="jumlah_uang"
                                                class="form-control" placeholder="Jumlah Uang" autocomplete="off">
                                        </div>
                                        <p id="sisa_saldo_uang" class="text-info"></p>
                                    </div>
                                </div>

                                <!-- Input Penarikan Emas -->
                                <div class="row mb-4" id="gold_input" style="display: none;">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                            <select name="jumlah_emas" id="jumlah_emas" class="form-control">
                                                <option value="0.01">0.01 gram</option>
                                                <option value="0.5">0.5 gram</option>
                                                <option value="1">1 gram</option>
                                                <option value="2">2 gram</option>
                                                <option value="5">5 gram</option>
                                            </select>
                                        </div>
                                        <small class="form-text text-muted">Jumlah emas yang ingin ditarik</small>
                                        <p id="sisa_saldo_emas" class="text-info"></p>
                                    </div>
                                </div>

                                <!-- Data Hidden -->
                                <input type="hidden" name="id_user" value="<?= $user_data['id']; ?>">
                                <input type="hidden" id="current_balance_emas_hidden" value="<?= (float)$user_data['emas']; ?>">

                                <!-- Tombol -->
                                <div class="row">
                                    <div class="col-md-8">
                                        <button type="submit" name="withdraw" class="btn btn-success">Tarik</button>
                                    </div>
                                </div>
                            </form>
                        <?php } else { ?>
                            <p>Silakan cari Data nasabah dengan NIK.</p>
                        <?php } ?>

                        <!-- Pesan -->
                        <?php if ($message) { ?>
                            <div class="alert alert-info"><?= $message; ?></div>
                        <?php } ?>

                    </div>
                </div>




                <!-- End of Form Section -->
            </div>
            </div>


            <script>
                if (window.opener) {
                    window.opener.location.reload();
                }
            </script>

            <script>
                // Ambil elemen radio untuk jenis penarikan
                const withdrawTypeRadios = document.querySelectorAll('input[name="withdraw_type"]');
                const moneyInput = document.getElementById('money_input');
                const goldInput = document.getElementById('gold_input');

                // Elemen untuk menampilkan sisa saldo
                const sisaSaldoUang = document.getElementById('sisa_saldo_uang');
                const sisaSaldoEmas = document.getElementById('sisa_saldo_emas');

                // Inputan jumlah
                const jumlahEmasSelect = document.getElementById('jumlah_emas');
                const jumlahUangInput = document.getElementById('jumlah_uang');

                // Nilai saldo dari input hidden (pastikan ini ada di HTML kamu)
                const currentBalanceEmas = parseFloat(document.getElementById('current_balance_emas').value);
                const currentGoldPriceSell = parseFloat(<?php echo $current_gold_price_sell; ?>); // harga jual emas per gram

                // Fungsi untuk menampilkan input sesuai pilihan
                withdrawTypeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'money') {
                            moneyInput.style.display = 'block';
                            goldInput.style.display = 'none';
                            sisaSaldoEmas.style.display = 'none';
                            sisaSaldoUang.style.display = 'block';
                            sisaSaldoUang.textContent = '';
                        } else if (this.value === 'gold') {
                            moneyInput.style.display = 'none';
                            goldInput.style.display = 'block';
                            sisaSaldoUang.style.display = 'none';
                            sisaSaldoEmas.style.display = 'block';
                            sisaSaldoEmas.textContent = '';
                        }
                    });
                });
            </script>






        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
</body>

</html>