<?php

use App\Models\Avatar;
use App\Models\Botconfig;

class bot_helper
{
    function send_bot_command(Botconfig $botconfig, string $command, array $args): string
    {
        $raw = "" . $command . "" . implode("~#~", $args) . "" . $botconfig->get_secret();
        $cooked = sha1($raw);
        global $reply;
        $reply["raw"] = $raw;
        $reply["cooked"] = $cooked;
        return "" . $command . "|||" . implode("~#~", $args) . "@@@" . $cooked . "";
    }
    function send_message(Botconfig $botconfig, Avatar $botavatar, Avatar $avatar, string $message, bool $allow_bot = false)
    {
        $reply_status = true;
        $why_failed = "No idea";
        if ($allow_bot == true) {
            if ($botconfig->get_ims() == true) {
                $bot_send_message = $this->send_bot_command($botconfig, "im", [$avatar->getAvataruuid(),$message]);
                $status = $this->send_message_to_avatar($botavatar, $bot_send_message);
                if ($status["status"] == false) {
                    $reply_status = false;
                    $why_failed = $status["message"];
                }
            }
        }
        if ($reply_status == true) {
            return $this->send_message_to_avatar($avatar, $message);
        } else {
            return ["status" => false,"message" => $why_failed];
        }
    }
    function send_message_to_avatar(Avatar $avatar, string $sendmessage): array
    {
        $message = new message();
        $message->setAvatarlink($avatar->getId());
        $message->setMessage($sendmessage);
        return $message->createEntry();
    }
}
