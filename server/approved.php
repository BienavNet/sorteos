<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMAILER\PHPMAILER\SMTP;
use PHPMailer\PHPMailer\Exception;


date_default_timezone_set("America/New_York");
require __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/SMTP.php";
require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/PHPMailer.php";
require_once __DIR__ . "/vendor/phpmailer/phpmailer/src/Exception.php";

function sendEmail($mail, $body, $email, $alt_body)
{
    try {
        if (str_contains($email, "hotmail") || str_contains($email, "yahoo") || str_contains($email, "outlook"))
            $mail->Body = $alt_body;
        else
            $mail->Body = $body;


        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "";
        $mail->Password = "";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom("", "Tornilujos la 40");
        $mail->addAddress("$email");
        $mail->isHTML(true);
        $mail->Subject = "Entrega de Boletos";
        $mail->Body = $body;
        $mail->AltBody = $alt_body;
        $mail->send();
        echo "No se produjo ningun error";
    } catch (Exception $e) {
        print_r($mail->ErrorInfo);
    }
}

$mail = new PHPMailer(true);
$body = file_get_contents(__DIR__.'/res/resend.html');
$alt_body = "Gracias por su compra<br>Orden #  <br>Cedula: <br>Nombre: Fernando Medina Lozada<br>Correo: <br>Telefono: <br>Boletos: ";

sendEmail($mail, $body, "", $alt_body);
sendEmail($mail, $body, "", $alt_body);


echo "correo enviado";
