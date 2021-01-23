<?php

namespace App\Helpers;

use App\Models\Avatar;
use App\Models\Botconfig;
use App\Models\Message;

class BotHelper
{
    public function sendBotCommand(Botconfig $botconfig, string $command, array $args): string
    {
        $raw = "" . $command . "" . implode("~#~", $args) . "" . $botconfig->getSecret();
        $cooked = sha1($raw);
        global $reply;
        $reply["raw"] = $raw;
        $reply["cooked"] = $cooked;
        return "" . $command . "|||" . implode("~#~", $args) . "@@@" . $cooked . "";
    }
    /**
     * sendMessage
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function sendMessage(
        Botconfig $botconfig,
        Avatar $botavatar,
        Avatar $avatar,
        string $message,
        bool $allow_bot = false
    ): array {
        $reply_status = true;
        $why_failed = "No idea";
        if ($allow_bot == true) {
            if ($botconfig->getIms() == true) {
                $bot_sendMessage = $this->sendBotCommand($botconfig, "im", [$avatar->getAvatarUUID(),$message]);
                $status = $this->sendMessageToAvatar($botavatar, $bot_sendMessage);
                if ($status["status"] == false) {
                    $reply_status = false;
                    $why_failed = $status["message"];
                }
            }
        }
        if ($reply_status == true) {
            return $this->sendMessageToAvatar($avatar, $message);
        }
        return ["status" => false,"message" => $why_failed];
    }
    /**
     * sendMessageToAvatar
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function sendMessageToAvatar(Avatar $avatar, string $sendmessage): array
    {
        $message = new Message();
        $message->setAvatarLink($avatar->getId());
        $message->setMessage($sendmessage);
        return $message->createEntry();
    }
}
