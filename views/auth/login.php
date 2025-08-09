<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../config/koneksi.php';

function validate_recaptcha($recaptcha_response)
{
    $secret = '6LdyAJ8rAAAAANpA6LHlBYYZUm9m_A6JKeM8q5jH';

    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $secret,
        'response' => $recaptcha_response,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response);
    return $result->success ?? false;
}

if (isset($_POST['login'])) {
    // Validasi reCAPTCHA
    if (!isset($_POST['g-recaptcha-response']) || !validate_recaptcha($_POST['g-recaptcha-response'])) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Validasi reCAPTCHA gagal. Silakan coba lagi.'];
        header("Location: index.php?page=login");
        exit;
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ambil data user berdasarkan username
    $username = mysqli_real_escape_string($koneksi, $username);
    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        if (password_verify($password, $data['password'])) {

            // Cek apakah role nasabah sudah verifikasi email
            if ($data['role'] == 'nasabah' && $data['verify_status'] != 'verified') {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Email Anda belum diverifikasi. Silakan cek email Anda untuk verifikasi.'];
                header("Location: index.php?page=login");
                exit;
            }

            // Cek verifikasi admin untuk nasabah
            if ($data['role'] == 'nasabah' && $data['is_verified'] != 1) {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Silakan tunggu verifikasi dari admin.'];
                header("Location: index.php?page=login");
                exit;
            }

            // Login berhasil
            $_SESSION['id'] = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['role'];
            header("Location: index.php?page=dashboard");
            exit;
        } else {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Username atau Password salah.'];
            header("Location: index.php?page=login");
            exit;
        }
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Username atau Password salah.'];
        header("Location: index.php?page=login");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signin</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
        }

        .container-login-login {
            display: flex;
            height: 100vh;
        }

        .signin-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 60px 40px;
        }

        .signin-section h2 {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 40px;
        }

        .form-group {
            width: 100%;
            max-width: 600px;
            margin-bottom: 30px;
        }

        .form-group input {
            width: 100%;
            padding: 20px;
            border: none;
            background-color: #f0f0f0;
            font-size: 20px;
            border-radius: 10px;
        }

        .btn-login {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            background-color: #25745A;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            border-radius: 10px;
        }

        .welcome-section {
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

        .welcome-section h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .welcome-section p {
            max-width: 500px;
            font-size: 20px;
            line-height: 1.7;
        }

        .btn-signup {
            margin-top: 40px;
            padding: 18px 36px;
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            border-radius: 35px;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container-login-login">
        <div class="signin-section">
            <h2>Login</h2>

            <?php if (isset($_SESSION['alert'])): ?>
                <div class="alert alert-<?= $_SESSION['alert']['type']; ?> w-100 text-center" style="max-width:600px;">
                    <?= $_SESSION['alert']['message']; ?>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="g-recaptcha" data-sitekey="6LdyAJ8rAAAAAAtPhceRHCvcwmYEh-foci8tYnL_"></div>
                <br>
                <button type="submit" name="login" class="btn-login">Login</button>
            </form>

        </div>

        <div class="welcome-section">
            <h2>Bank Sampah</h2>
            <p>Mulai perubahan dari diri sendiri. <br>Pilah sampah, selamatkan bumi, dan raih nilai. <br><strong>Memilah Sampah, Menabung Emas.</strong></p>
            <button class="btn-signup" onclick="window.location='index.php?page=register_nasabah'">Register Nasabah</button>
        </div>
    </div>
</body>

</html>