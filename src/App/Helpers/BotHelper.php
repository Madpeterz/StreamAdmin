<?php

namespace App\Helpers;

use App\Models\Avatar;
use App\Models\Botcommandq;
use App\Models\Botconfig;
use App\Models\Message;
use YAPF\Framework\Responses\DbObjects\CreateReply;

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
            if ($this->botAvatar->loadID($this->botconfig->getAvatarLink())->status == false) {
                $this->botAvatar = null;
            }
        }
        return $this->botAvatar;
    }
    protected function getBotConfig(): bool
    {
        if ($this->botconfig == null) {
            $this->botconfig = new Botconfig();
            return $this->botconfig->loadID(1)->status;
        }
        return true;
    }
    protected function addCommandToQ(string $command, array $args = []): CreateReply
    {
        $botcommandQ = new Botcommandq();
        $botcommandQ->setCommand($command);
        if (count($args) > 0) {
            $botcommandQ->setArgs(json_encode($args));
        }
        $botcommandQ->setUnixtime(time());
        return $botcommandQ->createEntry();
    }
    public function sendBotInvite(Avatar $avatar): CreateReply
    {
        if ($this->getBotConfig() == false) {
            return new CreateReply("Unable to get bot config");
        }
        if ($this->botconfig->getInvites() == false) {
            return new CreateReply("Invites are disabled for the bot");
        }
        return $this->addCommandToQ(
            "GroupInvite",
            [$this->botconfig->getInviteGroupUUID(),$avatar->getAvatarUUID(),"everyone"]
        );
    }

    public function sendBotNextNotecard(string $serverurl, string $httpInboundCode): CreateReply
    {
        return $this->addCommandToQ(
            "FetchNextNotecard",
            [$serverurl,$httpInboundCode]
        );
    }

    public function getBotCommand(string $command, array $args): string
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
        return $command . "|||" . implode("~#~", $cleanArgs) . "@@@" . $cooked;
    }

    public function sendMessage(
        Avatar $avatar,
        string $message,
        bool $allow_bot = false,
        bool $allowObjectIm = true
    ): CreateReply {
        if ($this->getBotConfig() == false) {
            return ["status" => false,"message" => "Unable to get bot config"];
        }
        if (($allow_bot == true) && ($this->botconfig->getIms() == true)) {
            if ($this->addCommandToQ("IM", [$avatar->getAvatarUUID(),$message])->status == false) {
                return new CreateReply("Unable to add IM to the botQ");
            }
        }
        if ($allowObjectIm == false) {
            return new CreateReply("Skipping message via object", true);
        }
        return $this->sendMessageToAvatar($avatar, $message);
    }
    /**
     * sendMessageToAvatar
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function sendMessageToAvatar(Avatar $avatar, string $sendmessage): CreateReply
    {
        $message = new Message();
        $message->setAvatarLink($avatar->getId());
        $message->setMessage($sendmessage);
        return $message->createEntry();
    }
}
