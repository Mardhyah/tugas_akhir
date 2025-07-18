<?php
$current_page = $_GET['page'] ?? '';
?>

<?php
include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../config/koneksi.php';



// Ambil data role dari session jika pengguna login
$loggedInRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Tentukan pilihan role yang bisa diakses
$roles = [];
if ($loggedInRole === 'superadmin') {
    $roles = ['superadmin', 'admin', 'nasabah'];
} elseif ($loggedInRole === 'admin') {
    $roles = ['admin', 'nasabah'];
} else {
    $roles = ['nasabah'];
    $autoRole = 'nasabah';
}

// Pindahkan 'nasabah' ke posisi pertama jika ada
if (($key = array_search('nasabah', $roles)) !== false) {
    unset($roles[$key]);
    array_unshift($roles, 'nasabah');
}

// Inisialisasi variabel
$err = '';
$username = $nama = $email = $notelp = $nik = $alamat = $tgl_lahir = $kelamin = '';

// Ambil nomor rekening terakhir dari database
$last_rek_query = "SELECT MAX(CAST(SUBSTRING(no_rek, 5, 4) AS UNSIGNED)) AS last_rek FROM user WHERE no_rek LIKE 'BSLH%'";
$last_rek_result = mysqli_query($koneksi, $last_rek_query);
$last_rek_number = 0;

if ($last_rek_result && mysqli_num_rows($last_rek_result) > 0) {
    $last_rek_row = mysqli_fetch_assoc($last_rek_result);
    if ($last_rek_row['last_rek'] !== null) {
        $last_rek_number = intval($last_rek_row['last_rek']);
    }
}

// Buat nomor rekening baru dengan format "BSLH0001", "BSLH0002"
$new_rek_number = $last_rek_number + 1;
$new_no_rek = "BSLH" . str_pad($new_rek_number, 4, "0", STR_PAD_LEFT);

