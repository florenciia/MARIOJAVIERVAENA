<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'config/email.php';

if (isset($_POST['g-recaptcha-response'])) {
    $secret_key = $email['recaptcha']['secret'];
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$_POST['g-recaptcha-response'];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    curl_close($curl);
    $responseCaptchaData = json_decode($data);
 
    if (!$responseCaptchaData->success) {
        http_response_code(400);
        die('recaptcha failed');
    }
}

if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['message'])
        || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
} else {
    $name = strip_tags(htmlspecialchars($_POST['name']));
    $email_address = strip_tags(htmlspecialchars($_POST['email']));
    $phone = strip_tags(htmlspecialchars($_POST['phone']));
    $message = strip_tags(htmlspecialchars($_POST['message']));
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'mail.privateemail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $email['auth']['username'];
        $mail->Password = $email['auth']['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom($email['from']['email'], $email['from']['name']);
        $mail->addAddress($email['to']['email'], $email['to']['name']);
        $mail->isHTML(false);
        $mail->Subject = "Contacto de ESCUELAVAENA: {$name}";
        $mail->Body = "Recibiste una mensaje de tu sitio web del formulario.\n\n"
            . "Estos son los detealles:\n\nNombre: $name\n\nEmail: $email_address\n\nTelefono: $phone\n\nMensaje:\n$message";
        $mail->send();
        http_response_code(200);
    } catch (Exception $e) {
        http_response_code(500);
    }
}
