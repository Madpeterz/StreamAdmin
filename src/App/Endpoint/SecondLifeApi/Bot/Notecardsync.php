<?php

namespace App\Endpoint\SecondLifeApi\Bot;

use App\Helpers\BotHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use App\R7\Model\Message;
use App\R7\Model\Notecard;
use App\Template\SecondlifeAjax;

class Notecardsync extends SecondlifeAjax
{
    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "This API is owner only");
            return;
        }
        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) == false) {
            $this->setSwapTag("message", "Unable to load bot config");
            return;
        }
        $botavatar = new Avatar();
        if ($botavatar->loadID($botconfig->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load bot avatar");
            return;
        }
        if ($botconfig->getNotecards() == false) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("hassyncmessage", "2");
            $this->setSwapTag("message", "Notecards not enabled on bot");
            return;
        }
        $notecard = new Notecard();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [0],
            "types" => ["i"],
            "matches" => [">"],
        ];
        $count_data = $this->sql->basicCountV2($notecard->getTable(), $whereConfig);
        if ($count_data["status"] == false) {
            $this->setSwapTag("message", "Unable to fetch next notecard");
            return;
        }

        $this->setSwapTag("status", true);
        if ($count_data["count"] == 0) {
            $this->setSwapTag("message", "No work");
            $this->setSwapTag("hassyncmessage", false);
            return;
        }

        $this->setSwapTag("hassyncmessage", true);
        $this->setSwapTag("avataruuid", $botavatar->getAvatarUUID());
        $bot_helper = new BotHelper();
        global $template_parts;
        $message = $bot_helper->sendBotCommand(
            $botconfig,
            "FetchNextNotecard",
            [$template_parts["url_base"],$this->slconfig->getHttpInboundSecret()]
        );
        $this->setSwapTag("message", $message);
    }
}