// Proses form registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $notelp = trim($_POST['notelp']);
    $nik = trim($_POST['nik']);
    $nip = trim($_POST['nip']);
    $status_gol = isset($_POST['status_gol']) ? $_POST['status_gol'] : 'non-CPNS';
    $gol = ($status_gol === 'CPNS' && isset($_POST['gol'])) ? $_POST['gol'] : 'non-CPNS'; // Default non-CPNS jika tidak diisi
    $bidang = isset($_POST['bidang']) ? $_POST['bidang'] : '';
    $alamat = trim($_POST['alamat']);
    $tgl_lahir = trim($_POST['tgl_lahir']);
    $kelamin = trim($_POST['kelamin']);
    $role = isset($_POST['role']) ? $_POST['role'] : 'nasabah';
    $status = 1; // Pastikan akun baru memiliki status 1

    // Jika pengguna tidak login, role otomatis adalah 'nasabah'
    if (!isset($loggedInRole)) {
        $role = 'nasabah';
    }

    // Validasi input
    if (empty($username) || empty($password) || empty($nama) || empty($role) || empty($email) || empty($notelp) || empty($nik) || empty($alamat) || empty($tgl_lahir) || empty($kelamin)) {
        $err = "Semua bidang harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Format email tidak valid!";
    } elseif (!preg_match('/^\d{16}$/', $nik)) {
        $err = "NIK harus terdiri dari 16 digit angka!";
    } elseif (isset($loggedInRole) && !in_array($role, $roles)) {
        $err = "Role tidak valid!";
    } else {
        // Cek apakah username sudah digunakan
        $check_query = "SELECT username FROM user WHERE username = ?";
        $check_stmt = mysqli_prepare($koneksi, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $err = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Masukkan data ke database
            $insert_query = "INSERT INTO user (username, password, nama, role, email, notelp, nik, alamat, tgl_lahir, kelamin, no_rek, status, gol, bidang, nip) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insert_stmt = mysqli_prepare($koneksi, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "ssssssssssssssi", $username, $hashed_password, $nama, $role, $email, $notelp, $nik, $alamat, $tgl_lahir, $kelamin, $new_no_rek, $status, $gol, $bidang, $nip);

            if (mysqli_stmt_execute($insert_stmt)) {
                $_SESSION['message'] = "Tambah admin berhasil";
                header("location: index.php?page=tambah_nasabah");
                exit;
            } else {
                $_SESSION['message'] = "Gagal menambah admin";
                header("location: index.php?page=tambah_nasabah");
                exit;
            }

            mysqli_stmt_close($insert_stmt);
        }

        mysqli_stmt_close($check_stmt);
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

<style>
    .container {
        max-width: 1000px;
        margin: 30px auto;
        background-color: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        font-family: 'Segoe UI', sans-serif;
    }

    form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .form-group {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(48%, 1fr));
        gap: 20px;
        width: 100%;
    }

    .form-field {
        display: flex;
        flex-direction: column;
    }

    .form-field label {
        margin-bottom: 6px;
        font-weight: 600;
        font-size: 14px;
    }

    .form-input,
    .form-input-nasabah,
    textarea,
    select {
        padding: 10px 12px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border-color 0.3s;
        font-family: inherit;
    }

    .form-input:focus,
    .form-input-nasabah:focus,
    textarea:focus,
    select:focus {
        border-color: #4a90e2;
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-field.full-width {
        grid-column: span 2;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        width: 100%;
        margin-top: 30px;
    }

    .inputbtn {
        padding: 10px 24px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-cancel {
        background-color: #f2f2f2;
        color: #333;
    }

    .btn-cancel:hover {
        background-color: #e0e0e0;
    }

    .btn-submit {
        background-color: #4a90e2;
        color: white;
    }

    .btn-submit:hover {
        background-color: #357ac9;
    }
</style>


<body>

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
                    <h1>Tambah Nasabah</h1>
                </div>
            </div>

            <div class="container">

                <form method="POST" action="">
                    <div class="form-group">
                        <div class="form-field">
                            <label>Username</label>
                            <input type="text" name="username" id="username" class="form-input" placeholder="username" value="<?= htmlspecialchars($username); ?>" required />
                        </div>
                        <div class=" form-field">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-input" placeholder="Nama" required />
                        </div>
                        <div class="form-field">
                            <label>Password</label>
                            <input type="password" name="password" id="password" class="form-input-nasabah" placeholder="Password" value="<?= htmlspecialchars($nama); ?>" required />
                        </div>
                        <div class="form-field">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" id="tgl_lahir" class="form-input-nasabah" value="<?= htmlspecialchars($tgl_lahir); ?>" required />
                        </div>
                        <div class="form-field">
                            <label>Email</label>
                            <input type="email" name="email" class="form-input" placeholder="email@example.com" required />
                        </div>
                        <div class="form-field">
                            <label>Jenis Kelamin</label>
                            <select name="kelamin" id="kelamin" class="form-input" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-Laki" <?= $kelamin == 'Laki-Laki' ? 'selected' : '' ?>>Laki-Laki</option>
                                <option value="Perempuan" <?= $kelamin == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Bidang</label>
                            <input type="text" name="bidang" id="bidang" class="form-input" placeholder="Bidang" />
                        </div>

                        <div class="form-field">
                            <label>NIK</label>
                            <input type="text" name="nik" id="nik" class="form-input" placeholder="NIK" maxlength="16" pattern="\d{16}" title="NIK harus terdiri dari 16 digit angka" required />
                        </div>

                        <div class="form-field">
                            <label>No. Telp</label>
                            <input type="text" name="notelp" id="notelp" class="form-input-nasabah" placeholder="08..." value="<?= htmlspecialchars($notelp); ?>" required />
                        </div>

                        <!-- Status Kepegawaian -->
                        <div class="form-field">
                            <label>Status Kepegawaian</label>
                            <select name="status_gol" id="status_gol" onchange="toggleGolongan()" class="form-input" required>
                                <option value="">Pilih Status</option>
                                <option value="PNS">PNS</option>
                                <option value="Honorer">Non-PNS</option>
                            </select>
                        </div>

                        <!-- Golongan (hanya untuk PNS) -->
                        <div class="form-group" id="golongan_nip_wrapper" style="display: none;">
                            <!-- Golongan (untuk PNS) -->
                            <div class="form-field">
                                <label for="gol">Golongan</label>
                                <select name="gol" id="gol" class="form-input">
                                    <option value="">Pilih Golongan</option>
                                    <option value="I/A">I/A</option>
                                    <option value="I/B">I/B</option>
                                    <option value="I/C">I/C</option>
                                    <option value="I/D">I/D</option>
                                    <option value="II/A">II/A</option>
                                    <option value="II/B">II/B</option>
                                    <option value="II/C">II/C</option>
                                    <option value="II/D">II/D</option>
                                    <option value="III/A">III/A</option>
                                    <option value="III/B">III/B</option>
                                    <option value="III/C">III/C</option>
                                    <option value="III/D">III/D</option>
                                    <option value="IV/A">IV/A</option>
                                    <option value="IV/B">IV/B</option>
                                    <option value="IV/C">IV/C</option>
                                    <option value="IV/D">IV/D</option>
                                    <option value="IV/E">IV/E</option>
                                </select>
                            </div>

                            <!-- NIP (untuk PNS) -->
                            <div class="form-field">
                                <label for="nip">NIP</label>
                                <input type="text" name="nip" id="nip" class="form-input" placeholder="NIP">
                            </div>
                        </div>


                        <!-- Script untuk menampilkan/menyembunyikan berdasarkan status -->
                        <script>
                            function toggleGolongan() {
                                const status = document.getElementById("status_gol").value;
                                const wrapper = document.getElementById("golongan_nip_wrapper");

                                if (status === "PNS") {
                                    wrapper.style.display = "flex";
                                } else {
                                    wrapper.style.display = "none";
                                }
                            }

                            // Jalankan saat halaman dimuat (misalnya saat edit)
                            window.addEventListener('DOMContentLoaded', toggleGolongan);
                        </script>




                        <div class="form-field full-width">
                            <label>Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-input-nasabah" placeholder="Alamat lengkap..." required><?= htmlspecialchars($alamat); ?></textarea>
                        </div>
                    </div>
                    <?php if (isset($loggedInRole)): ?>
                        <div class="form-field">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-input">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>


                    <div class="form-actions">
                        <button type="reset" class="inputbtn btn-cancel">Batal</button>
                        <button type="submit" class="inputbtn">Simpan</button>
                    </div>


                </form>
            </div>




        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->


    <script src="script.js"></script>

</body>