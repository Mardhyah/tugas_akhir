<?php
require __DIR__ . '/../../vendor/autoload.php'; // arahkan ke vendor PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'banksampah747@gmail.com';
    $mail->Password   = 'tyyhcrkpcojftwuh';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('banksampah747@gmail.com', 'Bank Sampah');
    $mail->addAddress('mardhyah01@gmail.com', 'User');

    $mail->isHTML(true);
    $mail->Subject = 'Tes Kirim Email';
    $mail->Body    = 'Ini adalah percobaan kirim email dari aplikasi Bank Sampah.';
    $mail->send();
    echo 'Email terkirim!';
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
