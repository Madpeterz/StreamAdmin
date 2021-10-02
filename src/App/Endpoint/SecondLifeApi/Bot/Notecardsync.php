<?php

namespace App\Endpoint\SecondLifeApi\Bot;

use App\Helpers\BotHelper;
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
        $bot_helper = new BotHelper();
        if ($bot_helper->getNotecards() == false) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("hassyncmessage", "2");
            $this->setSwapTag("message", "Notecards not enabled on bot");
            return;
        }
        $this->setSwapTag("haserrormessage", false);
        $len = strlen($this->slconfig->getHttpInboundSecret());
        if ($len < 5) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("haserrormessage", true);
            $this->setSwapTag("message", "httpcode is to short - unable to continue");
            return;
        } elseif ($len > 30) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("haserrormessage", true);
            $this->setSwapTag("message", "httpcode is to long - unable to continue");
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

        $bot_helper = new BotHelper();
        $botUUID = $bot_helper->getBotUUID();
        if ($botUUID == null) {
            $this->failed("Unable to load bot UUID");
            return;
        }

        $this->setSwapTag("hassyncmessage", true);
        $this->setSwapTag("avataruuid", $botUUID);

        global $template_parts;
        $bits = $bot_helper->getBotCommand(
            "FetchNextNotecard",
            [$template_parts["url_base"],$this->slconfig->getHttpInboundSecret()]
        );
        $this->setSwapTag("raw", $bits["raw"]);
        $this->setSwapTag("cooked", $bits["cooked"]);
        $this->setSwapTag("message", $bits["cmd"]);
    }
}
