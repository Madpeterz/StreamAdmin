<?php

namespace App\Helpers;

use App\R7\Model\Avatar;
use App\R7\Model\Botcommandq;
use App\R7\Model\Botconfig;
use App\R7\Model\Message;

class BotHelper
{
    protected ?Avatar $botAvatar = null;
    protected ?Botconfig $botconfig = null;
    public function attachBotSetup(Avatar $av, Botconfig $config): void
    {
        $this->botAvatar = $av;
        $this->botconfig = $config;
    }
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
    protected function addCommandToQ(string $command, array $args = []): bool
    {
        $botcommandQ = new Botcommandq();
        $botcommandQ->setCommand($command);
        if (count($args) > 0) {
            $botcommandQ->setArgs(json_encode($args));
        }
        $botcommandQ->setUnixtime(time());
        $reply = $botcommandQ->createEntry();
        return $reply["status"];
    }
    public function sendBotInvite(Avatar $avatar): bool
    {
        if ($this->getBotConfig() == false) {
            return false;
        }
        if ($this->botconfig->getInvites() == false) {
            return false;
        }
        return $this->addCommandToQ(
            "GroupInvite",
            [$this->botconfig->getInviteGroupUUID(),$avatar->getAvatarUUID(),"everyone"]
        );
    }
    /**
     * getBotCommand
     * @return mixed[] [cmd => X, raw => X, cooked => X]
     */
    public function getBotCommand(string $command, array $args): array
    {
        $cleanArgs = [];
        foreach ($args as $a) {
            $cleanArgs[] = urldecode($a);
        }
        if ($this->getBotConfig() == false) {
            return false;
        }
        $raw = "" . $command . "" . implode("~#~", $cleanArgs) . "" . $this->botconfig->getSecret();
        $cooked = sha1($raw);
        $cmd = $command . "|||" . implode("~#~", $args) . "@@@" . $cooked;
        return [
            "raw" => $raw,
            "cooked" => $cooked,
            "cmd" => $cmd,
        ];
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
        if (($allow_bot == true) && ($this->botconfig->getIms() == true)) {
            if ($this->addCommandToQ("IM", [$avatar->getAvatarUUID(),$message]) == false) {
                return ["status" => false,"message" => "Unable to add IM to the botQ"];
            }
        }
        return $this->sendMessageToAvatar($avatar, $message);
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
