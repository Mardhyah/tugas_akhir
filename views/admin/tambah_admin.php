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
if ($loggedInRole === 'admin') {
    $roles = ['admin', 'nasabah'];
} else {
    $roles = ['nasabah'];
    $autoRole = 'nasabah';
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

// Buat nomor rekening baru dengan form-add-adminat "BSLH0001", "BSLH0002"
$new_rek_number = $last_rek_number + 1;
$new_no_rek = "BSLH" . str_pad($new_rek_number, 4, "0", STR_PAD_LEFT);

// Proses form-add-admin registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $notelp = trim($_POST['notelp']);
    $nik = trim($_POST['nik']);
    $nip = isset($_POST['nip']) ? trim($_POST['nip']) : null;
    $status_gol = isset($_POST['status_gol']) ? $_POST['status_gol'] : null;
    $gol = isset($_POST['gol']) ? $_POST['gol'] : null;
    $bidang = isset($_POST['bidang']) ? trim($_POST['bidang']) : null;
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
        $err = "form-add-adminat email tidak valid!";
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
            mysqli_stmt_bind_param(
                $insert_stmt,
                "sssssssssssssss",
                $username,
                $hashed_password,
                $nama,
                $role,
                $email,
                $notelp,
                $nik,
                $alamat,
                $tgl_lahir,
                $kelamin,
                $new_no_rek,
                $status,
                $gol,
                $bidang,
                $nip
            );

            if (mysqli_stmt_execute($insert_stmt)) {
                $_SESSION['message'] = "Tambah admin berhasil";
                header("location: index.php?page=tambah_admin");
                exit;
            } else {
                $_SESSION['message'] = "Gagal menambah admin";
                header("location: index.php?page=tambah_admin");
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
    <link rel="stylesheet" href="../../assets/css/style.css">

    <title>BankSampah</title>
</head>




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
                    <h1>Tambah admin</h1>
                </div>
            </div>

            <div class="container-add-admin">

                <form method="POST" action="">
                    <div class="form-add-admin-group">
                        <div class="form-add-admin-field">
                            <label>Username</label>
                            <input type="text" name="username" id="username" class="form-add-admin-input" placeholder="username" value="<?= htmlspecialchars($username); ?>" required />
                        </div>
                        <div class=" form-add-admin-field">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-add-admin-input" placeholder="Nama" required />
                        </div>
                        <div class="form-add-admin-field">
                            <label>Password</label>
                            <input type="password" name="password" id="password" class="form-add-admin-input-nasabah" placeholder="Password" value="<?= htmlspecialchars($nama); ?>" required />
                        </div>
                        <div class="form-add-admin-field">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" id="tgl_lahir" class="form-add-admin-input-nasabah" value="<?= htmlspecialchars($tgl_lahir); ?>" required />
                        </div>
                        <div class="form-add-admin-field">
                            <label>Email</label>
                            <input type="email" name="email" class="form-add-admin-input" placeholder="email@example.com" required />
                        </div>

                        <div class="form-add-admin-field">
                            <label>Jenis Kelamin</label>
                            <select name="kelamin" id="kelamin" class="form-add-admin-input" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-Laki" <?= $kelamin == 'Laki-Laki' ? 'selected' : '' ?>>Laki-Laki</option>
                                <option value="Perempuan" <?= $kelamin == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <!-- <div class="form-add-admin-field">
                            <label>Bidang</label>
                            <input type="text" name="bidang" id="bidang" class="form-add-admin-input" placeholder="Bidang" />
                        </div> -->

                        <div class="form-add-admin-field">
                            <label>NIK</label>
                            <input type="text" name="nik" id="nik" class="form-add-admin-input" placeholder="NIK" maxlength="16" pattern="\d{16}" title="NIK harus terdiri dari 16 digit angka" required />
                        </div>

                        <div class="form-add-admin-field">
                            <label>No. Telp</label>
                            <input type="text" name="notelp" id="notelp" class="form-add-admin-input-nasabah" placeholder="08..." value="<?= htmlspecialchars($notelp); ?>" required />
                        </div>
                        <div class="form-add-admin-field full-width">
                            <label>Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-add-admin-input-nasabah" placeholder="Alamat lengkap..." required><?= htmlspecialchars($alamat); ?></textarea>
                        </div>
                    </div>

                    <?php if (isset($loggedInRole)): ?>
                        <div class="form-add-admin-field">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-add-admin-input">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-add-admin-actions">
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