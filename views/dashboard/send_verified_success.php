<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../vendor/autoload.php';

function sendmail_verified_success($email)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'banksampah747@gmail.com';
        $mail->Password   = 'tyyhcrkpcojftwuh'; // App Password Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('banksampah747@gmail.com', 'Bank Sampah');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Akun Anda Telah Diverifikasi';
        $mail->Body    = "
            <h2 style='color: green;'>Verifikasi Berhasil!</h2>
            <p>Halo,</p>
            <p>Akun Anda di <strong>Bank Sampah</strong> telah berhasil diverifikasi.</p>
            <p>Silakan login di: <a href='http://localhost/bank_sampah/index.php?page=login'>Halaman Login</a></p>
            <br>
            <p>Terima kasih telah bergabung!</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Gagal mengirim email ke $email: {$mail->ErrorInfo}");
    }
}
