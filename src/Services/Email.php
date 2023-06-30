<?php

namespace App\Zaptank\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email {

    private $smtp_host;
    private $smtp_username;
    private $smtp_password;
    private $smtp_port;

    public function __construct() {
        $this->smtp_host = $_ENV['SMTP_HOST'];
        $this->smtp_username = $_ENV['SMTP_USERNAME'];
        $this->smtp_password = $_ENV['SMTP_PASSWORD'];
        $this->smtp_port = $_ENV['SMTP_PORT'];
    }

    public function send(string $subject, string $body, string $altBody, string $email) :bool {

        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $this->smtp_host;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username = $this->smtp_username; // E-mail SMTP
        $mail->Password = $this->smtp_password;
        $mail->Port = $this->smtp_port;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true, ]];
        $mail->setFrom('noreply@redezaptank.com.br', 'DDTank'); // E-mail SMTP
        $mail->addAddress('' . $email . '', 'DDTank'); // E-mail do usuário
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;
        return $mail->send();       
    }
}