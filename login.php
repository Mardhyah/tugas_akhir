<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ambil data user berdasarkan username
    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        // Verifikasi password dengan password_verify()
        if (password_verify($password, $data['password'])) {
            // Password benar, simpan session
            $_SESSION['id'] = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['role'];

            header("Location: index.php?page=dashboard");
            exit;
        } else {
            echo "<script>alert('Username atau Password salah'); window.location='login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Username atau Password salah'); window.location='login.php';</script>";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
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

        .container {
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
            ;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            border-radius: 10px;
        }

        .social-login {
            margin-top: 40px;
            text-align: center;
        }

        .social-login i {
            font-size: 30px;
            margin: 0 18px;
            cursor: pointer;
            color: #444;
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
    <div class="container">
        <div class="signin-section">
            <h2>Signin</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="btn-login">Signin</button>
            </form>
            <div class="social-login">
                <p>or signin with</p>
                <i class="fab fa-facebook-circle"></i>
                <i class="fab fa-google-plus-g"></i>
                <i class="fab fa-linkedin"></i>
            </div>
        </div>
        <div class="welcome-section">
            <h2>Bank Sampah</h2>
            <p>Welcome back! We are so happy to have you here. It's great to see you again. We hope you had a safe and enjoyable time away.</p>
            <button class="btn-signup" onclick="window.location='register.php'">No account yet? Signup.</button>
        </div>
    </div>
</body>

</html>