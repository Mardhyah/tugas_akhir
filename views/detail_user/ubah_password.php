<?php
$current_page = $_GET['page'] ?? '';

include_once __DIR__ . '/../layouts/header.php';
include_once __DIR__ . '/../layouts/sidebar.php';
include_once __DIR__ . '/../../config/koneksi.php';



// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$query = "SELECT id FROM user WHERE username = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Tangani form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Cek konfirmasi password baru
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Konfirmasi password tidak sesuai.');</script>";
    } else {
        // Ambil password lama dari DB
        $query = "SELECT password FROM user WHERE id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verifikasi password lama
        if (!password_verify($old_password, $user['password'])) {
            echo "<script>alert('Password lama salah.');</script>";
        } else {
            // Hash dan simpan password baru
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE user SET password = ? WHERE id = ?";
            $stmt = $koneksi->prepare($update);
            $stmt->bind_param("si", $hashed, $id);
            if ($stmt->execute()) {
                echo "<script>alert('Password berhasil diubah!'); window.location.href='index.php?page=ubah_password';</script>";
                exit;
            } else {
                echo "<script>alert('Terjadi kesalahan saat menyimpan.');</script>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/bank_sampah/assets/css/style.css">
    <style>
        .password-form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
            width: 100%;
        }

        .password-form-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 700px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .password-form-card h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .password-form-card .form-group {
            margin-bottom: 20px;
        }

        .password-form-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        .password-form-card input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s ease;
            font-size: 15px;
        }

        .password-form-card input[type="password"]:focus {
            border-color: #3c91e6;
            outline: none;
        }

        .btn-submit {
            width: 100%;
            background-color: #25745A;
            color: #fff;
            padding: 14px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #357ad6;
        }

        /* Tambahan untuk eye icon */
        .input-password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-password-wrapper input {
            flex: 1;
            padding-right: 40px;
            /* untuk memberi ruang ikon mata */
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            width: 24px;
            height: 24px;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }

        .toggle-password:hover {
            opacity: 1;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .password-form-card {
                padding: 30px 20px;
            }
        }
    </style>

</head>

<body>
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>

        </nav>

        <!-- MAIN -->
        <main>

            <div class="main--content">
                <div class="header--wrapper">
                </div>

                <div class="password-form-container">
                    <div class="password-form-card">
                        <h2>Form Ubah Password</h2>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">

                            <div class="form-group">
                                <label for="old_password">Password Lama</label>
                                <div class="input-password-wrapper">
                                    <input type="password" id="old_password" name="old_password" required>
                                    <img src="https://img.icons8.com/ios-glyphs/30/visible--v1.png"
                                        class="toggle-password"
                                        onclick="togglePassword('old_password', this)" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="new_password">Password Baru</label>
                                <div class="input-password-wrapper">
                                    <input type="password" id="new_password" name="new_password" required>
                                    <img src="https://img.icons8.com/ios-glyphs/30/visible--v1.png"
                                        class="toggle-password"
                                        onclick="togglePassword('new_password', this)" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password Baru</label>
                                <div class="input-password-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <img src="https://img.icons8.com/ios-glyphs/30/visible--v1.png"
                                        class="toggle-password"
                                        onclick="togglePassword('confirm_password', this)" />
                                </div>
                            </div>

                            <button type="submit" class="btn-submit">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
                <script>
                    function togglePassword(inputId, img) {
                        const input = document.getElementById(inputId);
                        const isPassword = input.type === "password";

                        input.type = isPassword ? "text" : "password";

                        // Ganti ikon mata sesuai status
                        img.src = isPassword ?
                            "https://img.icons8.com/ios-glyphs/30/closed-eye.png" // mata tertutup
                            :
                            "https://img.icons8.com/ios-glyphs/30/visible--v1.png"; // mata terbuka
                    }
                </script>


        </main>
    </section>

    <script src="script.js"></script>
</body>

</html>