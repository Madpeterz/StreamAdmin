<?php

namespace App\Helpers;

use App\Models\Slconfig;
use PHPMailer\PHPMailer\PHPMailer;

class EmailHelper
{
    public function send_email($to = "", $subject = "", $message = ""): array
    {
        $to_name = explode("@", $to)[0];
        $slconfig = new Slconfig();
        if ($slconfig->loadID(1) == true) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $slconfig->getSmtp_host();
            $mail->Port = $slconfig->getSmtp_port();
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Username = $slconfig->getSmtp_username();
            $mail->Password = $slconfig->getSmtp_accesscode();
            $mail->setFrom($slconfig->getSmtp_from(), $slconfig->getSmtp_replyto());
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
