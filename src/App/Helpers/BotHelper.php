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
    public function attachBotSetup(?Avatar $av, ?Botconfig $config): void
    {
        $this->botAvatar = $av;
        $this->botconfig = $config;
    }
    public function getBotAvatarLink(): int
    {
        if ($this->botAvatar == null) {
            return 1;
        }
        return $this->botAvatar->getId();
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
            [$this->botconfig->getInviteGroupUUID(), $avatar->getAvatarUUID(), "everyone"]
        );
    }

    public function sendBotNextNotecard(string $serverurl, string $httpInboundCode): CreateReply
    {
        $BotcommandQ = new Botcommandq();
        if ($BotcommandQ->loadByCommand("FetchNextNotecard")->status == true) {
            return new CreateReply("already in Q", true, 0);
        }
        return $this->addCommandToQ(
            "FetchNextNotecard",
            [$serverurl, $httpInboundCode]
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
    ): CreateReply {
        // send via Mail server
        $mail = new Message();
        $mail->setAvatarLink($avatar->getId());
        $mail->setMessage($message);
        $create = $mail->createEntry();
        if ($create->status == false) {
            return $create;
        }
        // send via bot
        if ($this->getBotConfig() == false) {
            return $create;
        }
        if ($this->botconfig->getIms() == false) {
            return $create;
        }
        return $this->addCommandToQ("IM", [$avatar->getAvatarUUID(), $message]);
    }
}
