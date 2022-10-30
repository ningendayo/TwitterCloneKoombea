<?php

namespace utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailing
{

    private string $host;
    private string $port;
    private string $account_name;
    private string $address;
    private string $password;
    private PHPMailer $mail;
    private bool $debugMode;
    private string $last_error;

    public function __construct(bool $debugMode)
    {
        global $configs;
        $smtp_settings = $configs['smtp'];
        $this->debugMode = $debugMode;
        $this->host = $smtp_settings['host'];
        $this->port = $smtp_settings['port'];
        $this->account_name = $smtp_settings['account_name'];
        $this->address = $smtp_settings['address'];
        $this->password = $smtp_settings['password'];
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = "UTF-8";
    }

    public function sendMail(array $to, string $subject, string $body, array $files = []): bool
    {
        try {
            $this->mail->clearAllRecipients();
            $this->mail->clearCCs();
            $this->mail->clearBCCs();
            $this->mail->clearAttachments();
            $this->mail->SMTPDebug = ($this->debugMode) ? \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER : \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
            $this->mail->isSMTP();
            $this->mail->Host = $this->host;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->address;
            $this->mail->Password = $this->password;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = $this->port;
            $this->mail->setFrom($this->address, $this->account_name);
            if (count($to) >= 1) {
                $this->mail->addAddress($to[0]);
                unset($to[0]);
            }
            foreach ($to as $address) {
                $this->mail->addCC($address);
            }
            foreach ($files as $file) {
                $this->mail->addAttachment($file);
            }
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (\Exception $exception) {
            $this->last_error = $exception->getMessage();
            return false;
        }
    }

    public function getLastError(): string
    {
        return $this->last_error;
    }

}