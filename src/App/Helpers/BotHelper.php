<?php

namespace App\Helpers;

use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use App\R7\Model\Message;

class BotHelper
{
    protected ?Avatar $botAvatar = null;
    protected ?Botconfig $botconfig = null;
    public function getBotUUID(): ?string
    {
        if ($this->getBotAvatar() == null) {
            return null;
        }
        return $this->botAvatar->getAvatarUUID();
    }
    public function getNotecards(): bool
    {
        if ($this->getBotConfig() == false) {
            return false;
        }
        return $this->botconfig->getNotecards();
    }
    protected function getBotAvatar(): ?Avatar
    {
        if ($this->getBotConfig() == false) {
            return null;
        }
        if ($this->botAvatar == null) {
            $this->botAvatar = new Avatar();
            if ($this->botAvatar->loadID($this->botconfig->getAvatarLink()) == false) {
                $this->botAvatar = null;
            }
        }
        return $this->botAvatar;
    }
    protected function getBotConfig(): bool
    {
        if ($this->botconfig == null) {
            $this->botconfig = new Botconfig();
            return $this->botconfig->loadID(1);
        }
        return true;
    }
    public function sendBotInvite(Avatar $avatar): void
    {
        if ($this->getBotConfig() == false) {
            return;
        }
        if ($this->botconfig->getInvites() == false) {
            return;
        }
        $this->sendBotCommand(
            "GroupInvite",
            [$this->botconfig->getInviteGroupUUID(),$avatar->getAvatarUUID(),"everyone"]
        );
    }
    public function sendBotCommand(string $command, array $args): bool
    {
        if ($this->getBotConfig() == false) {
            return false;
        }
        $raw = "" . $command . "" . implode("~#~", $args) . "" . $this->botconfig->getSecret();
        $cooked = sha1($raw);
        global $reply;
        $reply["raw"] = $raw;
        $reply["cooked"] = $cooked;
        $bot_avatar = $this->getBotAvatar();
        if ($bot_avatar != null) {
            $status = $this->sendMessageToAvatar(
                $bot_avatar,
                "" . $command . "|||" . implode("~#~", $args) . "@@@" . $cooked . ""
            );
            return $status["status"];
        }
        return false;
    }
    public function getBotCommand(string $command, array $args): string
    {
        if ($this->getBotConfig() == false) {
            return false;
        }
        $raw = "" . $command . "" . implode("~#~", $args) . "" . $this->botconfig->getSecret();
        $cooked = sha1($raw);
        global $reply;
        $reply["raw"] = $raw;
        $reply["cooked"] = $cooked;
        return $command . "|||" . implode("~#~", $args) . "@@@" . $cooked;
    }
    /**
     * sendMessage
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function sendMessage(
        Avatar $avatar,
        string $message,
        bool $allow_bot = false
    ): array {
        if ($this->getBotConfig() == false) {
            return ["status" => false,"message" => "Unable to get bot config"];
        }
        $reply_status = true;
        $why_failed = "No idea";
        if ($allow_bot == true) {
            if ($this->botconfig->getIms() == true) {
                $reply_status = $this->sendBotCommand("im", [$avatar->getAvatarUUID(),$message]);
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
