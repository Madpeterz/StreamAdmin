<?php

namespace App\Helpers;

use App\R7\Model\Slconfig;
use PHPMailer\PHPMailer\PHPMailer;

class EmailHelper
{
    /**
     * sendEmail
     * @return mixed[] [status => bool, message=> string]
     */
    public function sendEmail($to = "", $subject = "", $message = ""): array
    {
        $to_name = explode("@", $to)[0];
        $slconfig = new Slconfig();
        if ($slconfig->loadID(1) == true) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $slconfig->getSmtpHost();
            $mail->Port = $slconfig->getSmtpPort();
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Username = $slconfig->getSmtpUsername();
            $mail->Password = $slconfig->getSmtpAccesscode();
            $mail->setFrom($slconfig->getSmtpFrom(), $slconfig->getSmtpReplyTo());
            $mail->addAddress($to, $to_name);
            $mail->Subject = $subject;
            $mail->IsHTML(true);
            $mail->msgHTML($message);
            if ($mail->send()) {
                return ["status" => true,"message" => "Sent"];
            }
            return ["status" => false,"message" => "Not sent"];
        }
        return ["status" => false,"message" => "Unable to load SMTP settings"];
    }
}
