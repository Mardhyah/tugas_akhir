<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

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
        $mail->Body = "
<div style='font-family: Arial, sans-serif; color: #333;'>
    <h2 style='color: #2e7d32;'>Akun Anda Berhasil Diverifikasi</h2>
    <p>Halo,</p>
        <p>Selamat! Akun Anda di <strong>Bank Sampah</strong> telah berhasil diverifikasi oleh admin.</p>
    <p>Anda sekarang bisa login</p>
    <p style='margin: 20px 0;'>
        <a href='http://localhost/bank_sampah/index.php?page=login'
           style='background-color: #2e7d32; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
            Login Sekarang
        </a>
    </p>
    <p>Salam hangat,</p>
    <p><strong>Tim Bank Sampah</strong></p>
</div>
";



        $mail->send();
        return true; // sukses
    } catch (Exception $e) {
        echo "❌ Error PHPMailer: {$mail->ErrorInfo}<br>";
        return false; // gagal
    }
}
