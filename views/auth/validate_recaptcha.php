<?php
$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret' => '6LdyAJ8rAAAAANpA6LHlBYYZUm9m_A6JKeM8q5jH',
    'response' => $_POST['g-recaptcha-response'],
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$result = json_decode($response);
return $result->success;
