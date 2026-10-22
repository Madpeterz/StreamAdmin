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
        return $this->botAvatar->_Id;
    }
    public function getBotUUID(): ?string
    {
        if ($this->getBotAvatar() == null) {
            return null;
        }
        return $this->botAvatar->_AvatarUUID;
    }
    public function getNotecards(): bool
    {
        if ($this->getBotConfig() == false) {
            return false;
        }
        return $this->botconfig->_Notecards;
    }
    protected function getBotAvatar(): ?Avatar
    {
        if ($this->getBotConfig() == false) {
            return null;
        }
        if ($this->botAvatar == null) {
            $this->botAvatar = new Avatar();
            if ($this->botAvatar->loadID($this->botconfig->_AvatarLink)->status == false) {
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
        $botcommandQ->_Command = $command;
        if (count($args) > 0) {
            $botcommandQ->_Args = json_encode($args);
        }
        $botcommandQ->_Unixtime = time();
        return $botcommandQ->createEntry();
    }
    public function sendBotInvite(Avatar $avatar): CreateReply
    {
        if ($this->getBotConfig() == false) {
            return new CreateReply("Unable to get bot config");
        }
        if ($this->botconfig->_Invites == false) {
            return new CreateReply("Invites are disabled for the bot");
        }
        return $this->addCommandToQ(
            "GroupInvite",
            [$this->botconfig->_InviteGroupUUID, $avatar->_AvatarUUID, "everyone"]
        );
    }

    public function sendBotNextNotecard(string $serverurl, string $httpInboundSecret): CreateReply
    {
        $BotcommandQ = new Botcommandq();
        if ($BotcommandQ->loadByCommand("FetchNextNotecard")->status == true) {
            return new CreateReply("already in Q", true, 0);
        }
        // the command travels through the message queue and IMs, so send a short lived
        // key scoped to sys.php Notecard/Next instead of the inbound secret
        return $this->addCommandToQ(
            "FetchNextNotecard",
            [$serverurl, SystemApiKey::current($httpInboundSecret, "Notecard", "Next")]
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
        $raw = "" . $command . "" . implode("~#~", $cleanArgs) . "" . $this->botconfig->_Secret;
        $cooked = sha1($raw);
        return $command . "|||" . implode("~#~", $cleanArgs) . "@@@" . $cooked;
    }

    public function sendMessage(
        Avatar $avatar,
        string $message,
    ): CreateReply {
        // send via Mail server
        $mail = new Message();
        $mail->_AvatarLink = $avatar->_Id;
        $mail->_Message = $message;
        $create = $mail->createEntry();
        if ($create->status == false) {
            return $create;
        }
        // send via bot
        if ($this->getBotConfig() == false) {
            return $create;
        }
        if ($this->botconfig->_Ims == false) {
            return $create;
        }
        return $this->addCommandToQ("IM", [$avatar->_AvatarUUID, $message]);
    }
}
