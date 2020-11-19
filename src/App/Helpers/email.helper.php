<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once("webpanel/vendor/autoload.php");
class email_helper
{
    public function send_email($to = "", $subject = "", $message = "")
    {
        $to_name = explode("@", $to)[0];
        $slconfig = new slconfig();
        if ($slconfig->loadID(1) == true) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $slconfig->get_smtp_host();
            $mail->Port = $slconfig->get_smtp_port();
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Username = $slconfig->get_smtp_username();
            $mail->Password = $slconfig->get_smtp_accesscode();
            $mail->setFrom($slconfig->get_smtp_from(), $slconfig->get_smtp_replyto());
            $mail->addAddress($to, $to_name);
            $mail->Subject = $subject;
            $mail->IsHTML(true);
            $mail->msgHTML($message);
            if ($mail->send()) {
                return ["status" => true,"message" => "Sent"];
            } else {
                return ["status" => false,"message" => "Not sent"];
            }
        } else {
            return ["status" => false,"message" => "Unable to load SMTP settings"];
        }
    }
}
