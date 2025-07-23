<?php
$current_page = $_GET['page'] ?? '';
?>

<?php

include_once __DIR__ . '/../../fungsi.php';
require_once __DIR__ . '/../../crypto/crypto_helper.php';




// Variabel untuk menyimpan pesan atau error
$message = "";

// Retrieve the last inserted transaction ID
$query = "SELECT no FROM transaksi ORDER BY no DESC LIMIT 1";
$result = $koneksi->query($query);

$current_gold_price_buy = getCurrentGoldPricebuy(); // For converting money to gold
$current_gold_price_sell = getCurrentGoldPricesell(); // For converting gold to money

// Jika tombol CHECK ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_value = $_POST['search_value'];
    if (empty($search_value)) {
        $message = "NIK tidak boleh kosong.";
    } else {
        $user_query = "SELECT user.*, dompet.uang, dompet.emas FROM user 
                    LEFT JOIN dompet ON user.id = dompet.id_user 
                    WHERE user.nik LIKE '%$search_value%' AND user.role = 'Nasabah'";
        $user_result = $koneksi->query($user_query);

        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();

            // Ambil harga emas terkini
            $current_gold_price_sell = getCurrentGoldPricesell();

            // Hitung jumlah emas yang setara dengan saldo uang
            $gold_equivalent = $user_data['emas'] * $current_gold_price_sell;
        } else {
            $message = "User dengan role 'Nasabah' tidak ditemukan.";
        }
    }
}
// If NIK has been searched and the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['withdraw'])) {
    $id_user = $_POST['id_user'];

    if ($result && $result->num_rows > 0) {
        $last_id = $result->fetch_assoc()['no'];
    } else {
        $last_id = 0; // No previous record found
    }

    $new_id = $last_id + 1;
    $id = 'TRANS' . date('Y') . str_pad($new_id, 6, '0', STR_PAD_LEFT); // Generate unique transaction ID

    // Set the default timezone to Asia/Jakarta
    date_default_timezone_set('Asia/Jakarta');

    $jenis_transaksi = 'tarik_saldo'; // Set jenis_transaksi
    $date = date('Y-m-d'); // Get the current date
    $time = date('H:i:s'); // Get the current time

    // Fetch user's gold balance
    $balance_query = "SELECT emas FROM dompet WHERE id_user = ?";
    $stmt_balance = $koneksi->prepare($balance_query);
    $stmt_balance->bind_param("i", $id_user);
    $stmt_balance->execute();
    $balance_result = $stmt_balance->get_result();
    $user_balance = $balance_result->fetch_assoc();

    if (isset($_POST['withdraw_type']) && ($_POST['withdraw_type'] === 'money' || $_POST['withdraw_type'] === 'gold')) {
        $withdraw_type = $_POST['withdraw_type'];

        // Determine withdrawal amount
        $jumlah_tarik = ($withdraw_type === 'money') ? $_POST['jumlah_uang'] : $_POST['jumlah_emas'];
        $jumlah_tarik_encrypted = encryptWithAES((string)$jumlah_tarik);

        // Validate withdrawal amount
        if (empty($jumlah_tarik) || !is_numeric($jumlah_tarik)) {
            $message = "Jumlah yang ditarik harus diisi dan berupa angka.";
        } elseif ($withdraw_type === 'money') {
            // Calculate how much gold needs to be deducted for money withdrawal
            $gold_to_deduct = $jumlah_tarik / $current_gold_price_sell; // Convert money to equivalent gold amount
            if ($gold_to_deduct > $user_balance['emas']) {
                $message = "Jumlah yang ditarik tidak boleh melebihi saldo emas Anda.";
            } else {
                try {
                    // Proceed with the transaction if the amount is valid
                    $koneksi->begin_transaction();

                    // Insert into transaksi table
                    $transaksi_query = "INSERT INTO transaksi (no, id, id_user, jenis_transaksi, date, time) VALUES (NULL, ?, ?, ?, ?, ?)";
                    $stmt_transaksi = $koneksi->prepare($transaksi_query);
                    $stmt_transaksi->bind_param("sssss", $id, $id_user, $jenis_transaksi, $date, $time);
                    $stmt_transaksi->execute();

                    // Insert into tarik_saldo table
                    $jenis_saldo = 'tarik_uang';
                    $stmt = $koneksi->prepare("INSERT INTO tarik_saldo (no, id_transaksi, jenis_saldo, jumlah_tarik) VALUES (NULL, ?, ?, ?)");
                    $stmt->bind_param("sss", $id, $jenis_saldo, $jumlah_tarik_encrypted); // ✅ pakai yang sudah terenkripsi

                    $stmt->execute();

                    // Update user's gold balance
                    $update_gold_query = "UPDATE dompet SET emas = emas - ? WHERE id_user = ?";
                    $stmt_update = $koneksi->prepare($update_gold_query);
                    $stmt_update->bind_param("di", $gold_to_deduct, $id_user);
                    $stmt_update->execute();

                    // Commit transaction
                    $koneksi->commit();

                    // Display success message
                    $message = "Penarikan uang berhasil! Saldo emas Anda telah diperbarui.";

                    // Redirect to nota.php
                    header("Location: index.php?page=nota&id_transaksi=$id");
                    exit;
                } catch (Exception $e) {
                    // Rollback transaction in case of an error
                    $koneksi->rollback();
                    $message = "Terjadi kesalahan saat melakukan penarikan: " . $e->getMessage();
                }
            }
        } elseif ($withdraw_type === 'gold') {
            // Validate gold withdrawal amount
            if ($jumlah_tarik < 0.1) {
                $message = "Jumlah emas yang ditarik tidak boleh kurang dari 0.1 gram.";
            } elseif ($jumlah_tarik > $user_balance['emas']) {
                $message = "Jumlah emas yang ditarik tidak boleh melebihi saldo emas Anda.";
            } elseif (($user_balance['emas'] - $jumlah_tarik) < 0.1) {
                $message = "Saldo emas tidak boleh kurang dari 0.1 gram setelah penarikan.";
            } else {
                try {
                    // Proceed with the transaction if the amount is valid
                    $koneksi->begin_transaction();

                    // Insert into transaksi table
                    $transaksi_query = "INSERT INTO transaksi (no, id, id_user, jenis_transaksi, date, time) VALUES (NULL, ?, ?, ?, ?, ?)";
                    $stmt_transaksi = $koneksi->prepare($transaksi_query);
                    $stmt_transaksi->bind_param("sssss", $id, $id_user, $jenis_transaksi, $date, $time);
                    $stmt_transaksi->execute();

                    // Insert into tarik_saldo table
                    $jenis_saldo = 'tarik_emas';
                    $stmt = $koneksi->prepare("INSERT INTO tarik_saldo (no, id_transaksi, jenis_saldo, jumlah_tarik) VALUES (NULL, ?, ?, ?)");
                    $stmt->bind_param("sss", $id, $jenis_saldo, $jumlah_tarik_encrypted); // ✅ juga pakai yang sudah terenkripsi

                    $stmt->execute();

                    // Update user's gold balance
                    $update_gold_query = "UPDATE dompet SET emas = emas - ? WHERE id_user = ?";
                    $stmt_update = $koneksi->prepare($update_gold_query);
                    $stmt_update->bind_param("di", $jumlah_tarik, $id_user);
                    $stmt_update->execute();

                    // Commit transaction
                    $koneksi->commit();

                    // Display success message
                    $message = "Penarikan emas berhasil! Saldo emas Anda telah diperbarui.";

                    // Redirect to nota.php

                    header("Location: index.php?page=nota&id_transaksi=$id");
                    exit;
                } catch (Exception $e) {
                    // Rollback transaction in case of an error
                    $koneksi->rollback();
                    $message = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch user's gold balance again if needed
$balance_query = "SELECT emas FROM dompet WHERE id_user = ?";
$stmt_balance = $koneksi->prepare($balance_query);
$stmt_balance->bind_param("i", $id_user);
$stmt_balance->execute();
$balance_result = $stmt_balance->get_result();
$user_balance = $balance_result->fetch_assoc();


// Ensure user_balance is set
$emas_balance = isset($user_balance['emas']) ? $user_balance['emas'] : 0;
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
?>

<!-- Include the value in a hidden field for use in JavaScript -->
<input type="hidden" id="current_balance_emas" value="<?php echo htmlspecialchars($emas_balance); ?>">


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
                    <!-- Start of Form Section -->
                    <div class="tabular--wrapper">
                        <!-- Search Section -->


                        <!-- Main Content -->

                        <!-- Search Section -->
                        <form method="POST" action="" onsubmit="return validateSearchForm()">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <input type="text" name="search_value" id="search_value" class="form-control"
                                        placeholder="Search by NIK or Name" maxlength="16" oninput="getSuggestions()" required autocomplete="off">
                                    <div id="suggestions" style="display: none; position: absolute; z-index: 1000; background: #fff; border: 1px solid #ccc;">
                                        <!-- Suggestions will be displayed here -->
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="search" class="btn btn-dark w-100">CHECK</button>
                                </div>
                            </div>
                        </form>


                        <!-- User Information Section -->
                        <?php if (isset($user_data)) { ?>
                            <div class="row mb-4">
                                <div class="col-md-5">

                                    <p><strong>ID</strong> : <?php echo $user_data['id']; ?></p>
                                    <p><strong>NIK</strong> : <?php echo $user_data['nik']; ?></p>
                                    <p><strong>Email</strong> : <?php echo $user_data['email']; ?></p>
                                </div>
                                <div class="col-md-5">
                                    <p><strong>Username</strong> : <?php echo $user_data['username']; ?></p>

                                    <p><strong>Nama Lengkap</strong> : <?php echo $user_data['nama']; ?></p>
                                    <p><strong>Saldo</strong> :
                                        <?php echo number_format($user_data['emas'], 4, '.', '.'); ?> g =
                                        Rp. <?php echo round($gold_equivalent, 2); ?>
                                    </p>
                                    <!-- <p><strong>Saldo Emas</strong> : <?php echo number_format($user_data['emas'], 4, ',', '.'); ?> g</p> -->
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <p class="text-danger"><?php echo $message; ?></p>
                                </div>
                            </div>
                        <?php }
                        ?>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                    </div>
                                    <input type="text" name="harga_emas_beli" class="form-control"
                                        placeholder="Harga Emas Beli" value="<?php echo $current_gold_price_buy; ?>"
                                        readonly>
                                </div>
                                <small class="form-text text-muted">Harga beli emas (saat ini) per gram</small>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                    </div>
                                    <input type="text" name="harga_emas_jual" class="form-control"
                                        placeholder="Harga Emas Jual" value="<?php echo $current_gold_price_sell; ?>"
                                        readonly>
                                </div>
                                <small class="form-text text-muted">Harga jual emas (saat ini) per gram</small>
                            </div>
                        </div>

                        <!-- Form Tarik Saldo -->
                        <?php if (isset($user_data) && !is_null($user_data)) { ?>
                            <form method="POST" action="index.php?page=tarik_saldo" target="notaWindow" onsubmit="window.open('', 'notaWindow').focus();">
                                <!-- Withdrawal Type Selection -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label><input type="radio" name="withdraw_type" value="money" required> Tarik
                                            Uang</label><br>
                                        <label><input type="radio" name="withdraw_type" value="gold"> Tarik Emas</label>
                                    </div>
                                </div>

                                <!-- Amount Section (Dynamic based on selection) -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <div id="money_input" style="display: none;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                                </div>
                                                <input type="number" step="0.01" name="jumlah_uang" id="jumlah_uang"
                                                    class="form-control" placeholder="Jumlah Uang">

                                            </div>
                                            <!-- <small class="form-text text-muted">Jumlah uang yang ingin ditarik</small>
                                        tampilkan saldo dikurangi inputan yang ingin ditarik -->

                                            <p id="sisa_saldo_uang" class="text-info"></p> <!-- Remaining money balance -->
                                        </div>

                                        <div id="gold_input" style="display: none;">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                                </div>
                                                <!-- <label for="jumlah_emas">Jumlah Emas (gram)</label> -->
                                                <select name="jumlah_emas" id="jumlah_emas" class="form-control">
                                                    <option value="0.5">0.5 gram</option>
                                                    <option value="0.01">0.01 gram</option>

                                                    <option value="1">1 gram</option>
                                                    <option value="2">2 gram</option>
                                                    <option value="5">5 gram</option>
                                                </select>
                                            </div>
                                            <small class="form-text text-muted">Jumlah emas yang ingin ditarik</small>
                                            <p id="sisa_saldo_emas" class="text-info" style="display: none;"></p>
                                            <input type="hidden" id="current_balance_emas"
                                                value="<?php echo $user_balance['emas']; ?>">
                                            <!-- Remaining gold balance -->

                                            <input type="hidden" id="current_balance_uang"
                                                value="<?php echo $user_balance['uang']; ?>">
                                            <input type="hidden" id="current_balance_emas"
                                                value="<?php echo $user_balance['emas']; ?>">

                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden Field for User ID -->
                                <input type="hidden" name="id_user" value="<?php echo $user_data['id']; ?>">

                                <!-- Submit Button -->
                                <div class="row">
                                    <div class="col-md-8">
                                        <button type="submit" name="withdraw" class="btn btn-success" onclick="setTimeout(() => window.location.reload(), 500);">Tarik</button>
                                        <!-- <a type="submit" name="withdraw" class="btn btn-success" href="nota.php" target="_blank">Tarik</a> -->
                                    </div>
                                </div>
                            </form>
                        <?php } else { ?>
                            <p> Silakan cari Data nasabah dengan NIK. </p>
                        <?php } ?>

                        <!-- Display any error or success messages -->
                        <?php if ($message) { ?>
                            <div class="alert alert-info">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>
                    </div>
                    <!-- End of Form Section -->
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
                // Show/hide input fields based on withdrawal type
                const withdrawTypeRadios = document.querySelectorAll('input[name="withdraw_type"]');
                const moneyInput = document.getElementById('money_input');
                const goldInput = document.getElementById('gold_input');
                const sisaSaldoUang = document.getElementById('sisa_saldo_uang'); // Display remaining money balance
                const sisaSaldoEmas = document.getElementById('sisa_saldo_emas'); // Display remaining gold balance
                const jumlahEmasSelect = document.getElementById('jumlah_emas');
                const jumlahUangInput = document.getElementById('jumlah_uang');

                // Saldo yang diambil dari input hidden
                const currentBalanceEmas = parseFloat(document.getElementById('current_balance_emas').value);
                const currentGoldPriceSell = parseFloat(<?php echo $current_gold_price_sell; ?>); // Gold price for selling

                // Function to show/hide the correct input field based on the selected withdrawal type
                withdrawTypeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'money') {
                            moneyInput.style.display = 'block';
                            goldInput.style.display = 'none';
                            sisaSaldoEmas.style.display = 'none';
                            sisaSaldoUang.style.display = 'block';
                        } else if (this.value === 'gold') {
                            moneyInput.style.display = 'none';
                            goldInput.style.display = 'block';
                            sisaSaldoUang.style.display = 'none';
                            sisaSaldoEmas.style.display = 'block';
                        }
                    });
                });

                // Update remaining gold balance when the user selects an amount of gold
                jumlahEmasSelect.addEventListener('change', function() {
                    const selectedAmount = parseFloat(this.value);
                    const remainingBalance = currentBalanceEmas - selectedAmount;

                    if (remainingBalance < 0.1) {
                        sisaSaldoEmas.textContent = `Saldo emas setelah penarikan: ${remainingBalance.toFixed(3)} gram (minimum saldo harus 0.1 gram)`;
                        sisaSaldoEmas.classList.remove('text-info');
                        sisaSaldoEmas.classList.add('text-danger');
                    } else {
                        sisaSaldoEmas.textContent = `Saldo emas setelah penarikan: ${remainingBalance.toFixed(3)} gram`;
                        sisaSaldoEmas.classList.remove('text-danger');
                        sisaSaldoEmas.classList.add('text-info');
                    }
                });


                // Update remaining balance when the user inputs money to withdraw
                // jumlahUangInput.addEventListener('input', function() {
                //     const jumlahUang = parseFloat(this.value);
                //     const emasToDeduct = jumlahUang / currentGoldPriceSell; // Convert money to gold
                //     const remainingEmas = currentBalanceEmas - emasToDeduct;

                //     if (remainingEmas < 0.1) {
                //         sisaSaldoUang.textContent =
                //             `Sisa emas setelah penarikan4: ${remainingEmas.toFixed(3)} gram (tidak boleh kurang dari 0.1 gram!)`;
                //         sisaSaldoUang.classList.add('text-danger');
                //     } else {
                //         sisaSaldoUang.textContent = `Sisa emas setelah penarikan2: ${remainingEmas.toFixed(3)} gram`;
                //         sisaSaldoUang.classList.remove('text-danger');
                //         sisaSaldoUang.classList.add('text-info');
                //     }
                // });
            </script>

        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>
</body>

</html>